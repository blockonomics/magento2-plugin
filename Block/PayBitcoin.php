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
use \Magento\Backend\Model\Session;

class PayBitcoin extends Template
{
    /**
     * Core registry
     * @var Registry
     */
    protected $_coreRegistry;

    protected $backendSession;

    /**
     * Constructor
     * @param Context $context
     * @param array $data
     */
    public function __construct(
        Context $context,
        Session $backendSession
    ) {
        parent::__construct($context);
        $this->backendSession = $backendSession;
    }

    /**
     * @return Current order id
     * @throws LocalizedException
     */
    public function getOrderId()
    {
        return $this->backendSession->getData('orderId', false);
    }
}