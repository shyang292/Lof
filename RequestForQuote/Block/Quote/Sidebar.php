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

namespace Lof\RequestForQuote\Block\Quote;

use Magento\Store\Model\ScopeInterface;

class Sidebar extends \Magento\Checkout\Block\Cart\AbstractCart
{
    /**
     * Xml pah to checkout sidebar display value
     */
    const XML_PATH_CHECKOUT_SIDEBAR_DISPLAY = 'requestforquote/sidebar/display';

    /**
     * @var \Magento\Catalog\Helper\Image
     */
    protected $imageHelper;

    /**
     * @param \Magento\Framework\View\Element\Template\Context                 $context              
     * @param \Magento\Customer\Model\Session                                  $customerSession      
     * @param \Magento\Checkout\Model\Session                                  $checkoutSession      
     * @param \Magento\Catalog\Helper\Image                                    $imageHelper          
     * @param \Magento\Customer\CustomerData\JsLayoutDataProviderPoolInterface $jsLayoutDataProvider 
     * @param array                                                            $data                 
     */
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\Customer\Model\Session $customerSession,
        \Magento\Checkout\Model\Session $checkoutSession,
        \Magento\Catalog\Helper\Image $imageHelper,
        \Magento\Customer\CustomerData\JsLayoutDataProviderPoolInterface $jsLayoutDataProvider,
        \Lof\RequestForQuote\Helper\Data $dataHelper,
        array $data = []
    ) {
        if (isset($data['jsLayout'])) {
            $this->jsLayout = array_merge_recursive($jsLayoutDataProvider->getData(), $data['jsLayout']);
            unset($data['jsLayout']);
        } else {
            $this->jsLayout = $jsLayoutDataProvider->getData();
        }
        parent::__construct($context, $customerSession, $checkoutSession, $data);
        $this->_isScopePrivate = false;
        $this->imageHelper     = $imageHelper;
        $this->dataHelper      = $dataHelper;
    }

    /**
     * Returns minicart config
     *
     * @return array
     */
    public function getConfig()
    {
        return [
            'submitQuoteUrl'           => $this->getCheckoutUrl(),
            'checkoutUrl'              => $this->getSubmitQuoteUrl(),
            'updateItemQtyUrl'         => $this->getUpdateItemQtyUrl(),
            'removeItemUrl'            => $this->getRemoveItemUrl(),
            'imageTemplate'            => $this->getImageHtmlTemplate(),
            'baseUrl'                  => $this->getBaseUrl(),
            'miniquoteMaxItemsVisible' => $this->getMiniCartMaxItemsCount(),
            'websiteId'                => $this->_storeManager->getStore()->getWebsiteId()
        ];
    }

    /**
     * @return string
     */
    public function getImageHtmlTemplate()
    {
        return $this->imageHelper->getFrame()
            ? 'Magento_Catalog/product/image'
            : 'Magento_Catalog/product/image_with_borders';
    }

    /**
     * Get one page checkout page url
     *
     * @codeCoverageIgnore
     * @return string
     */
    public function getCheckoutUrl()
    {
        return $this->getUrl('quotation/quote');
    }

    /**
     * Get shopping cart page url
     *
     * @return string
     * @codeCoverageIgnore
     */
    public function getSubmitQuoteUrl()
    {
        return $this->getUrl('quotation/quote');
    }

    /**
     * Get update cart item url
     *
     * @return string
     * @codeCoverageIgnore
     */
    public function getUpdateItemQtyUrl()
    {
        return $this->getUrl('checkout/sidebar/updateItemQty', ['_secure' => $this->getRequest()->isSecure()]);
    }

    /**
     * Get remove cart item url
     *
     * @return string
     * @codeCoverageIgnore
     */
    public function getRemoveItemUrl()
    {
        return $this->getUrl('checkout/sidebar/removeItem', ['_secure' => $this->getRequest()->isSecure()]);
    }

    /**
     * Define if Mini Shopping Cart Pop-Up Menu enabled
     *
     * @return bool
     * @codeCoverageIgnore
     * @SuppressWarnings(PHPMD.BooleanGetMethodName)
     */
    public function getIsNeedToDisplaySideBar()
    {
        return (bool)$this->_scopeConfig->getValue(
            self::XML_PATH_CHECKOUT_SIDEBAR_DISPLAY,
            ScopeInterface::SCOPE_STORE
        );
    }

    /**
     * Return totals from custom quote if needed
     *
     * @return array
     */
    public function getTotalsCache()
    {
        if (empty($this->_totals)) {
            $quote = $this->getCustomQuote() ? $this->getCustomQuote() : $this->getQuote();
            $this->_totals = $quote->getTotals();
        }
        return $this->_totals;
    }

    /**
     * Retrieve subtotal block html
     *
     * @codeCoverageIgnore
     * @return string
     */
    public function getTotalsHtml()
    {
        return $this->getLayout()->getBlock('checkout.cart.minicart.totals')->toHtml();
    }

    /**
     * Return base url.
     *
     * @codeCoverageIgnore
     * @return string
     */
    public function getBaseUrl()
    {
        return $this->_storeManager->getStore()->getBaseUrl();
    }

    /**
     * Return max visible item count for minicart
     *
     * @return int
     */
    private function getMiniCartMaxItemsCount()
    {
        return (int)$this->_scopeConfig->getValue('checkout/sidebar/count', ScopeInterface::SCOPE_STORE);
    }

    public function getIcon()
    {
        $image = '';
        $pointImage = $this->dataHelper->getConfig('general/icon');
        if ($pointImage) {
            $imageSrc = $this->_storeManager->getStore()->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_MEDIA) . 'lof/requestforquote/' . $pointImage;
            $image = __('<img src="%1" alt="rfq-icon"/>', $imageSrc);
        }
        return $image;
    }
}
