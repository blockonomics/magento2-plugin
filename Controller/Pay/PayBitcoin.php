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

use \Magento\Framework\App\Action\Action;
use \Magento\Framework\View\Result\PageFactory;
use \Magento\Framework\View\Result\Page;
use \Magento\Framework\App\Action\Context;
use \Magento\Framework\Exception\LocalizedException;
use \Magento\Sales\Model\Service\InvoiceService:

class PayBitcoin extends Action
{

    protected $_resultPageFactory;
    protected $_invoiceService;

    /**
     * @param Context $context
     * @param Registry $coreRegistry
     * @param PageFactory $resultPageFactory
     */
    public function __construct(
        Context $context,
        PageFactory $resultPageFactory,
        InvoiceService $invoiceService
    ) {
        parent::__construct(
            $context
        );
        $this->_resultPageFactory = $resultPageFactory;
        $this->_invoiceService = $invoiceService;
    }

    /**
     * @return Page
     * @throws LocalizedException
     */
    public function execute()
    {
        $resultPage = $this->_resultPageFactory->create();
        return $resultPage;
    }
}