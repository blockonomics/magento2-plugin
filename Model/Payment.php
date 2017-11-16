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
use Magento\Sales\Api\OrderRepositoryInterface;
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
        OrderRepositoryInterface $orderRepository,
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
        $this->orderRepository = $orderRepository;
    }

    /**
     * @param Int orderId
     * @return true if created a new invoice, false if no creation happened
     */
    public function createInvoice($orderId = -1)
    {
        if($orderId === -1) {
            $orderId = $this->backendSession->getData('orderId', false);
        }

        $order = $this->orderRepository->get($orderId);

        if($order->hasInvoices()) { 
            return false;
        }

        $invoice = $this->_invoiceService->prepareInvoice($order);
        $invoice->register();
        $invoice->save();
        return true;
    }

    /**
     * @param Int orderId
     */
    public function updateOrderStateAndStatus($orderId = -1, $state)
    {
        if($orderId == -1) {
            $orderId = $this->backendSession->getData('orderId', false);
        }

        $order = $this->orderRepository->get($orderId);

        if($state == 'processing') {
            $order->setState(Order::STATE_PROCESSING);
            $order->setStatus($order->getConfig()->getStateDefaultStatus(Order::STATE_PROCESSING));
        }

        if($state == 'pending') {
            $order->setStatus('pending_bitcoin_confirmation');
        }

        if($state == 'holded') {
            $order->setStatus($order->getConfig()->getStateDefaultStatus(Order::STATE_HOLDED));
        }

        $order->save();
    }
}