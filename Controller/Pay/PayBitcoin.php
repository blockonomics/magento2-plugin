<?php
/**
 * Blockonomics PayBitcoin controller
 *
 * @category    Blockonomics
 * @package     Blockonomics_Merchant
 * @author      Blockonomics
 * @copyright   Blockonomics (https://blockonomics.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
namespace Blockonomics\Merchant\Controller\Pay;

use Blockonomics\Merchant\Model\Payment as BlockonomicsPayment;
use \Magento\Framework\App\Action\Action;
use \Magento\Framework\View\Result\PageFactory;
use \Magento\Framework\View\Result\Page;
use \Magento\Framework\App\Action\Context;
use \Magento\Framework\Exception\LocalizedException;
use \Magento\Framework\App\ObjectManager;

class PayBitcoin extends Action
{

    protected $_resultPageFactory;
    protected $blockonomicsPayment;

    /**
     * @param Context $context
     * @param Registry $coreRegistry
     * @param PageFactory $resultPageFactory
     */
    public function __construct(
        Context $context,
        PageFactory $resultPageFactory,
        BlockonomicsPayment $blockonomicsPayment
    ) {
        parent::__construct(
            $context
        );
        $this->_resultPageFactory = $resultPageFactory;
        $this->blockonomicsPayment = $blockonomicsPayment;
    }

    /**
     * @return Page
     * @throws LocalizedException
     */
    public function execute()
    {
        /**
         * TODO: This part will be moved to callback from blockonomics API
         */
        /*
        $newInvoiceCreated = $this->blockonomicsPayment->createInvoice();

        if($newInvoiceCreated) {
            $this->blockonomicsPayment->updateOrderStateAndStatus();
        }
        */
        /**
         * TODO Block ends here
         */


        $objectManager = ObjectManager::getInstance();       
        $bitcoinTransaction = $objectManager->create('Blockonomics\Merchant\Model\BitcoinTransaction');
        $bitcoinTransaction->setValue(30);
        $bitcoinTransaction->save();

        $resultPage = $this->_resultPageFactory->create();
        return $resultPage;
    }
}