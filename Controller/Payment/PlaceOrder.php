<?php
/**
 * Blockonomics PlaceOrder controller
 *
 * @category    Blockonomics
 * @package     Blockonomics_Merchant
 * @author      Blockonomics
 * @copyright   Blockonomics (https://blockonomics.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
namespace Blockonomics\Merchant\Controller\Payment;

use Blockonomics\Merchant\Model\Payment as BlockonomicsPayment;
use Magento\Checkout\Model\Session;
use Magento\Backend\Model\Session as BackendSession;
use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Sales\Model\OrderFactory;

class PlaceOrder extends Action
{
    protected $orderFactory;
    protected $blockonomicsPayment;
    protected $checkoutSession;
    protected $backendSession;

    /**
     * @param Context $context
     * @param OrderFactory $orderFactory
     * @param Session $checkoutSession
     * @param BlockonomicsPayment $blockonomicsPayment
     */
    public function __construct(
        Context $context,
        OrderFactory $orderFactory,
        Session $checkoutSession,
        BackendSession $backendSession,
        BlockonomicsPayment $blockonomicsPayment
    ) {
    
        parent::__construct($context);

        $this->orderFactory = $orderFactory;
        $this->checkoutSession = $checkoutSession;
        $this->backendSession = $backendSession;
        $this->blockonomicsPayment = $blockonomicsPayment;
    }

    public function execute()
    {
        $id = $this->checkoutSession->getLastOrderId();

        $order = $this->orderFactory->create()->load($id);

        $this->backendSession->setData('orderId', $id);

        if (!$order->getIncrementId()) {
            $this->getResponse()->setBody(json_encode([
                'status' => false,
                'reason' => 'Order Not Found',
            ]));

            return;
        }

        $this->getResponse()->setBody(json_encode([
            'status' => true
        ]));

        return;
    }
}
