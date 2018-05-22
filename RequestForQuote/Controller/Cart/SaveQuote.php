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

namespace Lof\RequestForQuote\Controller\Cart;

use Magento\Customer\Api\Data\GroupInterface;

class SaveQuote extends \Magento\Framework\App\Action\Action
{
    /**
     * @var \Magento\Framework\Data\Form\FormKey\Validator
     */
    protected $_formKeyValidator;

    /**
     * @var \Magento\Customer\Model\Session
     */
    protected $_customerSession;

    /**
     * @var \Magento\Quote\Api\CartRepositoryInterface
     */
    protected $quoteRepository;

    /**
     * @var \Magento\Framework\UrlInterface
     */
    protected $_urlBuilder;

    /**
     * @var \Magento\Framework\HTTP\PhpEnvironment\RemoteAddress
     */
    protected $_remoteAddress;

    /**
     * @var \Lof\RequestForQuote\Model\Cart
     */
    protected $quoteCart;

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
     * @param \Magento\Framework\App\Action\Context                            $context                
     * @param \Magento\Framework\Data\Form\FormKey\Validator                   $formKeyValidator       
     * @param \Magento\Customer\Model\Session                                  $customerSession        
     * @param \Magento\Checkout\Model\Session                                  $checkoutSession        
     * @param \Magento\Quote\Api\CartRepositoryInterface                       $quoteRepository        
     * @param \Magento\Framework\Url                                           $urlBuilder             
     * @param \Magento\Framework\HTTP\PhpEnvironment\RemoteAddress             $remoteAddress          
     * @param \Lof\RequestForQuote\Model\Cart                                  $quoteCart              
     * @param \Lof\RequestForQuote\Model\ResourceModel\Quote\CollectionFactory $quoteCollectionFactory 
     * @param \Lof\RequestForQuote\Helper\Mail                                 $rfqMail  
     * @param \Lof\RequestForQuote\Helper\Data                                 $rfqHelper                
     */
    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Magento\Framework\Data\Form\FormKey\Validator $formKeyValidator,
        \Magento\Customer\Model\Session $customerSession,
        \Magento\Checkout\Model\Session $checkoutSession,
        \Magento\Quote\Api\CartRepositoryInterface $quoteRepository,
        \Magento\Framework\Url $urlBuilder,
        \Magento\Framework\HTTP\PhpEnvironment\RemoteAddress $remoteAddress,
        \Lof\RequestForQuote\Model\Cart $quoteCart,
        \Lof\RequestForQuote\Model\ResourceModel\Quote\CollectionFactory $quoteCollectionFactory,
        \Lof\RequestForQuote\Helper\Mail $rfqMail,
        \Lof\RequestForQuote\Helper\Data $rfqHelper
        ) {
        parent::__construct($context);
        $this->_formKeyValidator       = $formKeyValidator;
        $this->_customerSession        = $customerSession;
        $this->checkoutSession         = $checkoutSession;
        $this->quoteRepository         = $quoteRepository;
        $this->_urlBuilder             = $urlBuilder;
        $this->_remoteAddress          = $remoteAddress;
        $this->quoteCart               = $quoteCart;
        $this->_quoteCollectionFactory = $quoteCollectionFactory;
        $this->rfqMail                 = $rfqMail;
        $this->_dataHelper             = $rfqHelper;
    }

    /**
     * Create order action
     *
     * @return \Magento\Framework\Controller\ResultInterface
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    public function execute()
    {
        if (!$this->_formKeyValidator->validate($this->getRequest())) {
            return $this->resultRedirectFactory->create()->setPath('*/*/');
        }
        $quote = $this->quoteCart->getQuote();
        $post  = $this->getRequest()->getPostValue();
        $ip    = $this->_remoteAddress->getRemoteAddress();

        $resultRedirect = $this->resultRedirectFactory->create();

        if (!isset($post['username']) || (isset($post['username']) && !$post['username'])) {
            $this->messageManager->addError(__('Please enter your email.'));
            $resultRedirect->setUrl($this->_urlBuilder->getUrl('quotation/quote'));
            return $resultRedirect;
        }
        
        try {
            $quote_id_prefix = $this->_dataHelper->getConfig("general/quote_id_prefix");
            $digits_number = $this->_dataHelper->getConfig("general/digits_number");
            $digits_number = !$digits_number?(int)$digits_number:1000000000;
            $limit = $this->_dataHelper->getConfig("quote_item/limit_useage");
            $expiry_day = $this->_dataHelper->getConfig("quote_item/expiry_day");
            $firstname = isset($post['first_name'])?$post['first_name']:"";
            $lastname = isset($post['last_name'])?$post['last_name']:"";
            $company = isset($post['company'])?$post['company']:"";
            $telephone = isset($post['telephone'])?$post['telephone']:"";
            $address = isset($post['address'])?$post['address']:"";
            $region_id = isset($post['region_id'])?$post['region_id']:"0";
            $postcode = isset($post['postcode'])?$post['postcode']:"0";
            $country_id = isset($post['country_id'])?$post['country_id']:"";
            $tax_id = isset($post['tax_id'])?$post['tax_id']:"";
            $questions = isset($post['question'])?$post['question']:[];
            $question_string = '';
            if(is_array($questions) && $questions){
                $tmp_questions = [];
                foreach($questions as $key=>$question) {
                    $label = isset($question['label'])?$question['label']:$key;
                    $val = isset($question['value'])?$question['value']:'';
                    $label = strip_tags($label);
                    $label = trim($label);
                    $label = stripslashes($label);
                    $label = addslashes($label);
                    $val = strip_tags($val);
                    $val = trim($val);
                    $val = stripslashes($val);
                    $val = addslashes($val);
                    if($val) {
                        $tmp_questions[$key] = ['value'=>$val, 'label' => $label];
                    }
                }
                $question_string = serialize($tmp_questions);
            }
            /** MAGE QUOTE */
            if (!$this->getCustomerSession()->isLoggedIn()) {
                $quote->setCustomerId(null)
                ->setCustomerEmail($post['username'])
                ->setCustomerNote(strip_tags($post['customer_note']))
                ->setCustomerIsGuest(true)
                ->setCustomerGroupId(GroupInterface::NOT_LOGGED_IN_ID);

                if (isset($post['billing'])) {

                    $billing  = $post['billing'];
                    $street = $billing['street'];
                    if (is_array($street)) {
                        $street = trim(implode("\n", $street));
                    }
                    if($billing['firstname']) {
                        $firstname = (string) $billing['firstname'];
                    }
                    if($billing['lastname']) {
                        $lastname = (string) $billing['lastname'];
                    }
                    $quote->getBillingAddress()
                    ->setCountryId((string) $billing['country_id'])
                    ->setCity((string) $billing['city'])
                    ->setPostcode((string) $billing['postcode'])
                    ->setRegionId((string) $billing['region_id'])
                    ->setRegion((string) $billing['region'])
                    ->setFirstname((string) $billing['firstname'])
                    ->setLastname((string) $billing['lastname'])
                    ->setTelephone((string) $billing['telephone'])
                    ->setCompany((string) $billing['company'])
                    ->setStreet($street)
                    ->setCollectShippingRates(true);

                    if (isset($post['billing-address-same-as-shipping'])) {
                        $quote->getShippingAddress()
                        ->setCountryId((string) $billing['country_id'])
                        ->setCity((string) $billing['city'])
                        ->setPostcode((string) $billing['postcode'])
                        ->setRegionId((string) $billing['region_id'])
                        ->setRegion((string) $billing['region'])
                        ->setFirstname((string) $billing['firstname'])
                        ->setLastname((string) $billing['lastname'])
                        ->setTelephone((string) $billing['telephone'])
                        ->setCompany((string) $billing['company'])
                        ->setStreet($street)
                        ->setSameAsBilling(1)
                        ->setCollectShippingRates(true);
                    } else {
                        $shipping = $post['shipping'];
                        $street  = $shipping['street'];
                        if (is_array($street)) {
                            $street = trim(implode("\n", $street));
                        }
                        $quote->getShippingAddress()
                        ->setCountryId((string) $shipping['country_id'])
                        ->setCity((string) $shipping['city'])
                        ->setPostcode((string) $shipping['postcode'])
                        ->setRegionId((string) $shipping['region_id'])
                        ->setRegion((string) $shipping['region'])
                        ->setFirstname((string) $shipping['firstname'])
                        ->setLastname((string) $shipping['lastname'])
                        ->setTelephone((string) $shipping['telephone'])
                        ->setCompany((string) $shipping['company'])
                        ->setStreet($street)
                        ->setSameAsBilling(1)
                        ->setCollectShippingRates(true);
                    }

                    $this->quoteRepository->save($quote);
                    $this->quoteCart->save();
                }   
                if (isset($post['firstname'])) {
                    $shipping = $post['shipping'];
                    $quote->setFirstname($post['firstname']);
                }
                $quote->setRemoteIp($ip);
                $quote->setData('rfq_parent_quote_id', null);
                $quote->save();
            } else {
                if(!$quote->getCustomerId()){
                    $customer_object = $this->getCustomerSession()->getCustomer();

                    $firstname = $customer_object->getFirstName();
                    $lastname = $customer_object->getLastName();
                    $group_id = $customer_object->getGroupId();
                    $billing_address = $customer_object->getDefaultBilling();
                    $email = $customer_object->getEmail();

                    //Get billing address information
                    /*
                    $telephone = isset($post['telephone'])?$post['telephone']:"";
                    $address = isset($post['address'])?$post['address']:"";
                    $region_id = isset($post['region_id'])?$post['region_id']:"0";
                    $postcode = isset($post['postcode'])?$post['postcode']:"0";
                    $country_id = isset($post['country_id'])?$post['country_id']:"";
                    $tax_id = isset($post['tax_id'])?$post['tax_id']:"";
                    */

                    $quote->setCustomerId((int)$customer_object->getId())
                        ->setCustomerEmail($email)
                        ->setFirstname($firstname)
                        ->setLastname($lastname)
                        ->setCustomerGroupId($group_id);
                }
                $quote->setRemoteIp($ip);
                $quote->setCustomerNote(strip_tags($post['customer_note']));
                $quote->setData('rfq_parent_quote_id', null);
                $quote->save();
            }
            $expiry = null;
            if($expiry_day) {
                $expiry_day = (int)$expiry_day;
                $current_date = $quote->getCreatedAt();
                $date = strtotime("+".$expiry_day." days", strtotime($current_date));
                $expiry =  date("Y-m-d H:i:s", $date);
            }    
            /** RFQ QUOTE */
            $count = $this->_quoteCollectionFactory->create()->getSize();
            if ($count) {
                $incrementId = $digits_number + $count + 1;
            } else {
                $incrementId = $digits_number+1;
            }
            $incrementId = $quote_id_prefix.$incrementId;
            $customer = $quote->getCustomer();
            $email = $customer ? $quote->getCustomerEmail() : $customer->getEmail();
            if(!$email) {
                $email = isset($post['username'])?$post['username']:"";
            }
            $data = [
                'quote_id'     => $quote->getId(),
                'increment_id' => $incrementId,
                'status'       => \Lof\RequestForQuote\Model\Quote::STATE_PENDING,
                'email'        => $email,
                'customer_id'  => $customer ? $customer->getId() : '',
                'limit_useage' => $limit,
                'expiry'       => $expiry,
                'telephone'    => $telephone,
                'tax_id'       => $tax_id,
                'first_name'   => $firstname,
                'last_name'    => $lastname,
                'company'      => $company,
                'address'      => $address,
                'region_id'    => $region_id,
                'postcode'     => $postcode,
                'country_id'   => $country_id,
                'question'     => $question_string
            ];

            $rfqQuote = $this->_objectManager->create('Lof\RequestForQuote\Model\Quote');
            $rfqQuote->setData($data);
            $rfqQuote->save(); 

            $this->checkoutSession->setRfqLastQuoteId($rfqQuote->getId());
       
            /** SEND CONFIRMATIONS EMAIL */
            $this->rfqMail->sendNotificationNewQuoteEmail($quote, $rfqQuote);
            

            $resultRedirect->setUrl($this->_urlBuilder->getUrl('quotation/cart/success'));
        } catch (\Exception $e) {
            $this->messageManager->addError(__('Something went wrong while processing your quote. Please try again later.'));
            $resultRedirect->setUrl($this->_urlBuilder->getUrl('quotation/quote'));
        }
        return $resultRedirect;
    }

    /**
     * Get customer session object
     *
     * @return \Magento\Customer\Model\Session
     * @codeCoverageIgnore
     */
    public function getCustomerSession()
    {
        return $this->_customerSession;
    }

}
