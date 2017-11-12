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
use \Magento\Framework\App\ObjectManager;
use Blockonomics\Merchant\Model\BitcoinTransaction;
use Blockonomics\Merchant\Model\ResourceModel\BitcoinTransaction\Collection;

class Callback extends Action
{
    protected $order;
    protected $blockonomicsPayment;
    protected $transactionCollection;

    /**
     * @param Context $context
     * @param Order $order
     * @param Payment|BlockonomicsPayment $blockonomicsPayment
     * @internal param \Magento\Framework\View\Result\PageFactory $resultPageFactory
     */
    public function __construct(
        Context $context,
        Order $order,
        BlockonomicsPayment $blockonomicsPayment,
        Collection $transactionCollection
    )
    {
        parent::__construct($context);

        $this->order = $order;
        $this->blockonomicsPayment = $blockonomicsPayment;
        $this->transactionCollection = $transactionCollection;
    }

    /**
     * @return void
     */
    public function execute()
    {

        $secret = $this->getRequest()->getParam('secret');

        $status = $this->getRequest()->getParam('status');
        $addr   = $this->getRequest()->getParam('addr');
        $value  = $this->getRequest()->getParam('value');
        $txid   = $this->getRequest()->getParam('txid');

        $collection = $this->transactionCollection->addFieldToFilter('addr', $addr);

        foreach($collection as $item){

            $orderId = $item->getIdOrder();

            $newInvoiceCreated = $this->blockonomicsPayment->createInvoice($orderId);
            if($newInvoiceCreated) {
                $this->blockonomicsPayment->updateOrderStateAndStatus($orderId);
            }

            $item->setStatus($status);
            $item->setBitsPayed($value);
            $item->setTxId($txid);
            $item->save();
        }
        

        $this->getResponse()->setBody('OK');
    }
}
