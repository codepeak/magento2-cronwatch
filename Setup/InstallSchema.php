<?php

namespace Codepeak\Cronwatch\Setup;

use Magento\Framework\Setup\InstallSchemaInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;

/**
 * Class InstallSchema
 *
 * @package Codepeak\Cronwatch\Setup
 * @author  Robert Lord <robert@codepeak.se>
 */
class InstallSchema implements InstallSchemaInterface
{
    /**
     * {@inheritdoc}
     */
    public function install(SchemaSetupInterface $setup, ModuleContextInterface $context)
    {
        // Start the setup
        $setup->startSetup();

        // Setup a new table
        $tableCodepeakCronwatch = $setup->getConnection()->newTable($setup->getTable('codepeak_cronwatch'));

        // Add entity ID
        $tableCodepeakCronwatch->addColumn(
            'cronwatch_id',
            \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
            null,
            array('identity' => true, 'nullable' => false, 'primary' => true, 'unsigned' => true,),
            'Entity ID'
        );

        // Add linked ID
        $tableCodepeakCronwatch->addColumn(
            'cron_schedule_schedule_id',
            \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
            null,
            [],
            'cron_schedule_schedule_id'
        );

        // Create the table
        $setup->getConnection()->createTable($tableCodepeakCronwatch);

        // End the setup
        $setup->endSetup();
    }
}