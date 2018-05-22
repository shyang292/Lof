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

namespace Lof\RequestForQuote\Model;

use Magento\Catalog\Api\ProductRepositoryInterface;

class Cart extends \Magento\Checkout\Model\Cart
{
    /**
     * @param \Magento\Framework\Event\ManagerInterface
     * @param \Magento\Framework\App\Config\ScopeConfigInterface
     * @param \Magento\Store\Model\StoreManagerInterface
     * @param \Magento\Checkout\Model\ResourceModel\Cart
     * @param \Magento\Checkout\Model\Session
     * @param \Magento\Customer\Model\Session
     * @param \Magento\Framework\Message\ManagerInterface
     * @param \Magento\CatalogInventory\Api\StockRegistryInterface
     * @param \Magento\CatalogInventory\Api\StockStateInterface
     * @param \Magento\Quote\Api\CartRepositoryInterface
     * @param ProductRepositoryInterface
     * @param array
     */
    public function __construct(
        \Magento\Framework\Event\ManagerInterface $eventManager,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Checkout\Model\ResourceModel\Cart $resourceCart,
        \Magento\Checkout\Model\Session $checkoutSession,
        \Magento\Customer\Model\Session $customerSession,
        \Magento\Framework\Message\ManagerInterface $messageManager,
        \Magento\CatalogInventory\Api\StockRegistryInterface $stockRegistry,
        \Magento\CatalogInventory\Api\StockStateInterface $stockState,
        \Magento\Quote\Api\CartRepositoryInterface $quoteRepository,
        ProductRepositoryInterface $productRepository,
        array $data = []
    ) {
        parent::__construct(
            $eventManager,
            $scopeConfig,
            $storeManager,
            $resourceCart,
            $checkoutSession,
            $customerSession,
            $messageManager,
            $stockRegistry,
            $stockState,
            $quoteRepository,
            $productRepository
        );
    }

    /**
     * Get quote object associated with cart. By default it is current customer session quote
     *
     * @return \Magento\Quote\Model\Quote
     */
    public function getQuote()
    {
        if (!$this->hasData('rfq_quote')) {
            $this->setData('rfq_quote', $this->_checkoutSession->getRfqQuote());
        }
        return $this->_getData('rfq_quote');
    }

    public function save()
    {
        //$this->_eventManager->dispatch('checkout_cart_save_before', ['cart' => $this]);
        $this->getQuote()->getBillingAddress();
        $this->getQuote()->getShippingAddress()->setCollectShippingRates(true);
        $this->getQuote()->collectTotals();

        $quote = $this->getQuote();
        $quote->save();

        //$this->quoteRepository->save($this->getQuote());
        $this->_checkoutSession->setRfqQuoteId($this->getQuote()->getId());

        /**
         * Cart save usually called after changes with cart items.
         */
        //$this->_eventManager->dispatch('checkout_cart_save_after', ['cart' => $this]);
        $this->reinitializeState();
        return $this;
    }

    /**
     * Get shopping cart items summary (includes config settings)
     *
     * @return int|float
     */
    public function getSummaryQty()
    {
        $quoteId = $this->_checkoutSession->getRfqQuoteId();

        //If there is no quote id in session trying to load quote
        //and get new quote id. This is done for cases when quote was created
        //not by customer (from backend for example).
        if (!$quoteId && $this->_customerSession->isLoggedIn()) {
            $this->_checkoutSession->getRfqQuote();
            $quoteId = $this->_checkoutSession->getRfqQuoteId();
        }

        if ($quoteId && $this->_summaryQty === null) {
            $useQty = $this->_scopeConfig->getValue(
                'checkout/cart_link/use_qty',
                \Magento\Store\Model\ScopeInterface::SCOPE_STORE
            );
            $this->_summaryQty = $useQty ? $this->getItemsQty() : $this->getItemsCount();
        }
        return $this->_summaryQty;
    }

