<?php

namespace Lof\RequestForQuote\Controller\Adminhtml\Quote;

abstract class Create extends \Magento\Sales\Controller\Adminhtml\Order\Create
{
    /**
     * Retrieve quote object
     *
     * @return \Magento\Quote\Model\Quote
     */
    protected function _getQuote()
    {
        return $this->_getSession()->getRfqQuote();
    }

    /**
     * Retrieve order create model
     *
     * @return \Magento\Sales\Model\AdminOrder\Create
     */
    protected function _getOrderCreateModel()
    {
        return $this->_objectManager->get('Lof\RequestForQuote\Model\AdminOrder\Create');
    }
}