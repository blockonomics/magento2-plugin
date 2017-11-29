<?php
/**
 * Blockonomics payment model
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
use Magento\Framework\App\ObjectManager;

class Payment extends AbstractMethod
{
    const CODE = 'blockonomics_merchant';

    protected $_code = 'blockonomics_merchant';

    protected $_isInitializeNeeded = true;

    protected $urlBuilder;
    protected $storeManager;
    protected $_invoiceService;    
    protected $backendSession;

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
        array $data = []
    ) {
    
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
     * @param Int $orderId
     * @return true if created a new invoice, false if no creation happened
     */
    public function createInvoice($orderId = -1)
    {
        if ($orderId === -1) {
            $orderId = $this->backendSession->getData('orderId', false);
        }

        $order = $this->orderRepository->get($orderId);

        if ($order->hasInvoices()) {
            return false;
        }

        $invoice = $this->_invoiceService->prepareInvoice($order);
        $invoice->register();
        $invoice->save();
        
        try {
            $objectManager = ObjectManager::getInstance();
            $invoiceSender = $objectManager->create('Magento\Sales\Model\Order\Email\Sender\InvoiceSender');
            $invoiceSender->send($invoice);
            $order->setIsCustomerNotified(true)->save();
        } catch(\Exception $e) {
            echo $e;
        }

        return true;
    }

    /**
     * Set state and status of order
     *
     * @param Str $state
     * @param Int $orderId
     */
    public function updateOrderStateAndStatus($orderId = -1, $state)
    {
        if ($orderId == -1) {
            $orderId = $this->backendSession->getData('orderId', false);
        }

        $order = $this->orderRepository->get($orderId);

        if ($state == 'processing') {
            $order->setState(Order::STATE_PROCESSING);
            $order->setStatus($order->getConfig()->getStateDefaultStatus(Order::STATE_PROCESSING));
        }

        if ($state == 'pending') {
            $order->setState(Order::STATE_PROCESSING);
            $order->setStatus('pending_bitcoin_confirmation');
        }

        if ($state == 'holded') {
            $order->setStatus($order->getConfig()->getStateDefaultStatus(Order::STATE_HOLDED));
        }

        $order->save();
    }

    /**
     * Add a comment to order
     *
     * @param Str $comment
     * @param Int $orderId
     */
    public function addCommentToOrder($comment, $orderId = -1) {

        if ($orderId == -1) {
            $orderId = $this->backendSession->getData('orderId', false);
        }

        $order = $this->orderRepository->get($orderId);

        $order->addStatusHistoryComment($comment);
        $order->save();
    }
}
