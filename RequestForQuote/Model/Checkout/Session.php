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

namespace Lof\RequestForQuote\Model\Checkout;

use Magento\Customer\Api\Data\CustomerInterface;
use Magento\Quote\Model\Quote;
use Magento\Quote\Model\QuoteIdMaskFactory;

class Session extends \Magento\Checkout\Model\Session
{

    /**
     * Quote instance
     *
     * @var Quote
     */
    protected $_rfqQuote;

    /**
     * @param \Magento\Framework\App\Request\Http
     * @param \Magento\Framework\Session\SidResolverInterface
     * @param \Magento\Framework\Session\Config\ConfigInterface
     * @param \Magento\Framework\Session\SaveHandlerInterface
     * @param \Magento\Framework\Session\ValidatorInterface
     * @param \Magento\Framework\Session\StorageInterface
     * @param \Magento\Framework\Stdlib\CookieManagerInterface
     * @param \Magento\Framework\Stdlib\Cookie\CookieMetadataFactory
     * @param \Magento\Framework\App\State
     * @param \Magento\Sales\Model\OrderFactory
     * @param \Magento\Customer\Model\Session
     * @param \Magento\Quote\Api\CartRepositoryInterface
     * @param \Magento\Framework\HTTP\PhpEnvironment\RemoteAddress
     * @param \Magento\Framework\Event\ManagerInterface
     * @param \Magento\Store\Model\StoreManagerInterface
     * @param \Magento\Customer\Api\CustomerRepositoryInterface
     * @param QuoteIdMaskFactory
     * @param \Magento\Quote\Model\QuoteFactory
     * @param \Magento\Quote\Model\ResourceModel\Quote\CollectionFactory
     */
	public function __construct(
		\Magento\Framework\App\Request\Http $request,
		\Magento\Framework\Session\SidResolverInterface $sidResolver,
		\Magento\Framework\Session\Config\ConfigInterface $sessionConfig,
		\Magento\Framework\Session\SaveHandlerInterface $saveHandler,
		\Magento\Framework\Session\ValidatorInterface $validator,
		\Magento\Framework\Session\StorageInterface $storage,
		\Magento\Framework\Stdlib\CookieManagerInterface $cookieManager,
		\Magento\Framework\Stdlib\Cookie\CookieMetadataFactory $cookieMetadataFactory,
		\Magento\Framework\App\State $appState,
		\Magento\Sales\Model\OrderFactory $orderFactory,
		\Magento\Customer\Model\Session $customerSession,
		\Magento\Quote\Api\CartRepositoryInterface $quoteRepository,
		\Magento\Framework\HTTP\PhpEnvironment\RemoteAddress $remoteAddress,
		\Magento\Framework\Event\ManagerInterface $eventManager,
		\Magento\Store\Model\StoreManagerInterface $storeManager,
		\Magento\Customer\Api\CustomerRepositoryInterface $customerRepository,
		QuoteIdMaskFactory $quoteIdMaskFactory,
		\Magento\Quote\Model\QuoteFactory $quoteFactory,
		\Magento\Quote\Model\ResourceModel\Quote\CollectionFactory $quoteCollectionFactory
		) {
		parent::__construct(
			$request,
			$sidResolver,
			$sessionConfig,
			$saveHandler,
			$validator,
			$storage,
			$cookieManager,
			$cookieMetadataFactory,
			$appState,
			$orderFactory,
			$customerSession,
			$quoteRepository,
			$remoteAddress,
			$eventManager,
			$storeManager,
			$customerRepository,
			$quoteIdMaskFactory,
			$quoteFactory
		);
		$this->quoteCollectionFactory = $quoteCollectionFactory;
	}

