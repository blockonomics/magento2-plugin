<?php
/**
 * Blockonomics data upgrade
 *
 * @category    Blockonomics
 * @package     Blockonomics_Merchant
 * @author      Blockonomics
 * @copyright   Blockonomics (https://blockonomics.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

namespace Blockonomics\Merchant\Setup;

use Magento\Framework\Setup\UpgradeDataInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\App\ObjectManager;

class UpgradeData implements UpgradeDataInterface
{

    /**
     * {@inheritdoc}
     */
    public function upgrade(
        ModuleDataSetupInterface $setup,
        ModuleContextInterface $context
    ) {
        $installer = $setup;
        $installer->startSetup();
        
        $objectManager = ObjectManager::getInstance();

        if (version_compare($context->getVersion(), "0.1.1", "<")) {
            if ($installer->getTableRow($installer->getTable('core_config_data'), 'path', 'payment/blockonomics_merchant/title')) {
                $installer->updateTableRow(
                    $installer->getTable('core_config_data'),
                    'path',
                    'payment/blockonomics_merchant/title',
                    'value',
                    'Bitcoin'
                );
            }
        }

        if (version_compare($context->getVersion(), "0.1.2", "<")) {
            $status = $objectManager->create('Magento\Sales\Model\Order\Status');
            $status->setData('status', 'pending_bitcoin_confirmation')->setData('label', 'Pending bitcoin confirmation')->save();
        }
        
        if (version_compare($context->getVersion(), "0.1.3", "<")) {
            $statuses = $objectManager->create('Magento\Sales\Model\ResourceModel\Order\Status\Collection');

            foreach ($statuses as $status) {
                if ($status->getStatus() == 'pending_bitcoin_confirmation') {
                    $status->assignState('processing', false, true)->save();
                }
            }
        }
        
        $setup->endSetup();
    }
}