    /**
     * Add product to shopping cart (quote)
     *
     * @param int|Product $productInfo
     * @param \Magento\Framework\DataObject|int|array $requestInfo
     * @return $this
     * @throws \Magento\Framework\Exception\LocalizedException
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     */
    public function addProduct($productInfo, $requestInfo = null)
    {
        $product = $this->_getProduct($productInfo);
        $request = $this->_getProductRequest($requestInfo);
        $productId = $product->getId();

        if ($productId) {
            $stockItem = $this->stockRegistry->getStockItem($productId, $product->getStore()->getWebsiteId());
            $minimumQty = $stockItem->getMinSaleQty();
            //If product was not found in cart and there is set minimal qty for it
            if ($minimumQty
                && $minimumQty > 0
                && !$request->getQty()
                && !$this->getQuote()->hasProductId($productId)
            ) {
                $request->setQty($minimumQty);
            }
        }

        if ($productId) {
            try {
                $result = $this->getQuote()->addProduct($product, $request);
            } catch (\Magento\Framework\Exception\LocalizedException $e) {
                $this->_checkoutSession->setUseNotice(false);
                $result = $e->getMessage();
            }
            /**
             * String we can get if prepare process has error
             */
            if (is_string($result)) {
                if ($product->hasOptionsValidationFail()) {
                    $redirectUrl = $product->getUrlModel()->getUrl(
                        $product,
                        ['_query' => ['startcustomization' => 1]]
                    );
                } else {
                    $redirectUrl = $product->getProductUrl();
                }
                $this->_checkoutSession->setRedirectUrl($redirectUrl);
                if ($this->_checkoutSession->getUseNotice() === null) {
                    $this->_checkoutSession->setUseNotice(true);
                }
                throw new \Magento\Framework\Exception\LocalizedException(__($result));
            }
        } else {
            throw new \Magento\Framework\Exception\LocalizedException(__('The product does not exist.'));
        }
        return $this;
    }

    /**
     * Update cart items information
     *
     * @param  array $data
     * @return $this
     * @throws \Magento\Framework\Exception\LocalizedException
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.NPathComplexity)
     */
    public function updateItems($data)
    {
        $infoDataObject = new \Magento\Framework\DataObject($data);

        $qtyRecalculatedFlag = false;
        foreach ($data as $itemId => $itemInfo) {
            if (isset($itemInfo['customprice'])) {
                $itemInfo['customprice'] = (float) $itemInfo['customprice'];
            }
            if (isset($itemInfo['description'])) {
                $itemInfo['description'] = strip_tags(trim($itemInfo['description']));
            }
            $item = $this->getQuote()->getItemById($itemId);
            if (!$item) {
                continue;
            }

            if (!empty($itemInfo['remove']) || isset($itemInfo['qty']) && $itemInfo['qty'] == '0') {
                $this->removeItem($itemId);
                continue;
            }

            $qty = isset($itemInfo['qty']) ? (double)$itemInfo['qty'] : false;
            if ($qty > 0) {

                $item->setQty($qty);

                $update_price = false;

                if (isset($itemInfo['customprice']) && $itemInfo['customprice'] && $itemInfo['customprice']!=($item->getPrice())) {
                    if (!$item->getCustomPrice()) {
                        $price  = [
                            'price' => $item->getPrice(),
                            'base_price' => $item->getBasePrice(),
                            'price_incl_tax' => $item->getPriceInclTax(),
                            'base_price_incl_tax' => $item->getBasePriceInclTax(),
                        ];

                        $option = [
                            'product_id' => $item->getProductId(),
                            'code' => 'product_price',
                            'value' => serialize($price)
                        ];
                        $item->addOption($option);
                    }

                    if ($productPrice = $item->getOptionByCode('product_price')) {
                        $productPrice = unserialize($productPrice->getValue()); 
                    }
                    $old_price = $item->getPrice();
                    $customPrice = $itemInfo['customprice'];// / $qty;
                    $item->setCustomPrice($customPrice);
                    $item->setOriginalCustomPrice($customPrice);
                    if (!$item->getOriginalPrice()) {
                        $item->setOriginalPrice($old_price);
                    }
                    $update_price = true;
                } else {
                    $customPrice = $item->getPrice();// / $qty;
                    $item->setCustomPrice($customPrice);
                    $item->setOriginalCustomPrice($customPrice);
                    if (!$item->getOriginalPrice()) {
                        $item->setOriginalPrice($customPrice);
                    }
                }

                if (isset($itemInfo['description']) && $itemInfo['description']) {
                    $item->setDescription($itemInfo['description']);
                }

                if ($update_price && $item->getHasConfigurationUnavailableError()) {
                    $item->unsHasConfigurationUnavailableError();
                }

                if ($item->getHasError()) {
                    throw new \Magento\Framework\Exception\LocalizedException(__($item->getMessage()));
                }

                if (isset($itemInfo['before_suggest_qty']) && $itemInfo['before_suggest_qty'] != $qty) {
                    $qtyRecalculatedFlag = true;
                    $this->messageManager->addNotice(
                        __('Quantity was recalculated from %1 to %2', $itemInfo['before_suggest_qty'], $qty),
                        'quote_item' . $item->getId()
                    );
                }
            }
        }

        if ($qtyRecalculatedFlag) {
            $this->messageManager->addNotice(
                __('We adjusted product quantities to fit the required increments.')
            );
        }

        return $this;
    }
}
