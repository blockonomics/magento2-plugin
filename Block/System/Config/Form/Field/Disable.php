<?php
/**
 * Blockonomics disabled form for admin view
 *
 * @category    Blockonomics
 * @package     Blockonomics_Merchant
 * @author      Blockonomics
 * @copyright   Blockonomics (https://blockonomics.co)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

namespace Blockonomics\Merchant\Block\System\Config\Form\Field;

use Magento\Framework\Data\Form\Element\AbstractElement;

class Disable extends \Magento\Config\Block\System\Config\Form\Field
{

    protected function _getElementHtml(AbstractElement $element)
    {
        $element->setData('readonly', 1);
        $element->setData('placeholder', 'If URL is empty, refresh Magento cache and then come back to this page');
        return $element->getElementHtml();
    }
}
