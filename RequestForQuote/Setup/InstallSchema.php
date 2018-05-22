<?php
/**
 * Landofcoder
 * 
 * NOTICE OF LICENSE
 * 
 * This source file is subject to the Landofcoder.com license that is
 * available through the world-wide-web at this URL:
 * http://landofcoder.com/license
 * 
 * DISCLAIMER
 * 
 * Do not edit or add to this file if you wish to upgrade this extension to newer
 * version in the future.
 * 
 * @category   Landofcoder
 * @package    Lof_RequestForQuote
 * @copyright  Copyright (c) 2017 Landofcoder (http://www.landofcoder.com/)
 * @license    http://www.landofcoder.com/LICENSE-1.0.html
 */

namespace Lof\RequestForQuote\Setup;

use Magento\Framework\Setup\InstallSchemaInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;
use \Magento\Framework\DB\Ddl\Table;

class InstallSchema implements InstallSchemaInterface
{
    /**
     * {@inheritdoc}
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    public function install(SchemaSetupInterface $setup, ModuleContextInterface $context)
    {
        $installer = $setup;
        $installer->startSetup();
        $quoteTable = $installer->getTable('quote');
        $installer->getConnection()->addColumn(
            $quoteTable,
            'rfq_parent_quote_id',
            [
                'type'     => Table::TYPE_INTEGER,
                'length'   => null,
                'nullable' => true,
                'comment'  => 'RFQ Quote'
            ]
        );

        $installer->getConnection()->modifyColumn(
            $quoteTable,
            'customer_note',
            [
                'type' => Table::TYPE_TEXT
            ]
            );

        /**
         * Create table 'lof_rfq_quote'
         */
        $setup->getConnection()->dropTable($setup->getTable('lof_rfq_quote'));
        $table = $installer->getConnection()->newTable(
            $installer->getTable('lof_rfq_quote')
        )->addColumn(
            'entity_id',
            Table::TYPE_INTEGER,
            null,
            ['identity' => true, 'unsigned' => true, 'nullable' => false, 'primary' => true],
            'Entity Id'
        )->addColumn(
            'quote_id',
            Table::TYPE_INTEGER,
            null,
            ['nullable' => false],
            'Quote Id'
        )->addColumn(
            'increment_id',
            Table::TYPE_TEXT,
            32,
            [],
            'Increment Id'
        )->addColumn(
            'customer_id',
            Table::TYPE_INTEGER,
            null,
            ['nullable' => false],
            'Customer Id'
        )->addColumn(
            'status',
            Table::TYPE_TEXT,
            32,
            ['unsigned' => true],
            'Status'
        )->addColumn(
            'target_quote',
            Table::TYPE_INTEGER,
            null,
            ['nullable' => false],
            'Backend Target Quote'
        )->addColumn(
            'token',
            Table::TYPE_TEXT,
            255,
            ['nullable' => false],
            'Token'
        )->addColumn(
            'updated_at',
            Table::TYPE_TIMESTAMP,
            null,
            ['nullable' => false, 'default' => Table::TIMESTAMP_INIT],
            'Updated At'
        )->addColumn(
            'created_at',
            Table::TYPE_TIMESTAMP,
            null,
            ['nullable' => false, 'default' => Table::TIMESTAMP_INIT],
            'Created At'
        )->addColumn(
            'expiry',
            Table::TYPE_TIMESTAMP,
            null,
            ['nullable' => false],
            'Expiry'
        )->setComment(
            'RFQ Quote Table'
        );
        $installer->getConnection()->createTable($table);

        $installer->endSetup();
    }
}
