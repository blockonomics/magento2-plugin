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

use Magento\Framework\Setup\InstallDataInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;

class InstallData implements InstallDataInterface
{

    /**
     * {@inheritdoc}
     */
    public function install(
        ModuleDataSetupInterface $setup,
        ModuleContextInterface $context
    ) {

    		$callback_secret = sha1(openssl_random_pseudo_bytes(20));

				$data_secret = [
					'scope' => 'default',
					'scope_id' => 0,
					'path' => 'payment/blockonomics_merchant/callback_secret',
					'value' => $callback_secret,
				];
				$setup->getConnection()
					->insertOnDuplicate($setup->getTable('core_config_data'), $data_secret, ['value']);

				$objectManager = \Magento\Framework\App\ObjectManager::getInstance();
				$storeManager = $objectManager->get('\Magento\Store\Model\StoreManagerInterface');
				$base_url = $storeManager->getStore()->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_WEB);

				$data_secret = [
					'scope' => 'default',
					'scope_id' => 0,
					'path' => 'payment/blockonomics_merchant/callback_url',
					'value' => $base_url . 'blockonomics/payment/callback?secret=' . $callback_secret,
				];
				$setup->getConnection()
					->insertOnDuplicate($setup->getTable('core_config_data'), $data_secret, ['value']);

    }
}
