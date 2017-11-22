<?php
/**
 * Blockonomics data installation
 *
 * @category    Blockonomics
 * @package     Blockonomics_Merchant
 * @author      Blockonomics
 * @copyright   Blockonomics (https://blockonomics.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
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

                $data_callback_url = [
                    'scope' => 'default',
                    'scope_id' => 0,
                    'path' => 'payment/blockonomics_merchant/callback_url',
                    'value' => $base_url . 'blockonomics/payment/callback?secret=' . $callback_secret,
                ];
                $setup->getConnection()
                    ->insertOnDuplicate($setup->getTable('core_config_data'), $data_callback_url, ['value']);
    }
}
