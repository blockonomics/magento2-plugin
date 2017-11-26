<?php
/**
 * Blockonomics block for custom order field in admin view
 *
 * @category    Blockonomics
 * @package     Blockonomics_Merchant
 * @author      Blockonomics
 * @copyright   Blockonomics (https://blockonomics.co)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

namespace Blockonomics\Merchant\Block\Adminhtml\Order\View;

use Magento\Framework\View\Element\Template;
use Magento\Framework\View\Element\Template\Context;

class Custom extends Template
{

    public function __construct(
        Context $context
    ) {
        parent::__construct($context);
    }
}