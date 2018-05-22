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

namespace Lof\RequestForQuote\Model;

class Quote extends \Magento\Framework\Model\AbstractModel
{

    CONST STATE_PENDING    = 'pending';
    CONST STATE_ORDERED    = 'ordered';
    CONST STATE_CANCELED   = 'cancelled';
    CONST STATE_PROCESSING = 'processing';
    CONST STATE_EMAIL_SENT = 'email_sent';
    CONST STATE_REVIEWED   = 'reviewed';
    CONST STATE_EXPIRED    = 'expired';


    /**
     * Init resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('Lof\RequestForQuote\Model\ResourceModel\Quote');
    }

    /**
     * Get Rule statues
     *
     * @return array
     */
    public function getAvailableStatuses()
    {
        return [
            self::STATE_PENDING  => __('Pending'),
            self::STATE_ORDERED  => __('Ordered'),
            self::STATE_CANCELED => __('Cancelled'),
            self::STATE_REVIEWED => __('Reviewed'),
            self::STATE_EXPIRED  => __('Expired')
        ];
    }

    public function getStatusLabel()
    {
        $status = $this->getData('status');
        $availableStetuses = $this->getAvailableStatuses();
        foreach ($availableStetuses as $k => $v) {
            if ($k == $status) {
                return $v;
            }
        }
    }
}