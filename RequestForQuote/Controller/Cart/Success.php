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

namespace Lof\RequestForQuote\Controller\Cart;

class Success extends \Magento\Framework\App\Action\Action
{
    /**
     * @var \Magento\Framework\View\Result\Page
     */
    protected $resultPageFactory;

    /**
     * @var \Magento\Framework\Registry
     */
    protected $_coreRegistry;

    /**
     * @var \Magento\Checkout\Model\Session
     */
    protected $checkoutSession;

    /**
     * @var \Lof\RequestForQuote\Model\Quote
     */
    protected $_quoteFactory;

    /**
     * @param \Magento\Framework\App\Action\Context      $context           
     * @param \Magento\Framework\View\Result\PageFactory $resultPageFactory 
     * @param \Magento\Framework\Registry                $registry          
     * @param \Magento\Checkout\Model\Session            $checkoutSession   
     * @param \Lof\RequestForQuote\Model\QuoteFactory    $quoteFactory      
     */
    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Magento\Framework\View\Result\PageFactory $resultPageFactory,
        \Magento\Framework\Registry $registry,
        \Magento\Checkout\Model\Session $checkoutSession,
        \Lof\RequestForQuote\Model\QuoteFactory $quoteFactory
        ) {
        $this->resultPageFactory = $resultPageFactory;
        $this->_coreRegistry     = $registry;
        parent::__construct($context);  
        $this->checkoutSession   = $checkoutSession;
        $this->_quoteFactory     = $quoteFactory;
    }

    public function execute()
    {
        $lastQuoteId = $this->checkoutSession->getRfqLastQuoteId();
        $logger = $this->_objectManager->create('\Psr\Log\LoggerInterface');
        if ($lastQuoteId) {

            $quote = $this->_quoteFactory->create()->load($lastQuoteId);
            $this->_objectManager->get('Magento\Framework\Registry')->register('quote', $quote);

            $resultPage = $this->resultPageFactory->create();
            $resultPage->getConfig()->getTitle()->set(__('Success Page'));

            /** CLEAR QUOTE */
            $this->checkoutSession->clearRfqQuote();
            return $resultPage;
        } else {
            $resultRedirect = $this->resultRedirectFactory->create();
            $backUrl = $this->_url->getUrl('quotation/quote');
            return $resultRedirect->setUrl($backUrl);
        }
    }

}