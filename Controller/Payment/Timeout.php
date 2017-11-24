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
use Blockonomics\Merchant\Block\PayBitcoin;
use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Sales\Model\Order;
use Magento\Framework\App\ObjectManager;
use Blockonomics\Merchant\Model\BitcoinTransaction;
use Blockonomics\Merchant\Model\ResourceModel\BitcoinTransaction\Collection;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Store\Model\ScopeInterface;

class Timeout extends Action
{
    protected $order;
    protected $blockonomicsPayment;
    protected $transactionCollection;
    protected $payBitcoin;
    protected $scopeConfig;

    public function __construct(
        Context $context,
        Order $order,
        BlockonomicsPayment $blockonomicsPayment,
        PayBitcoin $payBitcoin,
        ScopeConfigInterface $scopeConfig,
        Collection $transactionCollection
    ) {
    
        parent::__construct($context);

        $this->order = $order;
        $this->blockonomicsPayment = $blockonomicsPayment;
        $this->payBitcoin = $payBitcoin;
        $this->scopeConfig = $scopeConfig;
        $this->transactionCollection = $transactionCollection;
    }

    /**
     * When order payment has timed out, this page is called, update payment status to On Hold
     *
     * @return void
     */
    public function execute()
    {

        // GET parameters from callback
        $secret = $this->getRequest()->getParam('secret');
        $addr   = $this->getRequest()->getParam('addr');

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
            $this->blockonomicsPayment->updateOrderStateAndStatus($orderId, 'holded');

            $order = $this->payBitcoin->getOrderById($orderId);
            $order->addStatusHistoryComment('Order payment has timed out');
            $order->save();
        }

        $item->save();

        $this->_redirect('checkout/onepage/failure');
        //$this->getResponse()->setBody('OK');
    }
}