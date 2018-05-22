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

namespace Lof\RequestForQuote\Controller\Quote;

use Magento\Customer\Controller\AccountInterface;
use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\View\Result\PageFactory;
use Magento\Framework\Exception\NotFoundException;

class Delete extends \Lof\RequestForQuote\Controller\AbstractIndex
{
	/**
     * @var \Magento\Framework\Registry
     */
    protected $_coreRegistry;

    /**
     * @param Context                                    $context           
     * @param \Magento\Framework\View\Result\PageFactory $resultPageFactory 
     * @param \Magento\Framework\Registry                $registry          
     */
    public function __construct(
        Context $context,
        \Magento\Framework\View\Result\PageFactory $resultPageFactory,
        \Magento\Framework\Registry $registry,
        \Lof\RequestForQuote\Model\QuoteFactory $quoteFactory,
        \Magento\Quote\Model\QuoteFactory $mageQuoteFactory,
        \Magento\Customer\Model\Session $customerSession,
        \Lof\RequestForQuote\Helper\Data $rfqData
        ) {
        $this->resultPageFactory    = $resultPageFactory;
        $this->_coreRegistry        = $registry;
        parent::__construct($context);
        $this->mageQuoteFactory = $mageQuoteFactory;
        $this->quoteFactory = $quoteFactory;
        $this->rfqData = $rfqData;
        $this->_customerSession = $customerSession;
    }

    /**
     * Delete action
     *
     * @return \Magento\Framework\Controller\ResultInterface
     */
    public function execute()
    {
        /** @var \Magento\Backend\Model\View\Result\Redirect $resultRedirect */
        $resultRedirect = $this->resultRedirectFactory->create();
        // check if we know what should be deleted
        $id = $this->getRequest()->getParam('quote_id');

        if ($id) {
            try {
                // init model and delete
                $model = $this->_objectManager->create('Lof\RequestForQuote\Model\Quote');
                $model->load($id);

                $customerId = $this->_customerSession->getCustomerId();
                if (($customerId == $model->getCustomerId()) && $model->getCustomerId()) {
                    $model->delete();
                // display success message
                    $this->messageManager->addSuccess(__('You deleted the quote.'));
                }
                // go to grid
                return $resultRedirect->setPath('*/*/history');
            } catch (\Exception $e) {
                // display error message
                $this->messageManager->addError($e->getMessage());
                // go back to edit form
                return $resultRedirect->setPath('*/*/view', ['quote_id' => $id]);
            }
        }
        // display error message
        $this->messageManager->addError(__('We can\'t find a quote to delete.'));
        // go to grid
        return $resultRedirect->setPath('*/*/');
    }
}