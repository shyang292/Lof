<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Lof\RequestForQuote\Block\Adminhtml\Quote\Create;

/**
 * Adminhtml sales order create block
 *
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Customer extends \Magento\Sales\Block\Adminhtml\Order\Create\Customer
{
    /**
     * Get buttons html
     *
     * @return string
     */
    public function getButtonsHtml()
    {
        return '';
    }
}
