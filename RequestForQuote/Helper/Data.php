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

class Data extends \Magento\Framework\App\Helper\AbstractHelper
{

    protected $_currency;

    /**
     * @var \Magento\Framework\Stdlib\DateTime\TimezoneInterface
     */
    protected $_localeDate;
    /**
     * @var Rate
     */
    protected $rate;
    /**
     * @var \Magento\Framework\ObjectManagerInterface
     */
    protected $_objectManager;

    protected $_priceCurrency;

    protected $_enable_quote = null;

    protected $customerSession = null;

    public function __construct(
        \Magento\Framework\App\Helper\Context $context,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Checkout\Model\Cart $cart,
        \Magento\Directory\Model\Currency $currency,
        \Magento\Framework\Stdlib\DateTime\TimezoneInterface $localeDate,
        \Lof\RequestForQuote\Model\ResourceModel\Quote\CollectionFactory $quoteCollectionFactory,
        \Magento\Tax\Model\Calculation\Rate $rate,
        \Magento\Customer\Model\Session $customerSession,
        \Magento\Customer\Api\CustomerRepositoryInterface $customerRepository,
        \Magento\Framework\Stdlib\DateTime\DateTime $date,
        \Magento\Framework\ObjectManagerInterface $objectManager,
        \Magento\Framework\Pricing\PriceCurrencyInterface $priceCurrency,
        \Magento\Directory\Model\CountryFactory $countryFactory
        ) {
        parent::__construct($context);
        $this->customerSession        = $customerSession;
        $this->rate                   = $rate;
        $this->_currency              = $currency;
        $this->_storeManager          = $storeManager;
        $this->cart                   = $cart;
        $this->_localeDate            = $localeDate;
        $this->quoteCollectionFactory = $quoteCollectionFactory;
        $this->date                   = $date;
        $this->customerRepository   = $customerRepository;
        $this->_objectManager  = $objectManager;
        $this->_priceCurrency = $priceCurrency;
        $this->_countryFactory = $countryFactory;
    }
    public function getCountryname($countryCode = null){
        if($countryCode) {
            $country = $this->_countryFactory->create()->loadByCode($countryCode);
            return $country->getName();
        }
        return '';
    }
    public function getShippingAddress() {
        $customer = $this->customerRepository->getById($this->getCustomer()->getId());
        return $customer;
    }
    public function getCustomer() {
        if(null === $this->customerSession){
            $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
            $this->customerSession = $objectManager->get('Magento\Customer\Model\Session');
        }
        if (!$this->customerSession->isLoggedIn()) {
            return false;
        }
        return $this->customerSession->getCustomer();
    }

    public function getCustomerData() {
        if(null === $this->customerSession){
            $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
            $this->customerSession = $objectManager->get('Magento\Customer\Model\Session');
        }
        if (!$this->customerSession->isLoggedIn()) {
            return false;
        }
        return $this->customerSession->getCustomerDataObject();
    }
    public function getRate() {
        return $this->rate->getCollection();
    }
    public function getCurrentCurrencySymbol()
    {
        return $this->_priceCurrency->getCurrency()->getCurrencySymbol();
        //return $this->_currency->getCurrencySymbol();
    }

    public function formatPriceWithCurency($price) {
        $priceHelper = $this->_objectManager->create('Magento\Framework\Pricing\Helper\Data'); // Instance of 
        return $priceHelper->currency($price, true, false);
    }

    /**
     * Return brand config value by key and store
     *
     * @param string $key
     * @param \Magento\Store\Model\Store|int|string $store
     * @return string|null
     */
    public function getConfig($key, $store = null)
    {
        if(!$store) {
            $store = $this->_objectManager->get('Magento\Store\Model\StoreManagerInterface')->getStore()->getId();
        }
        $store = $this->_storeManager->getStore($store);
        $websiteId = $store->getWebsiteId();

        $result = $this->scopeConfig->getValue(
            'requestforquote/' . $key,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
            $store);
        return $result;
    }

     /**
     * Return brand config value by key and store
     *
     * @param string $key
     * @param \Magento\Store\Model\Store|int|string $store
     * @return string|null
     */
    public function getSystemConfig($key, $store = null)
    {
        if(!$store) {
            $store = $this->_objectManager->get('Magento\Store\Model\StoreManagerInterface')->getStore()->getId();
        }
        $store = $this->_storeManager->getStore($store);
        $websiteId = $store->getWebsiteId();

        $result = $this->scopeConfig->getValue(
            'general/' . $key,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
            $store);
        return $result;
    }

    public function getQuote() {
        return $this->cart->getQuote();
    }

    /**
     * Get formatted price value including order currency rate to order website currency
     *
     * @param   float $price
     * @param   bool  $addBrackets
     * @return  string
     */
    public function formatPrice($price, $addBrackets = false)
    {
        return $this->formatPricePrecision($price, 2, $addBrackets);
    }

    /**
     * @param float $price
     * @param int $precision
     * @param bool $addBrackets
     * @return string
     */
    public function formatPricePrecision($price, $precision, $addBrackets = false)
    {
        return $this->_currency->formatPrecision($price, $precision, [], true, $addBrackets);
    }

    public function generateRandomString($length = 10) {
        $characters       = '0123456789abcdefghijklmnopqrstuvwxyz';
        $charactersLength = strlen($characters);
        $randomString     = '';
        for ($i = 0; $i < $length; $i++) {
            $randomString .= $characters[rand(0, $charactersLength - 1)];
        }
        return $randomString;
    }

    /**
     * Retrieve formatting date
     *
     * @param null|string|\DateTime $date
     * @param int $format
     * @param bool $showTime
     * @param null|string $timezone
     * @return string
     */
    public function formatDate(
        $date = null,
        $format = \IntlDateFormatter::SHORT,
        $showTime = false,
        $timezone = null
    ) {
        $date = $date instanceof \DateTimeInterface ? $date : new \DateTime($date);
        return $this->_localeDate->formatDateTime(
            $date,
            $format,
            $showTime ? $format : \IntlDateFormatter::NONE,
            null,
            $timezone
        );
    }

    public function isExpired(\Lof\RequestForQuote\Model\Quote $quote)
    {
        if (($quote->getStatus() != \Lof\RequestForQuote\Model\Quote::STATE_EXPIRED && $quote->getStatus() != \Lof\RequestForQuote\Model\Quote::STATE_CANCELED) && $expiryDate = $quote->getExpiry()) {
            $currentDate = $this->date->gmtDate();
            if (strtotime($expiryDate) <= strtotime($currentDate)) {
                return true;
            }
        }
        return false;
    }

    public function isEnabledQuote(){
        if(null === $this->_enable_quote){
            $enable_quote = $this->getConfig('general/enable');
            $customer_groups = $this->getConfig('general/customer_groups');
            $customer_groups = is_array($customer_groups)?$customer_groups:explode(",", $customer_groups);
            $this->_enable_quote = false;
            if($enable_quote) {
                if($customer_groups){
                    $logged_customer_group_id = (int)$this->customerSession->getCustomerGroupId();
                    if(in_array($logged_customer_group_id, $customer_groups)){
                        $this->_enable_quote = true;
                    }

                } else {
                    $this->_enable_quote = true;
                }
            }
        }
        return $this->_enable_quote;
    }

}