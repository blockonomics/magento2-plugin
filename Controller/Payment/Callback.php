<?php
/**
 * Blockonomics Callback controller
 *
 * @category    Blockonomics
 * @package     Blockonomics_Merchant
 * @author      Blockonomics
 * @copyright   Blockonomics (https://blockonomics.co)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
namespace Blockonomics\Merchant\Controller\Payment;

use Blockonomics\Merchant\Model\Payment as BlockonomicsPayment;
use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Sales\Model\Order;

class Callback extends Action
{
    protected $order;
    protected $blockonomicsPayment;

    /**
     * @param Context $context
     * @param Order $order
     * @param Payment|BlockonomicsPayment $blockonomicsPayment
     * @internal param \Magento\Framework\View\Result\PageFactory $resultPageFactory
     */
    public function __construct(
        Context $context,
        Order $order,
        BlockonomicsPayment $blockonomicsPayment
    )
    {
        parent::__construct($context);

        $this->order = $order;
        $this->blockonomicsPayment = $blockonomicsPayment;
    }

    /**
     * @return void
     */
    public function execute()
    {

        $newInvoiceCreated = $this->blockonomicsPayment->createInvoice();

        if($newInvoiceCreated) {
            $this->blockonomicsPayment->updateOrderStateAndStatus();
        }
        $this->getResponse()->setBody('OK');
    }
}
