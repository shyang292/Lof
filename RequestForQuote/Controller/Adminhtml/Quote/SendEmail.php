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

use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Filesystem;

class SendEmail extends \Magento\Backend\App\Action
{

    protected $_layout;

    /**
     * @param \Magento\Backend\App\Action\Context $context
     * @param \Magento\Framework\Registry $coreRegistry
     */
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magento\Quote\Api\CartRepositoryInterface $quoteRepository,
        \Lof\RequestForQuote\Helper\Mail $rfqMail
    ) {
        parent::__construct($context);
        $this->rfqMail = $rfqMail;
        $this->quoteRepository = $quoteRepository;
    }

    /**
     * Save action
     *
     * @return \Magento\Framework\Controller\ResultInterface
     */
    public function execute()
    {

        /** @var \Magento\Backend\Model\View\Result\Redirect $resultRedirect */
        $resultRedirect = $this->resultRedirectFactory->create();
        // check if data sent
        $id = $this->getRequest()->getParam('entity_id');

        if ($id) {
            try {
                // init model and delete
                $model = $this->_objectManager->create('\Lof\RequestForQuote\Model\Quote');
                $model->load($id);
                $model->setStatus(\Lof\RequestForQuote\Model\Quote::STATE_PENDING);
                $model->save();
                // display success message
                

                $mageQuote = $this->quoteRepository->get($this->getRequest()->getParam('magequote_id'));
                $this->rfqMail->sendNotificationNewQuoteEmail($mageQuote, $model);
                $this->messageManager->addSuccess(__('You sent the confirmation email.'));
                // go to grid
                return $resultRedirect->setPath('*/*/edit', ['entity_id' => $id]);
            } catch (\Exception $e) {
                // display error message
                $this->messageManager->addError($e->getMessage());
                // go back to edit form
                return $resultRedirect->setPath('*/*/edit', ['entity_id' => $id]);
            }
        }
        // display error message
        $this->messageManager->addError(__('We can\'t find the quote.'));
        // go to grid
        return $resultRedirect->setPath('*/*/');
    }
}