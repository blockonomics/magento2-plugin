<?php
/**
 * Blockonomics payment method model
 *
 * @category    Blockonomics
 * @package     Blockonomics_Merchant
 * @author      Blockonomics
 * @copyright   Blockonomics (https://blockonomics.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
namespace Blockonomics\Merchant\Model;

use Magento\Directory\Model\CountryFactory;
use Magento\Framework\Api\AttributeValueFactory;
use Magento\Framework\Api\ExtensionAttributesFactory;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\Response\Http;
use Magento\Framework\Data\Collection\AbstractDb;
use Magento\Framework\Model\Context;
use Magento\Framework\Model\ResourceModel\AbstractResource;
use Magento\Framework\Module\ModuleListInterface;
use Magento\Framework\Registry;
use Magento\Framework\Stdlib\DateTime\TimezoneInterface;
use Magento\Framework\UrlInterface;
use Magento\Payment\Helper\Data;
use Magento\Payment\Model\Method\AbstractMethod;
use Magento\Payment\Model\Method\Logger;
use Magento\Sales\Model\Order;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Sales\Model\Service\InvoiceService;
use Magento\Backend\Model\Session;

class Payment extends AbstractMethod
{
    //const BLOCKONOMICS_MAGENTO_VERSION = '1.0.0';
    const CODE = 'blockonomics_merchant';

    protected $_code = 'blockonomics_merchant';

    protected $_isInitializeNeeded = true;

    protected $urlBuilder;
    protected $storeManager;
    protected $_invoiceService;
    protected $backendSession;

    /**
     * @param Context $context
     * @param Registry $registry
     * @param ExtensionAttributesFactory $extensionFactory
     * @param AttributeValueFactory $customAttributeFactory
     * @param Data $paymentData
     * @param ScopeConfigInterface $scopeConfig
     * @param Logger $logger
     * @param UrlInterface $urlBuilder
     * @param StoreManagerInterface $storeManager
     * @param AbstractResource|null $resource
     * @param AbstractDb|null $resourceCollection
     * @param array $data
     * @internal param ModuleListInterface $moduleList
     * @internal param TimezoneInterface $localeDate
     * @internal param CountryFactory $countryFactory
     * @internal param Http $response
     */
    public function __construct(
        Context $context,
        Registry $registry,
        ExtensionAttributesFactory $extensionFactory,
        AttributeValueFactory $customAttributeFactory,
        Data $paymentData,
        ScopeConfigInterface $scopeConfig,
        Logger $logger,
        UrlInterface $urlBuilder,
        StoreManagerInterface $storeManager,
        InvoiceService $invoiceService,
        Session $backendSession,
        AbstractResource $resource = null,
        AbstractDb $resourceCollection = null,
        array $data = array()
    )
    {
        parent::__construct(
            $context,
            $registry,
            $extensionFactory,
            $customAttributeFactory,
            $paymentData,
            $scopeConfig,
            $logger,
            $resource,
            $resourceCollection,
            $data
        );

        $this->urlBuilder = $urlBuilder;
        $this->storeManager = $storeManager;
        $this->_invoiceService = $invoiceService;
        $this->backendSession = $backendSession;
    }

    /**
     * @param Order $order
     * @return array
     */
    public function getBlockonomicsRequest(Order $order)
    {
        $token = substr(md5(rand()), 0, 32);

        $payment = $order->getPayment();
        $payment->setAdditionalInformation('blockonomics_order_token', $token);
        $payment->save();

        $description = array();
        foreach ($order->getAllItems() as $item) {
            $description[] = number_format($item->getQtyOrdered(), 0) . ' × ' . $item->getName();
        }

        $params = array(
            'order_id' => $order->getIncrementId(),
            'price' => number_format($order->getGrandTotal(), 2, '.', ''),
            'currency' => $order->getOrderCurrencyCode(),
            'receive_currency' => $this->getConfigData('receive_currency'),
            'callback_url' => ($this->urlBuilder->getUrl('blockonomics/payment/callback') . '?token=' . $payment->getAdditionalInformation('blockonomics_order_token')),
            'cancel_url' => $this->urlBuilder->getUrl('checkout/onepage/failure'),
            'success_url' => $this->urlBuilder->getUrl('checkout/onepage/success'),
            'title' => $this->storeManager->getWebsite()->getName(),
            'description' => join($description, ', ')
        );
    }

    /**
     * @param Order $order
     */
    public function validateBlockonomicsCallback(Order $order)
    {
        try {
            if (!$order || !$order->getIncrementId()) {
                $request_order_id = (filter_input(INPUT_POST, 'order_id') ? filter_input(INPUT_POST, 'order_id') : filter_input(INPUT_GET, 'order_id'));

                throw new \Exception('Order #' . $request_order_id . ' does not exists');
            }

            $payment = $order->getPayment();
            $get_token = filter_input(INPUT_GET, 'token');
            $token1 = $get_token ? $get_token : '';
            $token2 = $payment->getAdditionalInformation('blockonomics_order_token');

            if ($token2 == '' || $token1 != $token2) {
                throw new \Exception('Tokens do match.');
            }

            $request_id = (filter_input(INPUT_POST, 'id') ? filter_input(INPUT_POST, 'id') : filter_input(INPUT_GET, 'id'));

        } catch (\Exception $e) {
            exit('Error occurred: ' . $e);
        }
    }

    /**
     * @param Order $order
     * @return true if created new, false if no creation happened
     */
    public function createInvoice($orderId = -1)
    {
        if($orderId === -1) {
            $orderId = $this->backendSession->getData('orderId', false);
        }

        if($order->hasInvoices() { return false; }

        $invoice = $this->_invoiceService->prepareInvoice($order);
        $invoice->register();
        $invoice->save();
        return true;
    }
}
