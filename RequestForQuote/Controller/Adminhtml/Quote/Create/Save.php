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

namespace Lof\RequestForQuote\Controller\Adminhtml\Quote\Create;

class Save extends \Lof\RequestForQuote\Controller\Adminhtml\Quote\Create
{
    /**
     * @var \Magento\Framework\HTTP\PhpEnvironment\RemoteAddress
     */
    protected $_remoteAddress;

    /**
     * @var \Lof\RequestForQuote\Model\ResourceModel\Quote\Collection
     */
    protected $_quoteCollectionFactory;

    /**
     * @var \Lof\RequestForQuote\Helper\Mail
     */
    protected $rfqMail;
    /**
     * @var \Lof\RequestForQuote\Helper\Data
     */
    protected $_dataHelper;

    /**
     * @param \Magento\Backend\App\Action\Context                              $context                
     * @param \Magento\Catalog\Helper\Product                                  $productHelper          
     * @param \Magento\Framework\Escaper                                       $escaper                
     * @param \Magento\Framework\View\Result\PageFactory                       $resultPageFactory      
     * @param \Magento\Backend\Model\View\Result\ForwardFactory                $resultForwardFactory   
     * @param \Lof\RequestForQuote\Model\ResourceModel\Quote\CollectionFactory $quoteCollectionFactory 
     * @param \Magento\Framework\HTTP\PhpEnvironment\RemoteAddress             $remoteAddress          
     * @param \Lof\RequestForQuote\Helper\Mail                                 $rfqMail    
     * @param \Lof\RequestForQuote\Helper\Data                                 $rfqHelper             
     */
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magento\Catalog\Helper\Product $productHelper,
        \Magento\Framework\Escaper $escaper,
        \Magento\Framework\View\Result\PageFactory $resultPageFactory,
        \Magento\Backend\Model\View\Result\ForwardFactory $resultForwardFactory,
        \Lof\RequestForQuote\Model\ResourceModel\Quote\CollectionFactory $quoteCollectionFactory,
        \Magento\Framework\HTTP\PhpEnvironment\RemoteAddress $remoteAddress,
        \Lof\RequestForQuote\Helper\Mail $rfqMail,
        \Lof\RequestForQuote\Helper\Data $rfqHelper
        ) {
        parent::__construct($context, $productHelper, $escaper, $resultPageFactory, $resultForwardFactory);
        $this->_remoteAddress          = $remoteAddress;
        $this->_quoteCollectionFactory = $quoteCollectionFactory;
        $this->rfqMail                 = $rfqMail;
        $this->_dataHelper             = $rfqHelper;
    }

    /**
     * Saving quote and create order
     *
     * @return \Magento\Backend\Model\View\Result\Forward|\Magento\Backend\Model\View\Result\Redirect
     *
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     */
    public function execute()
    {
        /** @var \Magento\Backend\Model\View\Result\Redirect $resultRedirect */
        $resultRedirect = $this->resultRedirectFactory->create();
        try {
            // check if the creation of a new customer is allowed
            if (!$this->_authorization->isAllowed('Lof_RequestForQuote::quote_save')
                && !$this->_getSession()->getCustomerId()
                && !$this->_getSession()->getQuote()->getCustomerIsGuest()
                ) {
                return $this->resultForwardFactory->create()->forward('denied');
        }
        $quote_id_prefix = $this->_dataHelper->getConfig("general/quote_id_prefix");
        $digits_number = $this->_dataHelper->getConfig("general/digits_number");
        $digits_number = !$digits_number?(int)$digits_number:10000;
        $quote    = $this->_getOrderCreateModel()->getQuote();
        $quote->setCustomerId($this->_getSession()->getCustomerId());
        $ip       = $this->_remoteAddress->getRemoteAddress();
        $post     = $this->getRequest()->getPost();
        $customer = $quote->getCustomer();

        $quote->setRemoteIp($ip);
//        $quote->setCustomerNote(strip_tags($post['order']['account']['customer_note']));
        $quote->setCustomerEmail(($customer && $customer->getEmail()) ? $customer->getEmail() : $post['order']['account']['email']);
        $quote->setData('rfq_parent_quote_id', null);
        $quote->save();

        /** RFQ QUOTE */
        $count = $this->_quoteCollectionFactory->create()->getSize();
        if ($count) {
            $incrementId = $digits_number + $count + 1;
        } else {
            $incrementId = $digits_number+1;
        }
        $incrementId = $quote_id_prefix.$incrementId;
        $data = [
            'quote_id'     => $quote->getId(),
            'increment_id' => $incrementId,
            'status'       => \Lof\RequestForQuote\Model\Quote::STATE_PENDING,
            'email'        => ($customer && $customer->getEmail()) ? $customer->getEmail() : $post['order']['account']['email'],
            'customer_id'  => $customer ? $customer->getId() : ''
        ];
        $rfqQuote = $this->_objectManager->create('Lof\RequestForQuote\Model\Quote');
        $rfqQuote->setData($data);
        $rfqQuote->save();

        /** SEND CONFIRMATIONS EMAIL */
        $this->rfqMail->sendNotificationNewQuoteEmail($quote, $rfqQuote);

        $this->_getSession()->clearRfqQuote();

        $this->messageManager->addSuccess(__('You created the quote.'));

        $resultRedirect->setPath('quotation/quote/edit', ['entity_id' => $rfqQuote->getId()]);

    } catch (\Magento\Framework\Exception\LocalizedException $e) {
        $message = $e->getMessage();
        if (!empty($message)) {
            $this->messageManager->addError($message);
        }
        $resultRedirect->setPath('quotation/quote/*');
    } catch (\Exception $e) {
        $this->messageManager->addException($e, __('Quote saving error: %1', $e->getMessage()));
        $resultRedirect->setPath('quotation/quote/*');
    }
    return $resultRedirect;
}
}
