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

namespace Lof\RequestForQuote\Helper;

class Product extends \Magento\Framework\App\Helper\AbstractHelper
{
    /**
     * @var \Magento\Checkout\Helper\Cart
     */
    protected $_cartHelper;

    /**
     * @var \Magento\Framework\Url\Helper\Data
     */
    protected $urlHelper;

    /**
     * @var \Magento\Framework\View\LayoutInterface
     */
    protected $_layout;

    /**
     * @var \Magento\Customer\Model\Session
     */
    protected $_customerSession;

    /**
     * @param \Magento\Framework\App\Helper\Context
     * @param \Magento\Checkout\Helper\Cart
     * @param \Magento\Framework\View\LayoutInterface
     * @param \Magento\Framework\Url\Helper\Data
     */
    public function __construct(
      \Magento\Framework\App\Helper\Context $context,
      \Magento\Customer\Model\Session $customerSession,
      \Magento\Checkout\Helper\Cart $cartHelper,
      \Magento\Framework\View\LayoutInterface $layout,
      \Magento\Framework\Url\Helper\Data $urlHelper,
      \Lof\RequestForQuote\Helper\Data $dataHelper
      ) {
      parent::__construct($context);
      $this->_cartHelper      = $cartHelper;
      $this->urlHelper        = $urlHelper;
      $this->_layout          = $layout;
      $this->_customerSession = $customerSession;
      $this->dataHelper       = $dataHelper;
  }

    /**
     * Get post parameters
     *
     * @param \Magento\Catalog\Model\Product $product
     * @return string
     */
    public function getAddToCartPostParams(\Magento\Catalog\Model\Product $product)
    {
        $url = $this->getAddToCartUrl($product);
        $url = str_replace("checkout/cart/add", "quotation/cart/add", $url);
        return [
        'action' => $url,
        'data' => [
        'product' => $product->getEntityId(),
        \Magento\Framework\App\ActionInterface::PARAM_NAME_URL_ENCODED =>
        $this->urlHelper->getEncodedUrl($url),
        ]
        ];
    }

    /**
     * Retrieve url for add product to cart
     * Will return product view page URL if product has required options
     *
     * @param \Magento\Catalog\Model\Product $product
     * @param array $additional
     * @return string
     */
    public function getAddToCartUrl($product, $additional = [])
    {
        if ($product->getTypeInstance()->hasRequiredOptions($product)) {
            if (!isset($additional['_escape'])) {
                $additional['_escape'] = true;
            }
            if (!isset($additional['_query'])) {
                $additional['_query'] = [];
            }
            $additional['_query']['options'] = 'cart';

            return $this->getProductUrl($product, $additional);
        }
        return $this->_cartHelper->getAddUrl($product, $additional);
    }

    /**
     * Retrieve Product URL using UrlDataObject
     *
     * @param \Magento\Catalog\Model\Product $product
     * @param array $additional the route params
     * @return string
     */
    public function getProductUrl($product, $additional = [])
    {
        if ($this->hasProductUrl($product)) {
            if (!isset($additional['_escape'])) {
                $additional['_escape'] = true;
            }
            $url = $product->getUrlModel()->getUrl($product, $additional);
            return $url;
        }

        return '#';
    }

    public function getProductQuoteForm(\Magento\Catalog\Model\Product $product)
    {
        $customerGroupId = $this->_customerSession->getCustomer()->getGroupId();
        $customerGroups  = explode(',', $this->dataHelper->getConfig('general/customer_groups'));
        $html            = '';

        if (in_array($customerGroupId, $customerGroups)) {
            if (($product->getData('product_quote') && $product->hasData('product_quote')) || !$product->hasData('product_quote')) {
                $html = $this->_layout->createBlock("Magento\Framework\View\Element\Template")->setTemplate('Lof_RequestForQuote::product/quote_form.phtml')->setProduct($product)->toHtml();
            }
        }
        return $html;
    }

    /**
     * Check Product has URL
     *
     * @param \Magento\Catalog\Model\Product $product
     * @return bool
     */
    public function hasProductUrl($product)
    {
        if ($product->getVisibleInSiteVisibilities()) {
            return true;
        }
        if ($product->hasUrlDataObject()) {
            if (in_array($product->hasUrlDataObject()->getVisibility(), $product->getVisibleInSiteVisibilities())) {
                return true;
            }
        }

        return false;
    }
}