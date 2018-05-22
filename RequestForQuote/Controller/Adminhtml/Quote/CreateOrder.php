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
use \Magento\Framework\Controller\ResultFactory;

class CreateOrder extends \Magento\Backend\App\Action
{

    protected $_layout;

    /**
     * @var \Magento\Framework\UrlInterface
     */
    protected $_urlBuilder;

    /**
     * @param \Magento\Backend\App\Action\Context
     * @param \Magento\Quote\Api\CartRepositoryInterface
     * @param \Magento\Backend\Model\Session\Quote
     */
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magento\Quote\Api\CartRepositoryInterface $quoteRepository,
        \Magento\Backend\Model\Session\Quote $sessionQuote
        ) {
        parent::__construct($context);
        $this->_sessionQuote   = $sessionQuote;
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
                $quote = $this->quoteRepository->get($this->getRequest()->getParam('magequote_id'));
                $this->_sessionQuote->setCustomerId($quote->getCustomerId());
                $mageQuote = $this->_sessionQuote->getQuote();
                $mageQuote->merge($quote);
                $mageQuote->save();

                $rfqQuote = $this->_objectManager->create('Lof\RequestForQuote\Model\Quote');
                $rfqQuote->load($id)->setTargetQuote($mageQuote->getId())->save();

                // go to grid
                $resultRedirect->setUrl($this->getUrl('sales/order_create/index'));

                return $resultRedirect;         
            } catch (\Exception $e) {
                // display error message
                $this->messageManager->addError($e->getMessage());
                // go back to edit form
                return $resultRedirect->setPath('*/*/edit', ['entity_id' => $id]);
            }
        }
        // display error message
        $this->messageManager->addError(__('We can\'t find a quote to delete.'));
        // go to grid
        return $resultRedirect->setPath('*/*/');
    }
}