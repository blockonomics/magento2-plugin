<?php
/**
 * Blockonomics PayBitcoin block
 *
 * @category    Blockonomics
 * @package     Blockonomics_Merchant
 * @author      Blockonomics
 * @copyright   Blockonomics (https://blockonomics.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
namespace Blockonomics\Merchant\Block;

use \Magento\Framework\Exception\LocalizedException;
use \Magento\Framework\View\Element\Template;
use \Magento\Framework\View\Element\Template\Context;
use \Magento\Framework\Registry;
use \Blockonomics\Merchant\Controller\Pay\PayBitcoin as PayBitcoinAction;
use Magento\Checkout\Model\Session;

class PayBitcoin extends Template
{
    /**
     * Core registry
     * @var Registry
     */
    protected $_coreRegistry;

    protected $_checkoutSession;

    /**
     * Constructor
     * @param Context $context
     * @param Registry $coreRegistry
     * @param array $data
     */
    public function __construct(
        Context $context,
        Session $checkoutSession,
        Registry $coreRegistry
    ) {
        $this->_coreRegistry = $coreRegistry;
        $this->_checkoutSession = $checkoutSession;
        parent::__construct($context);
    }

    /**
     * @return Current order id
     * @throws LocalizedException
     */
    public function getOrderId()
    {
        $id = $this->checkoutSession->getLastOrderId();

        return $id;
    }
}