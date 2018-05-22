<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Lof\RequestForQuote\Model\Config\Source;

use Magento\Customer\Api\GroupManagementInterface;

/**
 * Customer group attribute source
 *
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class CustomerGroups implements \Magento\Framework\Option\ArrayInterface
{

    protected $_customerGroup;


    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Customer\Model\ResourceModel\Group\Collection $customerGroup,        
        array $data = []
        ) {
        $this->_customerGroup = $customerGroup;
    }

    public function getCustomerGroups() {
        $customerGroups = $this->_customerGroup->toOptionArray();
        array_unshift($customerGroups, array('value'=>'', 'label'=>'Any'));
        return $customerGroups;
    }

    public function toOptionArray()
    {
        return $this->_customerGroup->toOptionArray();
    }
}
