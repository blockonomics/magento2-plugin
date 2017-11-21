<?php
/**
 * A Magento 2 module named Blockonomics/Merchant
 * Copyright (C) 2017
 *
 * This file is part of Blockonomics/Merchant.
 *
 * Blockonomics/Merchant is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program. If not, see <http://www.gnu.org/licenses/>.
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
