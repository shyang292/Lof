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

class Edit extends \Magento\Backend\Block\Widget\Form\Container
{
    /**
     * Core registry
     *
     * @var \Magento\Framework\Registry
     */
    protected $_coreRegistry = null;

    /**
     * Sales config
     *
     * @var \Magento\Sales\Model\Config
     */
    protected $_salesConfig;

    /**
     * Reorder helper
     *
     * @var \Magento\Sales\Helper\Reorder
     */
    protected $_reorderHelper;

    /**
     * @param \Magento\Backend\Block\Widget\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Sales\Model\Config $salesConfig
     * @param \Magento\Sales\Helper\Reorder $reorderHelper
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Widget\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Sales\Model\Config $salesConfig,
        \Magento\Sales\Helper\Reorder $reorderHelper,
        array $data = []
    ) {
        $this->_reorderHelper = $reorderHelper;
        $this->_coreRegistry  = $registry;
        $this->_salesConfig   = $salesConfig;
        parent::__construct($context, $data);
    }

    /**
     * Constructor
     *
     * @return void
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.NPathComplexity)
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    protected function _construct()
    {
        $this->_objectId   = 'entity_id';
        $this->_blockGroup = 'Lof_RequestForQuote';
        $this->_controller = 'adminhtml_quote';

        parent::_construct();
        $this->buttonList->remove('save');
        $this->setId('sales_order_view');

        $quote = $this->getQuote();

        $this->addButton(
                'delete',
                [
                    'label' => __('Delete'),
                    'onclick' => 'setLocation(\'' . $this->getUrl('quotation/*/delete', ['entity_id' => $quote->getId()]) . '\')'
                ]
            );
        $this->buttonList->update('back', 'onclick', 'setLocation(\'' . $this->getUrl('quotation/*/index') . '\')');
        $mageQuote = $this->getMageQuote();

        if ($quote->getStatus() != \Lof\RequestForQuote\Model\Quote::STATE_ORDERED) {
            $message = __('Are you sure you want to send a confirmation email to customer?');
            $this->addButton(
                'send_notification',
                [
                    'label' => __('Send Email'),
                    'class' => 'send-email',
                    'onclick' => "confirmSetLocation('{$message}', '{$this->getUrl('quotation/*/sendEmail', ['entity_id' => $quote->getId(), 'magequote_id' => $mageQuote->getId()])}')"
                ]
            );
        }

        if ($mageQuote && $mageQuote->getCustomerId()) {

            $this->addButton(
                'create_order',
                [
                    'label' => __('Create Order'),
                    'class' => 'save action-secondary',
                    'onclick' => 'setLocation(\'' . $this->getUrl('quotation/*/createorder', ['entity_id' => $quote->getId(), 'magequote_id' => $mageQuote->getId()]) . '\')'
                ]
            );
        }

        $this->addButton(
            'save',
            [
                'label' => __('Save Quote'),
                'class' => 'save primary',
                'data_attribute' => [
                    'mage-init' => ['button' => ['event' => 'save', 'target' => '#edit_form']],
                ]
            ],
            1
        );

        $this->addButton(
            'save_email',
            [
                'label' => __('Save Quote & Send Update Email'),
                'class' => 'save primary'
            ],
            1
        );
        
        $message = __('Send an email contains a purchase link to customer email. Customer click and redirect to site.');
            $this->addButton(
                'accept_quote',
                [
                    'label' => __('Accept Request'),
                    'class' => 'accept-quote action-secondary',
                    'onclick' => "confirmSetLocation('{$message}', '{$this->getUrl('quotation/*/accept', ['entity_id' => $quote->getId(), 'magequote_id' => $mageQuote->getId()])}')"
                ]
            );
    }

    /**
     * Retrieve order model object
     *
     * @return \Magento\Sales\Model\Order
     */
    public function getOrder()
    {
        return $this->_coreRegistry->registry('sales_order');
    }

    /**
     * Retrieve order model object
     *
     * @return \Magento\Sales\Model\Order
     */
    public function getMageQuote()
    {
        return $this->_coreRegistry->registry('mage_quote');
    }

    /**
     * Retrieve order model object
     *
     * @return \Magento\Sales\Model\Order
     */
    public function getQuote()
    {
        return $this->_coreRegistry->registry('quotation_quote');
    }

    /**
     * Retrieve Order Identifier
     *
     * @return int
     */
    public function getOrderId()
    {
        return $this->getOrder() ? $this->getOrder()->getId() : null;
    }

    /**
     * Get header text
     *
     * @return \Magento\Framework\Phrase
     */
    public function getHeaderText()
    {
        $_extOrderId = $this->getOrder()->getExtOrderId();
        if ($_extOrderId) {
            $_extOrderId = '[' . $_extOrderId . '] ';
        } else {
            $_extOrderId = '';
        }
        return __(
            'Order # %1 %2 | %3',
            $this->getOrder()->getRealOrderId(),
            $_extOrderId,
            $this->formatDate(
                $this->_localeDate->date(new \DateTime($this->getOrder()->getCreatedAt())),
                \IntlDateFormatter::MEDIUM,
                true
            )
        );
    }

    /**
     * URL getter
     *
     * @param string $params
     * @param array $params2
     * @return string
     */
    public function getUrl($params = '', $params2 = [])
    {
        $params2['quote_id'] = $this->getOrderId();
        return parent::getUrl($params, $params2);
    }

    /**
     * Edit URL getter
     *
     * @return string
     */
    public function getEditUrl()
    {
        return $this->getUrl('sales/quote_save/start');
    }

    /**
     * Email URL getter
     *
     * @return string
     */
    public function getEmailUrl()
    {
        return $this->getUrl('sales/*/email');
    }

    /**
     * Cancel URL getter
     *
     * @return string
     */
    public function getCancelUrl()
    {
        return $this->getUrl('sales/*/cancel');
    }

    /**
     * Invoice URL getter
     *
     * @return string
     */
    public function getInvoiceUrl()
    {
        return $this->getUrl('sales/order_invoice/start');
    }

    /**
     * Credit memo URL getter
     *
     * @return string
     */
    public function getCreditmemoUrl()
    {
        return $this->getUrl('sales/order_creditmemo/start');
    }

    /**
     * Hold URL getter
     *
     * @return string
     */
    public function getHoldUrl()
    {
        return $this->getUrl('sales/*/hold');
    }

    /**
     * Unhold URL getter
     *
     * @return string
     */
    public function getUnholdUrl()
    {
        return $this->getUrl('sales/*/unhold');
    }

    /**
     * Ship URL getter
     *
     * @return string
     */
    public function getShipUrl()
    {
        return $this->getUrl('adminhtml/order_shipment/start');
    }

    /**
     * Comment URL getter
     *
     * @return string
     */
    public function getCommentUrl()
    {
        return $this->getUrl('sales/*/comment');
    }

    /**
     * Reorder URL getter
     *
     * @return string
     */
    public function getReorderUrl()
    {
        return $this->getUrl('sales/order_create/reorder');
    }

    /**
     * Payment void URL getter
     *
     * @return string
     */
    public function getVoidPaymentUrl()
    {
        return $this->getUrl('sales/*/voidPayment');
    }

    /**
     * Check permission for passed action
     *
     * @param string $resourceId
     * @return bool
     */
    protected function _isAllowedAction($resourceId)
    {
        return $this->_authorization->isAllowed($resourceId);
    }

    /**
     * Return back url for view grid
     *
     * @return string
     */
    public function getBackUrl()
    {
        if ($this->getOrder() && $this->getOrder()->getBackUrl()) {
            return $this->getOrder()->getBackUrl();
        }

        return $this->getUrl('sales/*/');
    }

    /**
     * Payment review URL getter
     *
     * @param string $action
     * @return string
     */
    public function getReviewPaymentUrl($action)
    {
        return $this->getUrl('sales/*/reviewPayment', ['action' => $action]);
    }

    /**
     * @param \Magento\Sales\Model\Order $order
     * @return \Magento\Framework\Phrase
     */
    protected function getEditMessage($order)
    {
        // see if order has non-editable products as items
        $nonEditableTypes = $this->getNonEditableTypes($order);
        if (!empty($nonEditableTypes)) {
            return __(
                'This order contains (%1) items and therefore cannot be edited through the admin interface. ' .
                'If you wish to continue editing, the (%2) items will be removed, ' .
                ' the order will be canceled and a new order will be placed.',
                implode(', ', $nonEditableTypes),
                implode(', ', $nonEditableTypes)
            );
        }
        return __('Are you sure? This order will be canceled and a new one will be created instead.');
    }

    /**
     * @param \Magento\Sales\Model\Order $order
     * @return array
     */
    protected function getNonEditableTypes($order)
    {
        return array_keys(
            $this->getOrder()->getResource()->aggregateProductsByTypes(
                $order->getId(),
                $this->_salesConfig->getAvailableProductTypes(),
                false
            )
        );
    }

        /**
     * Prepare layout
     *
     * @return \Magento\Framework\View\Element\AbstractBlock
     */
    protected function _prepareLayout()
    {
        $this->_formScripts[] = "
        require([
        'jquery',
        'mage/backend/form'
        ], function(){
            jQuery('#save_email').click(function(){
                var actionUrl = jQuery('#edit_form').attr('action') + 'send/1';
                jQuery('#edit_form').attr('action', actionUrl);
                jQuery('#edit_form').submit();
            });
        });";
        return parent::_prepareLayout();
    }
}