    /**
     * Get checkout quote instance by current session
     *
     * @return Quote
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.NPathComplexity)
     */
    public function getQuote()
    {
        $this->_eventManager->dispatch('custom_quote_process', ['checkout_session' => $this]);

        if ($this->_quote === null) {
            $quote = $this->quoteFactory->create();
            if ($this->getQuoteId()) {
                try {
                    if ($this->_loadInactive) {
                        $quote = $this->quoteRepository->get($this->getQuoteId());
                    } else {
                        $quote = $this->quoteRepository->getActive($this->getQuoteId());
                    }

                    /**
                     * If current currency code of quote is not equal current currency code of store,
                     * need recalculate totals of quote. It is possible if customer use currency switcher or
                     * store switcher.
                     */
                    if ($quote->getQuoteCurrencyCode() != $this->_storeManager->getStore()->getCurrentCurrencyCode()) {
                        $quote->setStore($this->_storeManager->getStore());
                        $this->quoteRepository->save($quote->collectTotals());
                        /*
                         * We mast to create new quote object, because collectTotals()
                         * can to create links with other objects.
                         */
                        $quote = $this->quoteRepository->get($this->getQuoteId());
                    }
                } catch (\Magento\Framework\Exception\NoSuchEntityException $e) {
                    $this->setQuoteId(null);
                }
            }

            if (!$this->getQuoteId()) {
                if ($this->_customerSession->isLoggedIn() || $this->_customer) {
                    $customerId = $this->_customer
                        ? $this->_customer->getId()
                        : $this->_customerSession->getCustomerId();
                    try {
                        $quote = $this->quoteRepository->getActiveForCustomer($customerId);
                        $this->setQuoteId($quote->getId());
                    } catch (\Magento\Framework\Exception\NoSuchEntityException $e) {
                    }
                } else {
                    $quote->setIsCheckoutCart(true);
                    $this->_eventManager->dispatch('checkout_quote_init', ['quote' => $quote]);
                }
            }

            if ($this->_customer) {
                $quote->setCustomer($this->_customer);
            } elseif ($this->_customerSession->isLoggedIn()) {
                $quote->setCustomer($this->customerRepository->getById($this->_customerSession->getCustomerId()));
            }

            $quote->setStore($this->_storeManager->getStore());
            $this->_quote = $quote;
        }

        if (!$this->isQuoteMasked() && !$this->_customerSession->isLoggedIn() && $this->getQuoteId()) {
            $quoteId = $this->getQuoteId();
            /** @var $quoteIdMask \Magento\Quote\Model\QuoteIdMask */
            $quoteIdMask = $this->quoteIdMaskFactory->create()->load($quoteId, 'quote_id');
            if ($quoteIdMask->getMaskedId() === null) {
                $quoteIdMask->setQuoteId($quoteId)->save();
            }
            $this->setIsQuoteMasked(true);
        }

        $remoteAddress = $this->_remoteAddress->getRemoteAddress();
        if ($remoteAddress) {
            $this->_quote->setRemoteIp($remoteAddress);
            $xForwardIp = $this->request->getServer('HTTP_X_FORWARDED_FOR');
            $this->_quote->setXForwardedFor($xForwardIp);
        }

        return $this->_quote;
    }

    /**
     * Get checkout quote instance by current session
     *
     * @return Quote
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.NPathComplexity)
     */
    public function getRfqQuote()
    {
        if ($this->_rfqQuote === null) {
        	$mageQuote = $this->getQuote();
        	if (!$mageQuote->getId()) {
        		$mageQuote = $this->quoteFactory->create();
        		$mageQuote->setStore($this->_storeManager->getStore());
        		if ($customerId = $this->_customerSession->getCustomerId()) {
        			$mageQuote->setCustomer($this->customerRepository->getById($customerId));
        		}
        		$mageQuote->save();
        		$this->setQuoteId($mageQuote->getId());
        	}

        	$quoteId = $mageQuote->getId();
        	$quote   = $this->quoteCollectionFactory->create()
        	->addFieldToFilter('rfq_parent_quote_id', $quoteId)
        	->getFirstItem();

        	if (!$quote->getId()) {
        		$quote = $this->quoteFactory->create();
        		$quote->setStore($this->_storeManager->getStore());
        		if ($customerId = $this->_customerSession->getCustomerId()) {
        			$quote->setCustomer($this->customerRepository->getById($customerId));
        		}
        		$quote->setData('rfq_parent_quote_id', $quoteId);
        		$quote->save();
        		$quote->setRfqQuoteId($quote->getId());

                $quote = $this->quoteRepository->get($quote->getId());
        	}
            $this->_rfqQuote = $quote;
        }

    	return $this->_rfqQuote;
    }

    /**
     * @return string
     * @codeCoverageIgnore
     */
    protected function _getRfqQuoteIdKey()
    {
        return 'rfq_quote_id_' . $this->_storeManager->getStore()->getWebsiteId();
    }

    /**
     * @param int $quoteId
     * @return void
     * @codeCoverageIgnore
     */
    public function setRfqQuoteId($quoteId)
    {
        $this->storage->setData($this->_getRfqQuoteIdKey(), $quoteId);
    }

    /**
     * @return int
     * @codeCoverageIgnore
     */
    public function getRfqQuoteId()
    {
        return $this->getData($this->_getQuoteIdKey());
    }



    /**
     * @return string
     * @codeCoverageIgnore
     */
    protected function _getRfqLastQuoteIdKey()
    {
        return 'rfq_last_quote_id_' . $this->_storeManager->getStore()->getWebsiteId();
    }

    /**
     * @param int $quoteId
     * @return void
     * @codeCoverageIgnore
     */
    public function setRfqLastQuoteId($quoteId)
    {
        $this->storage->setData($this->_getRfqLastQuoteIdKey(), $quoteId);
    }

    /**
     * @return int
     * @codeCoverageIgnore
     */
    public function getRfqLastQuoteId()
    {
        return $this->getData($this->_getRfqLastQuoteIdKey());
    }


    /**
     * Destroy/end a session
     * Unset all data associated with object
     *
     * @return $this
     */
    public function clearRfqQuote()
    {
        $this->_rfqQuote = null;
        $this->setRfqQuoteId(null);
        $this->setRfqLastQuoteId(null);
        return $this;
    }
}