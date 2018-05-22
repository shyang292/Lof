<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Lof\RequestForQuote\Controller\Adminhtml\Quote\Create;

class Index extends \Lof\RequestForQuote\Controller\Adminhtml\Quote\Create
{
    /**
     * Index page
     *
     * @return void
     */
    public function execute()
    {
        $this->_initSession();

        /** @var \Magento\Backend\Model\View\Result\Page $resultPage */
        $resultPage = $this->resultPageFactory->create();
        $resultPage->setActiveMenu('Magento_Sales::sales');
        $resultPage->getConfig()->getTitle()->prepend(__('Quotations'));
        $resultPage->getConfig()->getTitle()->prepend(__('New Quote'));
        return $resultPage;
    }
}