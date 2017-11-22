<?php
/**
 * Blockonomics schema installation
 *
 * @category    Blockonomics
 * @package     Blockonomics_Merchant
 * @author      Blockonomics
 * @copyright   Blockonomics (https://blockonomics.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

namespace Blockonomics\Merchant\Setup;

use Magento\Framework\Setup\InstallSchemaInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;
use Magento\Framework\DB\Ddl\Table;

class InstallSchema implements InstallSchemaInterface
{

    /**
     * {@inheritdoc}
     */
    public function install(
        SchemaSetupInterface $setup,
        ModuleContextInterface $context
    ) {
        $installer = $setup;
        $installer->startSetup();

        $tableName = $installer->getTable('blockonomics_bitcoin_orders');

        // Check if the table already exists
        if ($installer->getConnection()->isTableExists($tableName) != true) {
            $table = $installer->getConnection()
                ->newTable($tableName)
                ->addColumn(
                    'id',
                    Table::TYPE_INTEGER,
                    null,
                    [
                        'identity' => true,
                        'unsigned' => true,
                        'nullable' => false,
                        'primary' => true
                    ],
                    'ID'
                )
                ->addColumn(
                    'id_order',
                    Table::TYPE_INTEGER,
                    null,
                    [ 'unsigned' => true, 'nullable' => false ],
                    'Order ID'
                )
                ->addColumn(
                    'timestamp',
                    Table::TYPE_INTEGER,
                    null,
                    [ 'nullable' => false ],
                    'Order time stamp'
                )
                ->addColumn(
                    'addr',
                    Table::TYPE_TEXT,
                    null,
                    [ 'nullable' => false ],
                    'Bitcoin Address'
                )
                ->addColumn(
                    'txid',
                    Table::TYPE_TEXT,
                    null,
                    ['nullable' => false ],
                    'Transaction ID'
                )
                ->addColumn(
                    'status',
                    Table::TYPE_INTEGER,
                    null,
                    ['nullable' => false ],
                    'Transaction Status'
                )
                ->addColumn(
                    'value',
                    Table::TYPE_FLOAT,
                    null,
                    ['nullable' => false ],
                    'Transaction value in BTC'
                )
                ->addColumn(
                    'bits',
                    Table::TYPE_INTEGER,
                    null,
                    ['nullable' => false ],
                    'Value in satoshi'
                )
                ->addColumn(
                    'bits_payed',
                    Table::TYPE_INTEGER,
                    null,
                    ['nullable' => false ],
                    'Bitcoins payed(from transaction) in satoshi'
                )
                ->setComment('Blockonomics News Table')
                ->setOption('type', 'InnoDB')
                ->setOption('charset', 'utf8');

            $installer->getConnection()->createTable($table);
        }

        $setup->endSetup();
    }
}
