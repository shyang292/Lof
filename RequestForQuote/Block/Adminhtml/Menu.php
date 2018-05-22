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
 * @package    Lof_Formbuilder
 * @copyright  Copyright (c) 2016 Landofcoder (http://www.landofcoder.com/)
 * @license    http://www.landofcoder.com/LICENSE-1.0.html
 */

namespace Lof\RequestForQuote\Block\Adminhtml;

use Lof\All\Model\Config;

class Menu extends \Magento\Backend\Block\Template
{
    /**
     * @var null|array
     */
    protected $items = null;

    /**
     * Block template filename
     *
     * @var string
     */
    protected $_template = 'Lof_All::menu.phtml';


    public function __construct(\Magento\Backend\Block\Template\Context $context,
        array $data = []
    ) {
        parent::__construct($context);

    }//end __construct()


    public function getMenuItems()
    {
        if ($this->items === null) {
            $items = [
                        'quote_create'    => [
                            'title'    => __('Add New Quote'),
                            'url'      => $this->getUrl('*/quote_create'),
                            'resource' => 'Lof_RequestForQuote::quote_edit'
                                ],
                      'quote'    => [
                            'title'    => __('Manage Quotations'),
                            'url'      => $this->getUrl('*/quote/index'),
                            'resource' => 'Lof_RequestForQuote::quote'
                                ],
                      'customfield' => [
                            'title'    => __('Manage Custom Fields'),
                            'url'      => $this->getUrl('*/customfield/index'),
                            'resource' => 'Lof_RequestForQuote::customfield'
                                ],
                      'rule' => [
                            'title'    => __('Manage Quotation Rules'),
                            'url'      => $this->getUrl('*/rule/index'),
                            'resource' => 'Lof_RequestForQuote::rule'
                            ],
                      'settings' => [
                            'title'    => __('Settings'),
                            'url'      => $this->getUrl('adminhtml/system_config/edit/section/requestforquote'),
                            'resource' => 'Lof_RequestForQuote::configurations',
                                ],
                      'readme'   => [
                            'title'     => __('Guide'),
                            'url'       => Config::QUOTE_GUIDE,
                            'attr'      => ['target' => '_blank'],
                            'separator' => true,
                                ],
                      'support'  => [
                            'title' => __('Get Support'),
                            'url'   => Config::LANDOFCODER_TICKET,
                            'attr'  => ['target' => '_blank'],
                        ],
                     ];

            $items = [
                        'quote_create'    => [
                            'title'    => __('Add New Quote'),
                            'url'      => $this->getUrl('*/quote_create'),
                            'resource' => 'Lof_RequestForQuote::quote_edit'
                                ],
                      'quote'    => [
                            'title'    => __('Manage Quotations'),
                            'url'      => $this->getUrl('*/quote/index'),
                            'resource' => 'Lof_RequestForQuote::quote'
                                ],
                      'settings' => [
                            'title'    => __('Settings'),
                            'url'      => $this->getUrl('adminhtml/system_config/edit/section/requestforquote'),
                            'resource' => 'Lof_RequestForQuote::configurations',
                                ],
                      'readme'   => [
                            'title'     => __('Guide'),
                            'url'       => Config::QUOTE_GUIDE,
                            'attr'      => ['target' => '_blank'],
                            'separator' => true,
                                ],
                      'support'  => [
                            'title' => __('Get Support'),
                            'url'   => Config::LANDOFCODER_TICKET,
                            'attr'  => ['target' => '_blank'],
                        ],
                     ];
            foreach ($items as $index => $item) {
                if (array_key_exists('resource', $item)) {
                    if (!$this->_authorization->isAllowed($item['resource'])) {
                        unset($items[$index]);
                    }
                }
            }

            $this->items = $items;
        }//end if

        return $this->items;

    }//end getMenuItems()


    /**
     * @return array
     */
    public function getCurrentItem()
    {
        $items          = $this->getMenuItems();
        $controllerName = $this->getRequest()->getControllerName();
        if (array_key_exists($controllerName, $items)) {
            return $items[$controllerName];
        }

        return $items['quote'];

    }//end getCurrentItem()


    /**
     * @param array $item
     * @return string
     */
    public function renderAttributes(array $item)
    {
        $result = '';
        if (isset($item['attr'])) {
            foreach ($item['attr'] as $attrName => $attrValue) {
                $result .= sprintf(' %s=\'%s\'', $attrName, $attrValue);
            }
        }

        return $result;

    }//end renderAttributes()


    /**
     * @param $itemIndex
     * @return bool
     */
    public function isCurrent($itemIndex)
    {
        return $itemIndex == $this->getRequest()->getControllerName();

    }//end isCurrent()


}//end class
