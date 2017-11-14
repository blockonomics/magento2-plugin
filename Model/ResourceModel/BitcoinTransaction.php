<?php
/**
 * Blockonomics payment method model
 *
 * @category    Blockonomics
 * @package     Blockonomics_Merchant
 * @author      Blockonomics
 * @copyright   Blockonomics (https://blockonomics.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

namespace Blockonomics\Merchant\Model\ResourceModel;

use \Magento\Framework\Model\ResourceModel\Db\AbstractDb;

class BitcoinTransaction extends AbstractDb
{
    /**
     * @return void
     */
    protected function _construct()
    {
        $this->_init('blockonomics_bitcoin_orders', 'id');
    }
}