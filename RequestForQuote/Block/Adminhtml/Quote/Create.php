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

namespace Lof\RequestForQuote\Block\Adminhtml\Quote;

class Create extends \Magento\Sales\Block\Adminhtml\Order\Create
{
    /**
     * Constructor
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_objectId = 'order_id';
        $this->_controller = 'order';
        $this->_mode = 'create';

        parent::_construct();

        $this->setId('sales_order_create');

        $customerId = $this->_sessionQuote->getCustomerId();
        $storeId = $this->_sessionQuote->getStoreId();

        $this->buttonList->update('save', 'label', __('Submit Quote'));
        $this->buttonList->update('save', 'onclick', 'order.submit()');
        $this->buttonList->update('save', 'class', 'primary');
        // Temporary solution, unset button widget. Will have to wait till jQuery migration is complete
        $this->buttonList->update('save', 'data_attribute', []);

        $this->buttonList->update('save', 'id', 'submit_order_top_button');
        if ($customerId === null || !$storeId) {
            $this->buttonList->update('save', 'style', 'display:none');
        }

        $this->buttonList->update('back', 'id', 'back_order_top_button');
        $this->buttonList->update('back', 'onclick', 'setLocation(\'' . $this->getBackUrl() . '\')');

        $this->buttonList->update('reset', 'id', 'reset_order_top_button');

        if ($customerId === null) {
            $this->buttonList->update('reset', 'style', 'display:none');
        } else {
            $this->buttonList->update('back', 'style', 'display:none');
        }

        $confirm = __('Are you sure you want to cancel this order?');
        $this->buttonList->update('reset', 'label', __('Cancel'));
        $this->buttonList->update('reset', 'class', 'cancel');
        $this->buttonList->update(
            'reset',
            'onclick',
            'deleteConfirm(\'' . $confirm . '\', \'' . $this->getCancelUrl() . '\')'
        );
    }

    /**
     * {@inheritdoc}
     *
     * @return $this
     */
    protected function _prepareLayout()
    {
        parent::_prepareLayout();
        $pageTitle = $this->getLayout()->createBlock('Lof\RequestForQuote\Block\Adminhtml\Quote\Create\Header')->toHtml();
        if (is_object($this->getLayout()->getBlock('page.title'))) {
            $this->getLayout()->getBlock('page.title')->setPageTitle($pageTitle);
        }
    }

    /**
     * Prepare header html
     *
     * @return string
     */
    public function getHeaderHtml()
    {
        $out = '<div id="order-header">' . $this->getLayout()->createBlock(
            'Lof\RequestForQuote\Block\Adminhtml\Quote\Create\Header'
        )->toHtml() . '</div>';
        return $out;
    }
    /**
     * Get cancel url
     *
     * @return string
     */
    public function getCancelUrl()
    {
        return $this->getUrl('quotation/quote/index');;
    }
    /**
     * Get URL for back (reset) button
     *
     * @return string
     */
    public function getBackUrl()
    {
        return $this->getUrl('quotation/quote/index');;
    }
}