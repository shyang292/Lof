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

use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;
use Magento\Framework\Setup\UpgradeSchemaInterface;
use Magento\Framework\DB\Ddl\Table;

class UpgradeSchema implements UpgradeSchemaInterface
{
    /**
     * {@inheritdoc}
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    public function upgrade(SchemaSetupInterface $setup, ModuleContextInterface $context)
    {
        $installer = $setup;
        $installer->startSetup();
        $tableItems = $installer->getTable('lof_rfq_quote');
        $quoteTableItems = $installer->getTable('quote_item');

        //added by allen
        $installer->getConnection()->addColumn(
            $quoteTableItems,
            'recommend',
            [
                'type'     => Table::TYPE_SMALLINT,
                'length'   => null,
                'comment'  => 'this is a flag to mark whether the item is recommended or not'
            ]
        );
        $installer->getConnection()->addColumn(
            $quoteTableItems,
            'qty_showup',
            [
                'type'     => Table::TYPE_SMALLINT,
                'length'   => null,
                'comment'  => 'this is a quantity which show up in quote grid'
            ]
        );
        //-----------------------

        $installer->getConnection()->addColumn(
            $tableItems,
            'expiry',
            [
                'type'     => Table::TYPE_TIMESTAMP,
                'length'   => null,
                'nullable' => true,
                'comment'  => 'Expiry'
            ]
        );

        $installer->getConnection()->addColumn(
            $tableItems,
            'target_quote',
            [
                'type'     => Table::TYPE_INTEGER,
                'length'   => null,
                'nullable' => true,
                'comment'  => 'Target Quote'
            ]
        );

         //Update for version 1.0.2
        if (version_compare($context->getVersion(), '1.0.2', '<')) {
            $installer->getConnection()->addColumn(
                $tableItems,
                'shipping_amount',
                [
                    'type'     => Table::TYPE_DECIMAL,
                    'length'   => '12,4',
                    'nullable' => false,
                    'default'  => '0.0000',
                    'comment'  => 'Shipping Amount'
                ]
            );
            $installer->getConnection()->addColumn(
                $tableItems,
                'remind',
                [
                    'type'     => Table::TYPE_TIMESTAMP,
                    'length'   => null,
                    'nullable' => true,
                    'comment'  => 'Remind Date'
                ]
            );
            $installer->getConnection()->addColumn(
                $tableItems,
                'tax_amount',
                [
                    'type'     => Table::TYPE_DECIMAL,
                    'length'   => '12,4',
                    'nullable' => false,
                    'default'  => '0.0000',
                    'comment'  => 'Tax Amount'
                ]
            );
            $installer->getConnection()->addColumn(
                $tableItems,
                'tax_id',
                [
                    'type'     => Table::TYPE_TEXT,
                    'length'   => 150,
                    'nullable' => true,
                    'default'  => '',
                    'comment'  => 'Tax Id'
                ]
            );
            $installer->getConnection()->addColumn(
                $tableItems,
                'quote_track_code',
                [
                    'type'     => Table::TYPE_TEXT,
                    'length'   => 100,
                    'nullable' => true,
                    'default'  => '0',
                    'comment'  => 'Quote Tracking code'
                ]
            );
            $installer->getConnection()->addColumn(
                $tableItems,
                'campaign_source',
                [
                    'type'     => Table::TYPE_TEXT,
                    'length'   => 150,
                    'nullable' => true,
                    'default'  => '',
                    'comment'  => 'Campaign Source'
                ]
            );
             $installer->getConnection()->addColumn(
                $tableItems,
                'email',
                [
                    'type'     => Table::TYPE_TEXT,
                    'length'   => 200,
                    'nullable' => true,
                    'default'  => '',
                    'comment'  => 'Email Address'
                ]
            );
            $installer->getConnection()->addColumn(
                $tableItems,
                'first_name',
                [
                    'type'     => Table::TYPE_TEXT,
                    'length'   => 150,
                    'nullable' => true,
                    'default'  => '',
                    'comment'  => 'First Name'
                ]
            );
            $installer->getConnection()->addColumn(
                $tableItems,
                'last_name',
                [
                    'type'     => Table::TYPE_TEXT,
                    'length'   => 150,
                    'nullable' => true,
                    'default'  => '',
                    'comment'  => 'Last Name'
                ]
            );
            $installer->getConnection()->addColumn(
                $tableItems,
                'company',
                [
                    'type'     => Table::TYPE_TEXT,
                    'length'   => 255,
                    'nullable' => true,
                    'default'  => '',
                    'comment'  => 'Company'
                ]
            );
            $installer->getConnection()->addColumn(
                $tableItems,
                'telephone',
                [
                    'type'     => Table::TYPE_TEXT,
                    'length'   => 50,
                    'nullable' => true,
                    'default'  => '',
                    'comment'  => 'telephone Number'
                ]
            );
            $installer->getConnection()->addColumn(
                $tableItems,
                'address',
                [
                    'type'     => Table::TYPE_TEXT,
                    'length'   => 255,
                    'nullable' => true,
                    'default'  => '',
                    'comment'  => 'Address'
                ]
            );
            $installer->getConnection()->addColumn(
                $tableItems,
                'street',
                [
                    'type'     => Table::TYPE_TEXT,
                    'length'   => 255,
                    'nullable' => true,
                    'default'  => '',
                    'comment'  => 'Street'
                ]
            );
            $installer->getConnection()->addColumn(
                $tableItems,
                'city',
                [
                    'type'     => Table::TYPE_TEXT,
                    'length'   => 40,
                    'nullable' => true,
                    'default'  => '',
                    'comment'  => 'City'
                ]
            );
            $installer->getConnection()->addColumn(
                $tableItems,
                'region',
                [
                    'type'     => Table::TYPE_TEXT,
                    'length'   => 40,
                    'nullable' => true,
                    'default'  => '',
                    'comment'  => 'Region'
                ]
            );
            $installer->getConnection()->addColumn(
                $tableItems,
                'region_id',
                [
                    'type'     => Table::TYPE_INTEGER,
                    'length'   => 10,
                    'nullable' => true,
                    'unsigned' => true,
                    'default'  => '0',
                    'comment'  => 'Region Id'
                ]
            );
            $installer->getConnection()->addColumn(
                $tableItems,
                'postcode',
                [
                    'type'     => Table::TYPE_TEXT,
                    'length'   => 20,
                    'nullable' => true,
                    'default'  => '',
                    'comment'  => 'postcode'
                ]
            );
            $installer->getConnection()->addColumn(
                $tableItems,
                'country_id',
                [
                    'type'     => Table::TYPE_TEXT,
                    'length'   => 30,
                    'nullable' => true,
                    'default'  => '',
                    'comment'  => 'country_id'
                ]
            );
            $installer->getConnection()->addColumn(
                $tableItems,
                'shipping_note',
                [
                    'type'     => Table::TYPE_TEXT,
                    'length'   => 200,
                    'nullable' => true,
                    'default'  => '',
                    'comment'  => 'Shipping Note'
                ]
            );
            $installer->getConnection()->addColumn(
                $tableItems,
                'rate_id',
                [
                    'type'     => Table::TYPE_INTEGER,
                    'length'   => 10,
                    'nullable' => true,
                    'default'  => '0',
                    'comment'  => 'Quote shipping rate'
                ]
            );
            $installer->getConnection()->addColumn(
                $tableItems,
                'send_expiry_email',
                [
                    'type'     => Table::TYPE_SMALLINT,
                    'length'   => null,
                    'nullable' => false,
                    'default'  => '1',
                    'comment'  => 'Send Expiry Notification Email'
                ]
            );
            $installer->getConnection()->addColumn(
                $tableItems,
                'send_remind_email',
                [
                    'type'     => Table::TYPE_SMALLINT,
                    'length'   => null,
                    'nullable' => false,
                    'default'  => '1',
                    'comment'  => 'Send Remind Notification Email'
                ]
            );

            $installer->getConnection()->addColumn(
                $tableItems,
                'question',
                [
                    'type'     => Table::TYPE_TEXT,
                    'length'   => '2M',
                    'nullable' => true,
                    'default'  => '',
                    'comment'  => 'Quote Questions'
                ]
            );

        }

         //Update for version 1.0.3
        if (version_compare($context->getVersion(), '1.0.3', '<')) {
            $installer->getConnection()->addColumn(
                $quoteTableItems,
                'original_price',
                [
                    'type'     => Table::TYPE_DECIMAL,
                    'length'   => '12,4',
                    'nullable' => false,
                    'default'  => '0.0000',
                    'comment'  => 'Original Item Price'
                ]
            );

            $installer->getConnection()->addColumn(
                $tableItems,
                'aim',
                [
                    'type'     => Table::TYPE_TEXT,
                    'length'   => '2M',
                    'nullable' => true,
                    'default'  => '',
                    'comment'  => 'Aim'
                ]
            );

            $installer->getConnection()->addColumn(
                $tableItems,
                'deliverables',
                [
                    'type'     => Table::TYPE_TEXT,
                    'length'   => '2M',
                    'nullable' => true,
                    'default'  => '',
                    'comment'  => 'Deliverables'
                ]
            );

            $installer->getConnection()->addColumn(
                $tableItems,
                'lead_time',
                [
                    'type'     => Table::TYPE_TEXT,
                    'length'   => '2M',
                    'nullable' => true,
                    'default'  => '',
                    'comment'  => 'LeadTime'
                ]
            );

            $installer->getConnection()->addColumn(
                $tableItems,
                'notes',
                [
                    'type'     => Table::TYPE_TEXT,
                    'length'   => '2M',
                    'nullable' => true,
                    'default'  => '',
                    'comment'  => 'Notes'
                ]
            );

            $installer->getConnection()->addColumn(
                $tableItems,
                'internal_notes',
                [
                    'type'     => Table::TYPE_TEXT,
                    'length'   => '2M',
                    'nullable' => true,
                    'default'  => '',
                    'comment'  => 'Internal Notes'
                ]
            );

            $installer->getConnection()->addColumn(
                $tableItems,
                'ship_size',
                [
                    'type'     => Table::TYPE_TEXT,
                    'length'   => '2M',
                    'nullable' => true,
                    'default'  => '',
                    'comment'  => 'Ship Size'
                ]
            );

        }
        $installer->endSetup();
        
    }
}