<?php
/**
 * Blockonomics PayBitcoin block
 *
 * @category    Blockonomics
 * @package     Blockonomics_Merchant
 * @author      Blockonomics
 * @copyright   Blockonomics (https://blockonomics.com)
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
use \Magento\Framework\App\ObjectManager;

class PayBitcoin extends Template
{
    protected $backendSession;

    const BASE_URL = 'https://www.blockonomics.co';
    const NEW_ADDRESS_URL = 'https://www.blockonomics.co/api/new_address';
    const PRICE_URL = 'https://www.blockonomics.co/api/price';

    /**
     * Constructor
     * @param Context $context
     * @param array $data
     */
    public function __construct(
        Context $context,
        Session $backendSession,
        OrderRepositoryInterface $orderRepository,
        ScopeConfigInterface $scopeConfig
    ) {
        parent::__construct($context);
        $this->backendSession = $backendSession;
        $this->orderRepository = $orderRepository;
        $this->scopeConfig = $scopeConfig;
    }

    /**
     * @return Current order id
     * @throws LocalizedException
     */
    public function getOrderId()
    {
        return $this->backendSession->getData('orderId', false);
    }

    /**
     * @return Order by id
     */
    public function getOrderById($orderId)
    {
        return $this->orderRepository->get($orderId);
    }


    /**
     * @return New bitcoin address from Blockonomics API
     */
    public function getNewAddress() 
    {
        $api_key = $this->scopeConfig->getValue('payment/blockonomics_merchant/app_key', ScopeInterface::SCOPE_STORE);
        $secret = $this->scopeConfig->getValue('payment/blockonomics_merchant/callback_url', ScopeInterface::SCOPE_STORE);

        $options = array(
            'http' => array(
                'header'  => 'Authorization: Bearer '.$api_key,
                'method'  => 'POST',
                'content' => ''
            )
        );

        $context = stream_context_create($options);
        $contents = file_get_contents($this::NEW_ADDRESS_URL."?match_callback=$secret&reset=1", false, $context);
        $new_address = json_decode($contents);
        return $new_address->address;
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
     * @return Convert currency to bitcoin from Blockonomics API
     */
    public function getOrderPriceInBitcoin()
    {
        $options = array( 'http' => array( 'method'  => 'GET') );
        $context = stream_context_create($options);
        $contents = file_get_contents($this::PRICE_URL. "?currency=USD", false, $context);
        $price = json_decode($contents);

        $orderId = $this->getOrderId();
        $order = $this->getOrderById($orderId);
        $order_total_price = $order->getGrandTotal();

        return intval(1.0e8*$order_total_price/$price->price);
    }

    public function createNewBitcoinTransaction($address)
    {  
        $objectManager = ObjectManager::getInstance();
        $bitcoinTransaction = $objectManager->create('Blockonomics\Merchant\Model\BitcoinTransaction');

        $bitcoinTransaction->setIdOrder($this->getOrderId());
        $bitcoinTransaction->setTimestamp(time());
        $bitcoinTransaction->setAddr($address);
        $bitcoinTransaction->setStatus(-1);
        $bitcoinTransaction->setValue($this->getOrderPriceInFiat());
        $bitcoinTransaction->setBits($this->getOrderPriceInBitcoin());

        $bitcoinTransaction->save();
    }
}