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

use Magento\Framework\Model\Context;
use Magento\Payment\Helper\Data;
use Magento\Payment\Model\Method\AbstractMethod;
use Magento\Sales\Model\Order;
use Magento\Sales\Api\OrderRepositoryInterface;
use Magento\Sales\Model\Service\InvoiceService;
use Magento\Backend\Model\Session;

class Payment extends AbstractMethod
{
    //const BLOCKONOMICS_MAGENTO_VERSION = '1.0.0';
    const CODE = 'blockonomics_merchant';
    protected $_code = 'blockonomics_merchant';
    protected $_isInitializeNeeded = true;

    protected $_invoiceService;
    protected $backendSession;

    /**
     * @param Context $context
     * @param array $data
     * @internal param TimezoneInterface $localeDate
     */
    public function __construct(
        Context $context,
        InvoiceService $invoiceService,
        Session $backendSession,
        OrderRepositoryInterface $orderRepository
    )
    {
        parent::__construct(
            $context,
            $logger,
            $data
        );

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
    public function updateOrderStateAndStatus($orderId = -1)
    {
        if($orderId === -1) {
            $orderId = $this->backendSession->getData('orderId', false);
        }

        $order = $this->orderRepository->get($orderId);
        
        $order->setState(Order::STATE_PROCESSING);
        $order->setStatus($order->getConfig()->getStateDefaultStatus(Order::STATE_PROCESSING));
        $order->save();
    }
}
