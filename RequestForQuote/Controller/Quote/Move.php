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
use Magento\Framework\App\Filesystem\DirectoryList;

class Move extends \Lof\RequestForQuote\Controller\AbstractIndex
{
    /**
     * @var \Magento\Framework\Data\Form\FormKey\Validator
     */
    protected $_formKeyValidator;

    /**
     * @var \Magento\Checkout\Model\Session
     */
    protected $_checkoutSession;

    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Magento\Framework\Data\Form\FormKey\Validator $formKeyValidator,
        \Magento\Catalog\Api\ProductRepositoryInterface $productRepository,
        \Magento\Checkout\Model\Session $checkoutSession,
        \Magento\Checkout\Model\Cart $mageCart,
        \Lof\RequestForQuote\Model\QuoteFactory $quoteFactory,
        \Magento\Quote\Api\CartRepositoryInterface $quoteRepository,
        \Magento\Framework\Url $urlBuilder
        ) {
        parent::__construct($context);
        $this->_formKeyValidator = $formKeyValidator;
        $this->productRepository = $productRepository;
        $this->_checkoutSession  = $checkoutSession;
        $this->mageCart          = $mageCart;
        $this->quoteFactory      = $quoteFactory;
        $this->quoteRepository   = $quoteRepository;
        $this->_urlBuilder       = $urlBuilder;
    }

    /**
     * Initialize product instance from request data
     *
     * @return \Magento\Catalog\Model\Product|false
     */
    protected function _initProduct()
    {
        $productId = (int)$this->getRequest()->getParam('product');
        if ($productId) {
            $storeId = $this->_objectManager->get('Magento\Store\Model\StoreManagerInterface')->getStore()->getId();
            try {
                return $this->productRepository->getById($productId, false, $storeId);
            } catch (NoSuchEntityException $e) {
                return false;
            }
        }
        return false;
    }

    public function execute()
    {
        if (!$this->_formKeyValidator->validate($this->getRequest())) {
            return $this->resultRedirectFactory->create()->setPath('*/*/');
        }

        $mageQuote = $this->mageCart->getQuote();
        $items     = $mageQuote->getAllItems();
        foreach ($items as $item) {
            $mageQuote->removeItem($item->getId());
        }
        $backUrl = '';
        $data = $this->getRequest()->getParams();
        if ($data['quote']) {
            $rfqQuote = $this->quoteFactory->create()->load($data['quote']);
            if ($rfqQuote->getId()) {
                $tmpQuote = $this->quoteRepository->get($rfqQuote->getQuoteId());
                if ($tmpQuote->getId()) {
                    $mageQuote->merge($tmpQuote);
                    $this->mageCart->save();
                    $backUrl = $this->_urlBuilder->getUrl('checkout/cart');
                }
            }
        }
        return $this->_goBack($backUrl);
    }

    /**
     * Set back redirect url to response
     *
     * @param null|string $backUrl
     *
     * @return \Magento\Framework\Controller\Result\Redirect
     */
    protected function _goBack($backUrl = null)
    {
        $resultRedirect = $this->resultRedirectFactory->create();

        if ($backUrl || $backUrl = $this->getBackUrl($this->_redirect->getRefererUrl())) {
            $resultRedirect->setUrl($backUrl);
        }
        
        return $resultRedirect;
    }
}