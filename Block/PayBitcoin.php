<?php
/**
 * Blockonomics block for paying bitcoin
 *
 * @category    Blockonomics
 * @package     Blockonomics_Merchant
 * @author      Blockonomics
 * @copyright   Blockonomics (https://blockonomics.co)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

namespace Blockonomics\Merchant\Block;

use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\View\Element\Template;
use Magento\Framework\View\Element\Template\Context;
use Magento\Backend\Model\Session;
use Magento\Sales\Model\Order;
use Magento\Sales\Api\OrderRepositoryInterface;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Store\Model\ScopeInterface;
use Magento\Framework\App\ObjectManager;
use Blockonomics\Merchant\Model\BitcoinTransaction;
use Blockonomics\Merchant\Model\ResourceModel\BitcoinTransaction\Collection;

class PayBitcoin extends Template
{
    protected $backendSession;
    protected $transactionCollection;

    // If debug mode is enabled, reuse bitcoin addresses
    const DEBUG = false;

    const BASE_URL = 'https://www.blockonomics.co';
    const PRICE_URL = 'https://www.blockonomics.co/api/price';
    const NEW_ADDRESS_URL = 'https://www.blockonomics.co/api/new_address';

    public function __construct(
        Context $context,
        Session $backendSession,
        OrderRepositoryInterface $orderRepository,
        ScopeConfigInterface $scopeConfig,
        Collection $transactionCollection
    ) {
        parent::__construct($context);
        $this->backendSession = $backendSession;
        $this->orderRepository = $orderRepository;
        $this->scopeConfig = $scopeConfig;
        $this->transactionCollection = $transactionCollection;
    }

    /**
     * @return Current order id from backend session
     */
    public function getOrderId()
    {
        return $this->backendSession->getData('orderId', false);
    }

    /**
     * @param int $orderId
     * @return Order by id
     */
    public function getOrderById($orderId)
    {
        return $this->orderRepository->get($orderId);
    }

    /**
     * @return Order bitcoin payment address from db if has any, if not return empty string
     */
    public function getOrderBitcoinAddress()
    {
        $collection = $this->transactionCollection->addFieldToFilter('id_order', $this->getOrderId());

        $orderAddr = '';

        foreach ($collection as $item) {
            $orderAddr = $item->getAddr();
        }

        return $orderAddr;
    }

    /**
     * @return False if no errors
     */
    private function checkForErrors($responseObj) {
        if(!isset($responseObj->response_code)) {
            $error = 'Your webhost is blocking outgoing HTTPS connections';
        } else {
            switch ($responseObj->response_code) {
                case 200:
                    break;
                case 401:
                    $error = 'API key is incorrect.';
                    break;
                case 500:
                    $error = $responseObj->message;
                    break;
                default:
                    $error = 'Error while generating new bitcoin address.';
                    break;
            }
        }
        if(isset($error)) {
            return $error;
        }
        // No errors
        return null;
    }

    /**
     * @return New bitcoin address from Blockonomics API
     */
    public function getNewAddress()
    {
        $api_key = $this->scopeConfig->getValue('payment/blockonomics_merchant/app_key', ScopeInterface::SCOPE_STORE);
        $secret = $this->scopeConfig->getValue('payment/blockonomics_merchant/callback_secret', ScopeInterface::SCOPE_STORE);

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, "https://www.blockonomics.co/api/new_address?match_callback=$secret");
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
        $header = "Authorization: Bearer " . $api_key;
        $headers = array();
        $headers[] = $header;
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        $contents = curl_exec($ch);
        if (curl_errno($ch)) {
            throw new Exception(curl_error($ch));
        }
        $responseObj = json_decode($contents);
        //Create response object if it does not exist
        if (!isset($responseObj)) $responseObj = new \stdClass();
        $responseObj->response_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close ($ch);
        
        $error = $this->checkForErrors($responseObj);

        if ($error) {
            $responseObj->address = '';
            $responseObj->error = $error;
        } else {
            $address = $responseObj->address;
        }
        
        return $responseObj;
    }

    /**
     * @return Altcoin enabled status from settings
     */
    public function getAltcoinStatus()
    {
        $altcoins_enabled = $this->scopeConfig->getValue('payment/blockonomics_merchant/altcoins', ScopeInterface::SCOPE_STORE);
        return $altcoins_enabled;
    }

    /**
     * @return Convert currency to fiat currency
     */
    public function getOrderPriceInFiat()
    {
        $orderId = $this->getOrderId();
        $order = $this->getOrderById($orderId);
        return $order->getGrandTotal();
    }

    /**
     * @return Currency code from store
     */
    public function getCurrencyCode()
    {
        return $this->_storeManager->getStore()->getCurrentCurrency()->getCode();
    }

    /**
     * @return Convert currency to bitcoin from Blockonomics API
     */
    public function getOrderPriceInBitcoin()
    {
        $currency_code = $this->getCurrencyCode();

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, "https://www.blockonomics.co/api/price?currency=".$currency_code);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        $contents = curl_exec($ch);
        if (curl_errno($ch)) {
            throw new Exception(curl_error($ch));
        }
        curl_close ($ch);

        $price = json_decode($contents)->price;
        $orderId = $this->getOrderId();
        $order = $this->getOrderById($orderId);
        $order_total_price = $order->getGrandTotal();

        $adjustment = intval($this->getPremiumAdjustment());
        $adjusted_price = $price * ((100 + $adjustment) / 100);

        return intval(1.0e8*$order_total_price/$adjusted_price);
    }

    /**
     * @return Premium adjustment from settings
     */
    public function getPremiumAdjustment()
    {
        $premium = $this->scopeConfig->getValue('payment/blockonomics_merchant/premium', ScopeInterface::SCOPE_STORE);
        return $premium;
    }

    /**
     * Create new order into database
     */
    public function createNewBitcoinTransaction($address)
    {
        $objectManager = ObjectManager::getInstance();
        $bitcoinTransaction = $objectManager->create('Blockonomics\Merchant\Model\BitcoinTransaction');

        $orderTimestamp = time();
        $this->backendSession->setData('orderTimestamp', $orderTimestamp);

        $bitcoinTransaction->setIdOrder($this->getOrderId());
        $bitcoinTransaction->setTimestamp($orderTimestamp);
        $bitcoinTransaction->setAddr($address);
        $bitcoinTransaction->setStatus(-1);
        $bitcoinTransaction->setValue($this->getOrderPriceInFiat());
        $bitcoinTransaction->setBits($this->getOrderPriceInBitcoin());

        $bitcoinTransaction->save();

        return $orderTimestamp;
    }

    /**
     * @return Order timestamp
     */
    public function getOrderTimestamp()
    {
        return $this->backendSession->getData('orderTimestamp', false);
    }

    /**
     * @return Secret from core_config
     */
    public function getSecret()
    {
        return $this->scopeConfig->getValue('payment/blockonomics_merchant/callback_secret', ScopeInterface::SCOPE_STORE);
    }

    /**
     * Set secret from core_config to session data
     * This way we can easily authenticate the timeout
     */
    public function setSectretToSession()
    {
        $secret = $this->getSecret();
        $this->backendSession->setData('sessionSecret', $secret);
    }

    public function setUuid($uuid)
    {
        $this->backendSession->setData('uuid', $uuid);
    }

    public function getUuid()
    {
        return $this->backendSession->getData('uuid' , false);
    }

}
