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

namespace Lof\RequestForQuote\Block\Adminhtml\Quote\Edit\Items\Renderer;

use Magento\Sales\Model\Order\Item;

class DefaultRenderer extends \Magento\Sales\Block\Adminhtml\Order\View\Items\Renderer\DefaultRenderer
{
     /**
     * @var \Magento\Catalog\Helper\Product\Configuration
     */
    protected $configurationHelper;
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\CatalogInventory\Api\StockRegistryInterface $stockRegistry,
        \Magento\CatalogInventory\Api\StockConfigurationInterface $stockConfiguration,
        \Magento\Framework\Registry $registry,
        \Magento\GiftMessage\Helper\Message $messageHelper,
        \Magento\Checkout\Helper\Data $checkoutHelper,
        \Magento\Catalog\Model\ProductFactory $productFactory,
        \Magento\Catalog\Block\Product\ImageBuilder $imageBuilder,
	    \Magento\Store\Model\App\Emulation $appEmulation,
	    \Magento\Catalog\Helper\ImageFactory $helperFactory,
        \Magento\Catalog\Helper\Product\Configuration $configurationHelper,
        array $data = []
    ) {
        parent::__construct($context, $stockRegistry, $stockConfiguration, $registry, $messageHelper, $checkoutHelper);
		$this->productFactory = $productFactory;
		$this->imageBuilder   = $imageBuilder;
		$this->helperFactory  = $helperFactory;
		$this->appEmulation   = $appEmulation;
        $this->configurationHelper = $configurationHelper;
		
    }

    /**
     * Retrieve invoice model instance
     *
     * @return \Magento\Sales\Model\Order\Invoice
     */
    public function getOrder()
    {
        return $this->_coreRegistry->registry('mage_quote');
    }

	/**
	 * Retrieve product image
	 *
	 * @param \Magento\Catalog\Model\Product $product
	 * @param string $imageId
	 * @param array $attributes
	 * @return \Magento\Catalog\Block\Product\Image
	 */
	public function getImage($product, $imageId, $attributes = [])
	{
	    $image = $this->helperFactory->create()->init($product, $imageId)
	        ->constrainOnly(true)
	        ->keepAspectRatio(true)
	        ->keepTransparency(true)
	        ->keepFrame(false)
	        ->resize(200, 300);

	    return $image;
	}

	public function getProductImage($store, $productId)
	{
		$this->appEmulation->startEnvironmentEmulation($store->getId(), \Magento\Framework\App\Area::AREA_FRONTEND, true);
		$product  = $this->productFactory->create()->load($productId);
		$imageUrl = $this->getImage($product, 'product_base_image')->getUrl();
		$this->appEmulation->stopEnvironmentEmulation();

		return $imageUrl;
	}

	public function getCurrencySymbol($currency_code = null) {
        $currentcySymbol = "";
        if(!$currency_code) {
            $currency_code = $this->getOrder()->getGlobalCurrencyCode();
        }
        if($currency_code) {
            $_objectManager = \Magento\Framework\App\ObjectManager::getInstance();
            $currency = $_objectManager->create('Magento\Directory\Model\CurrencyFactory')->create()->load($currency_code);
            $currencySymbol = $currency->getCurrencySymbol();
        } else {
            $_objectManager = \Magento\Framework\App\ObjectManager::getInstance();
            $rfqHelper                = $_objectManager->create('Lof\RequestForQuote\Helper\Data')->create();
            $currentcySymbol          = $rfqHelper->getCurrentCurrencySymbol();  
        }
        

        return $currencySymbol;
    }

    public function getSelectedOptionsOfQuoteItem($item)
    {
        return $this->configurationHelper->getCustomOptions($item);
    }
}
