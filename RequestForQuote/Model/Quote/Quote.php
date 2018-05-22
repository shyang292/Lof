<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Lof\RequestForQuote\Model\Quote;

use Magento\Customer\Api\Data\CustomerInterface;
use Magento\Customer\Api\Data\GroupInterface;
use Magento\Framework\Model\AbstractExtensibleModel;
use Magento\Quote\Api\Data\PaymentInterface;
use Magento\Quote\Model\Quote\Address;
use Magento\Sales\Model\ResourceModel;
use Magento\Sales\Model\Status;
use Magento\Framework\Api\AttributeValueFactory;
use Magento\Framework\Api\ExtensionAttribute\JoinProcessorInterface;

/**
 * Quote model
 *
 * Supported events:
 *  sales_quote_load_after
 *  sales_quote_save_before
 *  sales_quote_save_after
 *  sales_quote_delete_before
 *  sales_quote_delete_after
 *
 * @method int getIsMultiShipping()
 * @method Quote setIsMultiShipping(int $value)
 * @method float getStoreToBaseRate()
 * @method Quote setStoreToBaseRate(float $value)
 * @method float getStoreToQuoteRate()
 * @method Quote setStoreToQuoteRate(float $value)
 * @method string getBaseCurrencyCode()
 * @method Quote setBaseCurrencyCode(string $value)
 * @method string getStoreCurrencyCode()
 * @method Quote setStoreCurrencyCode(string $value)
 * @method string getQuoteCurrencyCode()
 * @method Quote setQuoteCurrencyCode(string $value)
 * @method float getGrandTotal()
 * @method Quote setGrandTotal(float $value)
 * @method float getBaseGrandTotal()
 * @method Quote setBaseGrandTotal(float $value)
 * @method int getCustomerId()
 * @method Quote setCustomerId(int $value)
 * @method Quote setCustomerGroupId(int $value)
 * @method string getCustomerEmail()
 * @method Quote setCustomerEmail(string $value)
 * @method string getCustomerPrefix()
 * @method Quote setCustomerPrefix(string $value)
 * @method string getCustomerFirstname()
 * @method Quote setCustomerFirstname(string $value)
 * @method string getCustomerMiddlename()
 * @method Quote setCustomerMiddlename(string $value)
 * @method string getCustomerLastname()
 * @method Quote setCustomerLastname(string $value)
 * @method string getCustomerSuffix()
 * @method Quote setCustomerSuffix(string $value)
 * @method string getCustomerDob()
 * @method Quote setCustomerDob(string $value)
 * @method string getRemoteIp()
 * @method Quote setRemoteIp(string $value)
 * @method string getAppliedRuleIds()
 * @method Quote setAppliedRuleIds(string $value)
 * @method string getPasswordHash()
 * @method Quote setPasswordHash(string $value)
 * @method string getCouponCode()
 * @method Quote setCouponCode(string $value)
 * @method string getGlobalCurrencyCode()
 * @method Quote setGlobalCurrencyCode(string $value)
 * @method float getBaseToGlobalRate()
 * @method Quote setBaseToGlobalRate(float $value)
 * @method float getBaseToQuoteRate()
 * @method Quote setBaseToQuoteRate(float $value)
 * @method string getCustomerTaxvat()
 * @method Quote setCustomerTaxvat(string $value)
 * @method string getCustomerGender()
 * @method Quote setCustomerGender(string $value)
 * @method float getSubtotal()
 * @method Quote setSubtotal(float $value)
 * @method float getBaseSubtotal()
 * @method Quote setBaseSubtotal(float $value)
 * @method float getSubtotalWithDiscount()
 * @method Quote setSubtotalWithDiscount(float $value)
 * @method float getBaseSubtotalWithDiscount()
 * @method Quote setBaseSubtotalWithDiscount(float $value)
 * @method int getIsChanged()
 * @method Quote setIsChanged(int $value)
 * @method int getTriggerRecollect()
 * @method Quote setTriggerRecollect(int $value)
 * @method string getExtShippingInfo()
 * @method Quote setExtShippingInfo(string $value)
 * @method int getGiftMessageId()
 * @method Quote setGiftMessageId(int $value)
 * @method bool|null getIsPersistent()
 * @method Quote setIsPersistent(bool $value)
 * @method Quote setSharedStoreIds(array $values)
 * @method Quote setWebsite($value)
 * @SuppressWarnings(PHPMD.ExcessivePublicCount)
 * @SuppressWarnings(PHPMD.TooManyFields)
 * @SuppressWarnings(PHPMD.ExcessiveClassComplexity)
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class Quote extends \Magento\Quote\Model\Quote
{

    /**
     * Remove quote item by item identifier
     *
     * @param   int $itemId
     * @return $this
     */
    public function removeItem($itemId)
    {
        $item = $this->getItemById($itemId);

        if ($item) {
            $item->setQuote($this);
            /**
             * If we remove item from quote - we can't use multishipping mode
             */
            $this->setIsMultiShipping(false);
            $item->isDeleted(true);
            if ($item->getHasChildren()) {
                foreach ($item->getChildren() as $child) {
                    $child->isDeleted(true);
                }
            }

            $parent = $item->getParentItem();
            if ($parent) {
                $parent->isDeleted(true);
            }


//            //added by allen
            $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
            $resource = $objectManager->get('Magento\Framework\App\ResourceConnection');
            $connection = $resource->getConnection();
            $tableName = $resource->getTableName('quote_item');
            $sql = "delete from " . $tableName . " where item_id = ".$itemId;
//            $sql = "delete from" . $tableName;
            $connection->query($sql);
            //--------------------------
            $this->_eventManager->dispatch('sales_quote_remove_item', ['quote_item' => $item]);
        }

        return $this;
    }

}
