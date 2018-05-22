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

class Edit extends \Magento\Backend\App\Action
{
    /**
     * @var \Magento\Framework\View\Result\PageFactory
     */
    protected $resultPageFactory;

    /**
     * @param \Magento\Backend\App\Action\Context $context
     * @param \Magento\Framework\Registry $coreRegistry
     * @param \Magento\Framework\View\Result\PageFactory $resultPageFactory
     */
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magento\Framework\Registry $coreRegistry,
        \Magento\Framework\View\Result\PageFactory $resultPageFactory,
        \Magento\Quote\Api\CartRepositoryInterface $quoteRepository,
        \Lof\RequestForQuote\Model\ResourceModel\Quote\CollectionFactory $quoteCollectionFactory
    ) {
        $this->resultPageFactory = $resultPageFactory;
        parent::__construct($context);
        $this->_coreRegistry          = $coreRegistry;
        $this->quoteRepository        = $quoteRepository;
        $this->quoteCollectionFactory = $quoteCollectionFactory;
    }

    /**
     * {@inheritdoc}
     */
    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('Lof_RequestForQuote::quote_edit');
    }

    public function execute()
    {
        // 1. Get ID and create model
        $id = $this->getRequest()->getParam('entity_id');
        $model = $this->_objectManager->create('Lof\RequestForQuote\Model\Quote');

        // 2. Initial checking
        if ($id) {
            $model->load($id);
            if (!$model->getId()) {
                $this->messageManager->addError(__('This quote no longer exists.'));
                /** @var \Magento\Backend\Model\View\Result\Redirect $resultRedirect */
                $resultRedirect = $this->resultRedirectFactory->create();
                return $resultRedirect->setPath('*/*/');
            }
        }
        // 3. Set entered data if was error when we do save
        $data = $this->_objectManager->get('Magento\Backend\Model\Session')->getFormData(true);
        if (!empty($data)) {
            $model->setData($data);
        }

        if ($model->getId()) {
            $mageQuote = $this->quoteRepository->get($model->getQuoteId()); 
            $this->_coreRegistry->register('mage_quote', $mageQuote);
        }



        // 4. Register model to use later in forms
        $this->_coreRegistry->register('quotation_quote', $model);

        /** @var \Magento\Backend\Model\View\Result\Page $resultPage */
        $resultPage = $this->resultPageFactory->create();

        // 5. Build edit form
        $resultPage->setActiveMenu('Magento_Sales::sales')
            ->addBreadcrumb(__('RequestForQuote'), __('RequestForQuote'))
            ->addBreadcrumb(__('Quote'), __('Quote'));
        $resultPage->addBreadcrumb(
            $id ? __('Edit Quote') : __('New Quote'),
            $id ? __('Edit Quote') : __('New Quote')
        );
        $resultPage->getConfig()->getTitle()->prepend(__('Quotations'));
        $resultPage->getConfig()->getTitle()->prepend($model->getId() ? '#' . $model->getIncrementId() : __('New Quote'));
        return $resultPage;
    }
}
