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

class Status extends Action
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
        $uuid = $this->getRequest()->getParam('uuid');
        $this->payBitcoin->setUuid($uuid);

        $resultPage = $this->resultPageFactory->create();
        return $resultPage;
    }
}
