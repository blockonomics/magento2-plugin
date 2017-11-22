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
use Magento\Framework\App\ObjectManager;
use Blockonomics\Merchant\Model\BitcoinTransaction;
use Blockonomics\Merchant\Model\ResourceModel\BitcoinTransaction\Collection;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Store\Model\ScopeInterface;

class Callback extends Action
{
    protected $order;
    protected $blockonomicsPayment;
    protected $transactionCollection;
    protected $scopeConfig;

    public function __construct(
        Context $context,
        Order $order,
        BlockonomicsPayment $blockonomicsPayment,
        ScopeConfigInterface $scopeConfig,
        Collection $transactionCollection
    ) {
    
        parent::__construct($context);

        $this->order = $order;
        $this->blockonomicsPayment = $blockonomicsPayment;
        $this->scopeConfig = $scopeConfig;
        $this->transactionCollection = $transactionCollection;
    }

    /**
     * When this callback is called, get parameters from GET query and decide
     * correct action using them
     *
     * If status = 0, set payment pending
     * If status = 2 and paid amoun >= order total, set order processing and create invoice
     * If status = 2 and paid amoun < order total, set payment on hold
     *
     * @return void
     */
    public function execute()
    {

        // GET parameters from callback
        $secret = $this->getRequest()->getParam('secret');
        $status = $this->getRequest()->getParam('status');
        $addr   = $this->getRequest()->getParam('addr');
        $value  = $this->getRequest()->getParam('value');
        $txid   = $this->getRequest()->getParam('txid');

        // Get secret set in core_config_data
        $stored_secret = $this->scopeConfig->getValue('payment/blockonomics_merchant/callback_secret', ScopeInterface::SCOPE_STORE);

        // If callback secret does not match, return
        if ($secret != $stored_secret) {
            $this->getResponse()->setBody('AUTH ERROR');
            return;
        }

        $collection = $this->transactionCollection->addFieldToFilter('addr', $addr);

        foreach ($collection as $item) {
            $orderId = $item->getIdOrder();

            if ($status == 0) {
                $this->blockonomicsPayment->updateOrderStateAndStatus($orderId, 'pending');
            }

            if ($status == 2) {
                // Check if paid amount is greater or equal to order sum
                if ($value >= $item->getBits()) {
                    $newInvoiceCreated = $this->blockonomicsPayment->createInvoice($orderId);
                    $this->blockonomicsPayment->updateOrderStateAndStatus($orderId, 'processing');
                }

                if ($value < $item->getBits()) {
                    $this->blockonomicsPayment->updateOrderStateAndStatus($orderId, 'holded');
                }
            }

            $item->setStatus($status);
            $item->setBitsPayed($value);
            $item->setTxId($txid);

            $item->save();
        }

        $this->getResponse()->setBody('OK');
    }
}
