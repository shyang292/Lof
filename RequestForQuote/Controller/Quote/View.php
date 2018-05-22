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

namespace Lof\RequestForQuote\Controller\Quote;

use Magento\Customer\Controller\AccountInterface;
use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\View\Result\PageFactory;
use Magento\Framework\Exception\NotFoundException;

class View extends \Lof\RequestForQuote\Controller\AbstractIndex
{
	/**
     * @var \Magento\Framework\Registry
     */
    protected $_coreRegistry;

    /**
     * @param Context
     * @param \Magento\Framework\View\Result\PageFactory
     * @param \Magento\Framework\Registry
     * @param \Lof\RequestForQuote\Model\QuoteFactory
     * @param \Magento\Quote\Model\QuoteFactory
     * @param \Lof\RequestForQuote\Helper\Data
     */
    public function __construct(
        Context $context,
        \Magento\Framework\View\Result\PageFactory $resultPageFactory,
        \Magento\Framework\Registry $registry,
        \Lof\RequestForQuote\Model\QuoteFactory $quoteFactory,
        \Magento\Quote\Model\QuoteFactory $mageQuoteFactory,
        \Lof\RequestForQuote\Helper\Data $rfqData
        ) {
        $this->resultPageFactory    = $resultPageFactory;
        $this->_coreRegistry        = $registry;
        parent::__construct($context);
        $this->mageQuoteFactory = $mageQuoteFactory;
        $this->quoteFactory = $quoteFactory;
        $this->rfqData = $rfqData;
    }

    public function execute()
    {
        $resultPage = $this->resultPageFactory->create();
        if ($quoteId = $this->getRequest()->getParam('quote_id')) {
            $quote = $this->quoteFactory->create()->load($quoteId);

            if ($this->rfqData->isExpired($quote)) {
                $quote->setStatus(\Lof\RequestForQuote\Model\Quote::STATE_EXPIRED);
                $quote->save();
            }

            $this->_coreRegistry->register('current_rfq_quote', $quote);
            $mageQuote = $this->mageQuoteFactory->create()->load($quote->getQuoteId());
            $this->_coreRegistry->register('current_quote', $mageQuote);
        }
        if (!$this->rfqData->getConfig('general/enable') || !$quote->getId()) {
            throw new NotFoundException(__('Page not found.'));
        }

        $resultPage->getConfig()->getTitle()->set(__('Quote # %1', $quote->getIncrementId()));

        /** @var \Magento\Framework\View\Element\Html\Links $navigationBlock */
        $navigationBlock = $resultPage->getLayout()->getBlock('customer_account_navigation');
        if ($navigationBlock) {
            $navigationBlock->setActive('quotation/quote/history');
        }

        return $resultPage;
    }
}