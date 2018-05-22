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

namespace Lof\RequestForQuote\Observer\Backend;

use \Magento\Sales\Model\Order;
use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Message\ManagerInterface;

class CheckoutSubmitAllAfter implements ObserverInterface
{
	/**
	 * @var \Lof\RequestForQuote\Model\ResourceModel\Quote\Collection
	 */
	protected $quoteCollectionFactory;

	/**
	 * @param \Lof\RequestForQuote\Model\QuoteFactory
	 * @param \Lof\RequestForQuote\Model\ResourceModel\Quote\CollectionFactory
	 */
	public function __construct(
		\Lof\RequestForQuote\Model\QuoteFactory $quoteFactory,
		\Lof\RequestForQuote\Model\ResourceModel\Quote\CollectionFactory $quoteCollectionFactory
	) {
		$this->quoteCollectionFactory = $quoteCollectionFactory;
	}

	public function execute(\Magento\Framework\Event\Observer $observer)
	{
		$order = $observer->getOrder();
		$quote = $observer->getQuote();
		if ($quote->getId() &&  $order->getStatus()== Order::STATE_COMPLETE) {
			$collection = $this->quoteCollectionFactory->create();
			$quote = $collection->addFieldToFilter('target_quote', $quote->getId())->getFirstItem();
			$quote->setStatus(\Lof\RequestForQuote\Model\Quote::STATE_ORDERED);
			$quote->save();
		}
        
	}
}