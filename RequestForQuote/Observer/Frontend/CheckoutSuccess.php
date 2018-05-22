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

namespace Lof\RequestForQuote\Observer\Frontend;

use Magento\Framework\Event\ObserverInterface;

class CheckoutSuccess implements ObserverInterface
{
	/**
	 * @var \Magento\Quote\Model\QuoteFactory
	 */
	protected $quoteFactory;

	/**
	 * @var \Magento\Quote\Model\ResourceModel\Quote\CollectionFactory
	 */
	protected $quoteCollectionFactory;

	/**
	 * @var \Magento\Customer\Api\CustomerRepositoryInterface
	 */
	protected $customerRepository;

	/**
	 * @var \Magento\Store\Model\StoreManagerInterface
	 */
	protected $_storeManager;

	/**
	 * @var \Magento\Checkout\Model\Session
	 */
	protected $checkoutSession;

	/**
	 * @var \Magento\Customer\Model\Session
	 */
	protected $_customerSession;

	/**
	 * @var \Magento\Quote\Api\CartRepositoryInterface
	 */
	protected $quoteRepository;
	/**
	 * @var \Magento\Sales\Model\OrderFactory
	 */
	protected $orderFactory;

	/**
	 * @param \Lof\RequestForQuote\Model\QuoteFactory                    $quoteFactory           
	 * @param \Magento\Quote\Model\ResourceModel\Quote\CollectionFactory $quoteCollectionFactory 
	 * @param \Magento\Store\Model\StoreManagerInterface                 $storeManager           
	 * @param CustomerRepository                                         $customerRepository     
	 * @param \Magento\Checkout\Model\Session                            $checkoutSession        
	 */
	public function __construct(
		\Magento\Quote\Model\QuoteFactory $quoteFactory,
		\Magento\Quote\Model\ResourceModel\Quote\CollectionFactory $quoteCollectionFactory,
		\Magento\Store\Model\StoreManagerInterface $storeManager,
		\Magento\Customer\Api\CustomerRepositoryInterface $customerRepository,
		\Magento\Checkout\Model\Session $checkoutSession,
		\Magento\Customer\Model\Session $customerSession,
		\Magento\Quote\Api\CartRepositoryInterface $quoteRepository,
		\Magento\Sales\Model\OrderFactory $orderFactory
		) {
		$this->quoteFactory           = $quoteFactory;
		$this->quoteCollectionFactory = $quoteCollectionFactory;
		$this->customerRepository     = $customerRepository;
		$this->_storeManager          = $storeManager;
		$this->checkoutSession        = $checkoutSession;
		$this->_customerSession       = $customerSession;
		$this->quoteRepository        = $quoteRepository;
		$this->orderFactory           = $orderFactory;
	}

	public function execute(\Magento\Framework\Event\Observer $observer)
	{
		$mageQuote = $this->quoteFactory->create();
		$mageQuote->setStore($this->_storeManager->getStore());
		if ($customerId = $this->_customerSession->getCustomerId()) {
			$mageQuote->setCustomer($this->customerRepository->getById($customerId));
		}
		$this->quoteRepository->save($mageQuote);
		$this->checkoutSession->setQuoteId($mageQuote->getId());

		$orderId = $observer->getOrderIds();
		$order = $this->orderFactory->create()->load($orderId);
		if ($order->getId()) {
			$quote = $this->quoteCollectionFactory->create()
			->addFieldToFilter('rfq_parent_quote_id', $order->getQuoteId())
			->getFirstItem();
			if ($quote->getId()) {
				$quote->setData('rfq_parent_quote_id', $mageQuote->getId());
				$quote->save();
			}
		}
	}
}