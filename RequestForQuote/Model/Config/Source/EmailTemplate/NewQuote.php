<?php
/**
 * Landofcoder
 * 
 * NOTICE OF LICENSE
 * 
 * This source file is subject to the landofcoder.com license that is
 * available through the world-wide-web at this URL:
 * http://landofcoder.com/license
 * 
 * DISCLAIMER
 * 
 * Do not edit or add to this file if you wish to upgrade this extension to newer
 * version in the future.
 * 
 * @category   Landofcoder
 * @package    Lof_FAQ
 * @copyright  Copyright (c) 2016 Landofcoder (http://www.landofcoder.com/)
 * @license    http://www.landofcoder.com/LICENSE-1.0.html
 */
namespace Lof\RequestForQuote\Model\Config\Source\EmailTemplate;

class NewQuote implements \Magento\Framework\Option\ArrayInterface
{
    /**
     * @param \Magento\Email\Model\ResourceModel\Template\CollectionFactory $templatesFactory 
     * @param \Magento\Email\Model\Template\Config                          $emailConfig      
     */
    public function  __construct(
        \Magento\Email\Model\ResourceModel\Template\CollectionFactory $templatesFactory,
        \Magento\Email\Model\Template\Config $emailConfig
        ) {
        $this->_templatesFactory = $templatesFactory;
         $this->_emailConfig = $emailConfig;
    }
    /**
     * Options getter
     *
     * @return array
     */
    public function toOptionArray()
    {
        $collection = $this->_templatesFactory->create();
        $collection->load();
        $options = $collection->toOptionArray();
        $templateId = 'rfq_new_quote';
        $templateLabel = $this->_emailConfig->getTemplateLabel($templateId);
        $templateLabel = __('%1 (Default)', $templateLabel);
        array_unshift($options, ['value' => $templateId, 'label' => $templateLabel]);
        $emailTemplates = [];
        foreach ($options as $k => $v) {
            $emailTemplates[$v['value']] = $v['label'];
        }
        return $emailTemplates;
    }
}
