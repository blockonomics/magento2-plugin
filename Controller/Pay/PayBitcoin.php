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
        $newInvoiceCreated = $this->blockonomicsPayment->createInvoice();

        if($newInvoiceCreated) {
            $this->blockonomicsPayment->updateOrderStateAndStatus();
        }

        $resultPage = $this->_resultPageFactory->create();
        return $resultPage;
    }
}