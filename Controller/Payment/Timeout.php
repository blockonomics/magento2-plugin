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
use \Magento\Framework\View\Result\PageFactory;
use \Magento\Framework\View\Result\Page;
use Magento\Backend\Model\Session as BackendSession;

class Timeout extends Action
{
    protected $order;
    protected $blockonomicsPayment;
    protected $transactionCollection;
    protected $payBitcoin;
    protected $scopeConfig;
    protected $resultPageFactory;
    protected $backendSession;

    public function __construct(
        Context $context,
        Order $order,
        BackendSession $backendSession,
        BlockonomicsPayment $blockonomicsPayment,
        PayBitcoin $payBitcoin,
        ScopeConfigInterface $scopeConfig,
        Collection $transactionCollection,
        PageFactory $resultPageFactory
    ) {
    
        parent::__construct($context);

        $this->order = $order;
        $this->blockonomicsPayment = $blockonomicsPayment;
        $this->payBitcoin = $payBitcoin;
        $this->scopeConfig = $scopeConfig;
        $this->transactionCollection = $transactionCollection;
        $this->resultPageFactory = $resultPageFactory;
        $this->backendSession = $backendSession;
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
        $session_secret = $this->backendSession->getData('sessionSecret', true);

        // If callback secret does not match, return
        if ($session_secret != $stored_secret) {
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

        $resultPage = $this->resultPageFactory->create();
        return $resultPage;
    }
}