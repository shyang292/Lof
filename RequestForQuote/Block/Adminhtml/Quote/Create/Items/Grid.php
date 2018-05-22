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

namespace Lof\RequestForQUote\Block\Adminhtml\Quote\Create\Items;

class Grid extends \Magento\Sales\Block\Adminhtml\Order\Create\Items\Grid
{
    /**
     * Retrieve quote model object
     *
     * @return \Magento\Quote\Model\Quote
     */
    public function getQuote()
    {
        return $this->_getSession()->getRfqQuote();
    }

    public function getRecommendHtml(Item $item){

        $block = $this->getLayout()->getBlock('item_row_recommend');
        $block->setItem($item);
        return $block->toHtml();
    }

//    public function getListPrice(Item $item){
//        return '10';
//        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
//        $resource = $objectManager->get('Magento\Framework\App\ResourceConnection');
//        $connection = $resource->getConnection();
//        $tableName = $resource->getTableName('quote_item');
//        $sql = "select base_price from " . $tableName . " where item_id =".$item->getId();
//        $result = $connection->fetchAll($sql);
//        return $result[0];
//    }
}