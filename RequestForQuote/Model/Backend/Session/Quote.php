<?php
namespace Lof\RequestForQuote\Model\Backend\Session;

use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Customer\Api\GroupManagementInterface;

class Quote extends \Magento\Backend\Model\Session\Quote
{
    /**
     * Quote instance
     *
     * @var Quote
     */
    protected $_rfqQuote;

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
        CustomerRepositoryInterface $customerRepository,
        \Magento\Quote\Api\CartRepositoryInterface $quoteRepository,
        \Magento\Sales\Model\OrderFactory $orderFactory,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        GroupManagementInterface $groupManagement,
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
            $customerRepository,
            $quoteRepository,
            $orderFactory,
            $storeManager,
            $groupManagement,
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
    public function getRfqQuote()
    {
        if ($this->_rfqQuote === null) {
        	$mageQuote = $this->getQuote();
        	if (!$mageQuote->getId()) {
        		$mageQuote = $this->quoteFactory->create();
        		$mageQuote->setStore($this->_storeManager->getStore());
        		if ($customerId = $this->getCustomerId()) {
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
        		if ($customerId = $this->getCustomerId()) {
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