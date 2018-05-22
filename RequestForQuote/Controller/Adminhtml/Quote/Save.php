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

namespace Lof\RequestForQuote\Controller\Adminhtml\Quote;

use Lof\RequestForQuote\Model\Quote;

class Save extends \Magento\Backend\App\Action
{

    protected $_quote;

    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Lof\RequestForQuote\Model\ResourceModel\Quote\CollectionFactory $quoteCollectionFactory,
        \Magento\Quote\Api\CartRepositoryInterface $quoteRepository,
        \Lof\RequestForQuote\Helper\Mail $mailData,
        \Lof\RequestForQuote\Helper\Data $helperData
        ) {
        parent::__construct($context);
        $this->quoteCollectionFactory = $quoteCollectionFactory;
        $this->quoteRepository        = $quoteRepository;
        $this->mailData               = $mailData;
        $this->helperData             = $helperData;
    }

    /**
     * Check the permission to run it
     *
     * @return boolean
     */
    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('Lof_RequestForQuote::quote_save');
    }

    public function execute()
    {
        /** @var \Magento\Backend\Model\View\Result\Redirect $resultRedirect */
        $resultRedirect = $this->resultRedirectFactory->create();
        
        // check if we know what should be deleted
        $id = $this->getRequest()->getParam('quote_id');
        if ($id) {
            try {
                $data = $this->getRequest()->getPostValue();
                $quote = $this->quoteCollectionFactory->create()->addFieldToFilter('quote_id', $id)->getFirstItem();

                $customer_email = $quote->getEmail();
                $customer_id = $quote->getCustomerId();
//                $this->updateItems($data['quote']);
                $mage_quote = $this->getQuote();

                //Update mage quote data
                if(isset($data['mage_quote']) && $data['mage_quote']) {
                    foreach($data['mage_quote'] as $key=>$val){
                        $mage_quote->setData($key, $val);
                    }
                }
                if(!$mage_quote->getCustomerId() && $customer_email){
                    $storeManager = $this->_objectManager->get('Magento\Store\Model\StoreManagerInterface');
                    $storeId = (int) $mage_quote->getStoreId();
                    $store = $storeManager->getStore($storeId);

                    $customerFactory =  $this->_objectManager->get('\Magento\Customer\Model\CustomerFactory'); 
                    $customer=$customerFactory->create();
                    $customer->setWebsiteId((int)$store->getWebsiteId());
                    $customer->loadByEmail($customer_email);
                    
                    if($customer->getId()){
                        $mage_quote->setCustomerId($customer->getId());
                        $mage_quote->setCustomerGroupId($customer->getGroupId());
                        $customer_id = $customer->getId();
                    }
                }
                //End update mage quote data
                if($mage_quote->getCustomerId()) {
                    $mage_quote->setCustomerIsGuest(0);
                    $customer_id = $mage_quote->getCustomerId();
                }
                $mage_quote->getBillingAddress();
                $mage_quote->getShippingAddress()->setCollectShippingRates(true);
                $mage_quote->collectTotals();
                $mage_quote->save();

                $oldExpiredDate = $quote->getExpiry();

                if ($quote->getId() && $quote->getQuoteId()==$id) {

                    $quote->setData('expiry', $data['expiry']);

                    if ($this->getRequest()->getParam('send')) {
                        if ($quote->getQuoteId()) {
                            $mageQuote = $this->quoteRepository->get($quote->getQuoteId());
                            switch ($data['status']) {
                                case Quote::STATE_CANCELED:
                                    $this->mailData->sendNotificationQuoteCancelledEmail($mageQuote, $quote);
                                    break;

                                case Quote::STATE_REVIEWED:
                                    $this->mailData->sendNotificationQuoteReviewedEmail($mageQuote, $quote);
                                    break;

                                case Quote::STATE_EXPIRED:
                                    $this->mailData->sendNotificationQuoteExpiredEmail($mageQuote, $quote);
                                    break;
                            }
                        }
                    }
                    if($customer_id) {
                        $quote->setCustomerId($customer_id);
                    }
                    if(isset($data['admin_note'])) {
                        $quote->setData('admin_note', $data['admin_note']);
                    }
                    if(isset($data['terms'])) {
                        $quote->setData('terms', $data['terms']);
                    }
                    if(isset($data['wtexpect'])) {
                        $quote->setData('wtexpect', $data['wtexpect']);
                    }
                    if(isset($data['break_line'])) {
                        $quote->setData('break_line', $data['break_line']);
                    }
                    $quote->setData('status', $data['status']);
                    $quote->save();

                    $oldExpiredDate = $this->helperData->formatDate($oldExpiredDate);
                    $newExpiredDate = $this->helperData->formatDate($quote->getExpiry());

                    if ($this->getRequest()->getParam('send')) {
                        if ($oldExpiredDate!=$newExpiredDate) {
                            $this->mailData->sendNotificationChangeExpiredEmail($mageQuote, $quote, $oldExpiredDate, $newExpiredDate);
                        }
                    }

                    $this->messageManager->addSuccess(__('You saved the quote'));
                    return $resultRedirect->setPath('*/*/edit', ['entity_id' => $quote->getId()]);
                }
            } catch (\Exception $e) {
                $this->messageManager->addError($e->getMessage());
            }
            return $resultRedirect->setPath('*/*/edit', ['entity_id' => $id]);
        }
        // display error message
        $this->messageManager->addError(__('We can\'t find a quote.'));
        // go to grid
        return $resultRedirect->setPath('*/*/');
    }

    public function getQuote()
    {
        if ($this->_quote == NULL) {
            $data  = $this->getRequest()->getPostValue();
            $this->_quote = $this->quoteRepository->get($data['quote_id']);
        }
        return $this->_quote;
    }

    public function updateItems($data)
    {
        $infoDataObject = new \Magento\Framework\DataObject($data);

        $quote = $this->getQuote();

        $qtyRecalculatedFlag = false;
        foreach ($data as $itemId => $itemInfo) {
            if (isset($itemInfo['customprice'])) {
                $itemInfo['customprice'] = (float) $itemInfo['customprice'];
            }
            if (isset($itemInfo['description'])) {
                $itemInfo['description'] = strip_tags(trim($itemInfo['description']));
            }
            $item = $quote->getItemById($itemId);

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

                if (isset($itemInfo['customprice']) && $itemInfo['customprice'] && $itemInfo['customprice']!=($item->getPrice())) {
                    if (!$item->getCustomPrice()) {
                        $price  = [
                            'price'               => $item->getPrice(),
                            'base_price'          => $item->getBasePrice(),
                            'price_incl_tax'      => $item->getPriceInclTax(),
                            'base_price_incl_tax' => $item->getBasePriceInclTax()
                        ];

                        $option = [
                            'code'  => 'product_price',
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

                if ($item->getHasError()) {
                    //throw new \Magento\Framework\Exception\LocalizedException(__($item->getMessage()));
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
