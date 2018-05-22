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

class NewQuote extends \Magento\Framework\App\Action\Action
{
    /**
     * @var \Magento\Quote\Api\CartRepositoryInterface
     */
    protected $quoteRepository;

    /**
     * @var \Magento\Framework\UrlInterface
     */
    protected $_urlBuilder;

    /**
     * @param \Magento\Framework\App\Action\Context
     * @param \Magento\Checkout\Model\Cart
     * @param \Magento\Quote\Api\CartRepositoryInterface
     * @param \Magento\Framework\Url
     * @param \Lof\RequestForQuote\Model\ResourceModel\Quote\CollectionFactory
     */
    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Magento\Checkout\Model\Cart $cart,
        \Magento\Quote\Api\CartRepositoryInterface $quoteRepository,
        \Magento\Framework\Url $urlBuilder,
        \Lof\RequestForQuote\Model\ResourceModel\Quote\CollectionFactory $quoteCollectionFactory,
        \Lof\RequestForQuote\Helper\Data $quoteHelper
        ) {
        parent::__construct($context);
        $this->quoteCollectionFactory = $quoteCollectionFactory;
        $this->cart                   = $cart;
        $this->quoteRepository        = $quoteRepository;
        $this->_urlBuilder            = $urlBuilder;
        $this->quoteHelper            = $quoteHelper;
    }

    /**
     * Delete shopping cart item action
     *
     * @return \Magento\Framework\Controller\Result\Redirect
     */
    public function execute()
    {
        $id = (int) $this->getRequest()->getParam('quote_id');
        $token =  $this->getRequest()->getParam('token');
        if ($id && $token) {
            $quote = $this->quoteCollectionFactory->create()
            ->addFieldToFilter('token', $token)
            ->addFieldToFilter('quote_id', $id)
            ->getFirstItem();

            if ($quote->getId() && ($quote->getQuoteId() == $id) && $quote->getToken()) {
                if (!$this->quoteHelper->isExpired($quote)) {
                    try {
                        $quote->setData('token', '')->save();
                        $mageQuote = $this->cart->getQuote();
                        $rfqQuote  = $this->quoteRepository->get($quote->getQuoteId());
                        $mageQuote->merge($rfqQuote);
                        $this->cart->save();
                    } catch (\Exception $e) {
                        $this->messageManager->addError(__('We can\'t remove the item.'));
                        $this->_objectManager->get('Psr\Log\LoggerInterface')->critical($e);
                    }
                }
            }
        }
        $backUrl = $this->_urlBuilder->getUrl('checkout/cart');
        return $this->resultRedirectFactory->create()->setUrl($backUrl);
    }
}
