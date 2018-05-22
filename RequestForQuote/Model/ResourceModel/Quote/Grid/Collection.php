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

namespace Lof\RequestForQuote\Model\ResourceModel\Quote\Grid;

use Magento\Framework\Api\Search\SearchResultInterface;
use Magento\Framework\Search\AggregationInterface;
use Lof\RequestForQuote\Model\ResourceModel\Quote\Collection as FormCollection;

class Collection extends FormCollection implements SearchResultInterface
{
    /**
     * @var AggregationInterface
     */
    protected $aggregations;

    /**
     * [__construct description]
     * @param \Magento\Framework\Data\Collection\EntityFactoryInterface    $entityFactory 
     * @param \Psr\Log\LoggerInterface                                     $logger        
     * @param \Magento\Framework\Data\Collection\Db\FetchStrategyInterface $fetchStrategy 
     * @param \Magento\Framework\Event\ManagerInterface                    $eventManager  
     * @param                                                              $mainTable     
     * @param                                                              $eventPrefix   
     * @param                                                              $eventObject   
     * @param                                                              $resourceModel 
     * @param string                                                       $model         
     * @param \Magento\Framework\DB\Adapter\AdapterInterface|null          $connection    
     * @param \Magento\Framework\Model\ResourceModel\Db\AbstractDb|null    $resource      
     */
    public function __construct(
        \Magento\Framework\Data\Collection\EntityFactoryInterface $entityFactory,
        \Psr\Log\LoggerInterface $logger,
        \Magento\Framework\Data\Collection\Db\FetchStrategyInterface $fetchStrategy,
        \Magento\Framework\Event\ManagerInterface $eventManager,
        $mainTable,
        $eventPrefix,
        $eventObject,
        $resourceModel,
        $model = 'Magento\Framework\View\Element\UiComponent\DataProvider\Document',
        $connection = null,
        \Magento\Framework\Model\ResourceModel\Db\AbstractDb $resource = null
    ) {
        parent::__construct(
            $entityFactory,
            $logger,
            $fetchStrategy,
            $eventManager,
            $connection,
            $resource
        );
        $this->_eventPrefix = $eventPrefix;
        $this->_eventObject = $eventObject;
        $this->_init($model, $resourceModel);
        $this->setMainTable($mainTable);
    }

    /**
     * @return AggregationInterface
     */
    public function getAggregations()
    {
        return $this->aggregations;
    }

    /**
     * @param AggregationInterface $aggregations
     * @return $this
     */
    public function setAggregations($aggregations)
    {
        $this->aggregations = $aggregations;
    }


    /**
     * Retrieve all ids for collection
     * Backward compatibility with EAV collection
     *
     * @param int $limit
     * @param int $offset
     * @return array
     */
    public function getAllIds($limit = null, $offset = null)
    {
        return $this->getConnection()->fetchCol($this->_getAllIdsSelect($limit, $offset), $this->_bindParams);
    }

    /**
     * Get search criteria.
     *
     * @return \Magento\Framework\Api\SearchCriteriaInterface|null
     */
    public function getSearchCriteria()
    {
        return null;
    }

    /**
     * Set search criteria.
     *
     * @param \Magento\Framework\Api\SearchCriteriaInterface $searchCriteria
     * @return $this
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function setSearchCriteria(\Magento\Framework\Api\SearchCriteriaInterface $searchCriteria = null)
    {
        return $this;
    }

    /**
     * Get total count.
     *
     * @return int
     */
    public function getTotalCount()
    {
        return $this->getSize();
    }

    /**
     * Set total count.
     *
     * @param int $totalCount
     * @return $this
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function setTotalCount($totalCount)
    {
        return $this;
    }

    /**
     * Set items list.
     *
     * @param \Magento\Framework\Api\ExtensibleDataInterface[] $items
     * @return $this
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function setItems(array $items = null)
    {
        return $this;
    }

    /**
     * Join store relation table if there is store filter
     *
     * @return void
     */
    protected function _renderFiltersBefore()
    {
        $this->getSelect()->joinInner(
            ['quote_table' => $this->getTable('quote')],
            'main_table.quote_id = quote_table.entity_id',
            ['quote_table.customer_firstname', 'quote_table.customer_lastname', 'quote_table.store_id', 'quote_table.customer_group_id', 'quote_table.customer_email', 'quote_table.base_grand_total', 'quote_table.grand_total', "lof_entity_id" => 'main_table.entity_id']
        )->group(
            'main_table.entity_id'
        );
    }
    /**
     * Perform operations after collection load
     *
     * @return $this
     */
    protected function _afterLoad()
    {
        foreach($this as $item) {
            $customer_firstname = $item->getData("customer_firstname");
            $customer_lastname = $item->getData("customer_lastname");
            if(!$customer_firstname) {
                $customer_firstname = $item->getData("first_name");
            }
            if(!$customer_lastname) {
                $customer_lastname = $item->getData("last_name");
            }
            
            if(!$customer_lastname && !$customer_firstname) {
                $customerID = (int)$item->getData("customer_id");
                if($customerID){
                    $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
                    $customerObj = $objectManager->create('Magento\Customer\Model\Customer')
                                            ->load($customerID);

                    $customer_firstname = $customerObj->getFirstName();
                    $customer_lastname = $customerObj->getLastName();
                }
            }
            
            $item->setData("customer_firstname", $customer_firstname);
            $item->setData("customer_lastname", $customer_lastname);
        }
        return parent::_afterLoad();
    }
    /**
     * Create all ids retrieving select with limitation
     * Backward compatibility with EAV collection
     *
     * @param int $limit
     * @param int $offset
     * @return \Magento\Eav\Model\Entity\Collection\AbstractCollection
     */
    protected function _getAllIdsSelect($limit = null, $offset = null)
    {
        $idsSelect = clone $this->getSelect();
        $idsSelect->reset(\Magento\Framework\DB\Select::ORDER);
        $idsSelect->reset(\Magento\Framework\DB\Select::LIMIT_COUNT);
        $idsSelect->reset(\Magento\Framework\DB\Select::LIMIT_OFFSET);
        $idsSelect->reset(\Magento\Framework\DB\Select::COLUMNS);
        $idsSelect->columns($this->getResource()->getIdFieldName(), 'main_table');
        $idsSelect->limit($limit, $offset);
        return $idsSelect;
    }
}
