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

namespace Lof\RequestForQuote\Helper;

class Mail extends \Magento\Framework\App\Helper\AbstractHelper
{

    protected $_currency;

    /**
     * @var \Magento\Framework\Stdlib\DateTime\TimezoneInterface
     */
    protected $timezone;

    /**
     * @var \Magento\Framework\View\LayoutInterface
     */
    protected $_layout;

    /**
     * @var \Magento\Framework\Message\ManagerInterface
     */
    protected $messageManager;

    /**
     * @param \Magento\Framework\App\Helper\Context
     * @param \Magento\Framework\Filter\FilterManager
     * @param \Magento\Framework\Translate\Inline\StateInterface
     * @param \Magento\Framework\Stdlib\DateTime\DateTime
     * @param \Magento\Framework\Message\ManagerInterface
     * @param \Magento\Store\Model\StoreManagerInterface
     * @param \Magento\Framework\Stdlib\DateTime\TimezoneInterface
     * @param \Magento\Framework\View\LayoutInterface
     * @param \Magento\Framework\Url
     * @param \Magento\Framework\Mail\Template\TransportBuilder
     */
    public function __construct(
        \Magento\Framework\App\Helper\Context $context,
        \Magento\Framework\Filter\FilterManager $filterManager,
        \Magento\Framework\Translate\Inline\StateInterface $inlineTranslation,
        \Magento\Framework\Stdlib\DateTime\DateTime $dateTime,
        \Magento\Framework\Message\ManagerInterface $messageManager,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Framework\Stdlib\DateTime\TimezoneInterface $timezone,
        \Magento\Framework\View\LayoutInterface $layout,
        \Magento\Framework\Url $urlBuilder,
        \Magento\Framework\Mail\Template\TransportBuilder $transportBuilder,
        \Lof\RequestForQuote\Helper\Data $rfqHelper
        ) {
        parent::__construct($context);
        $this->context           = $context;
        $this->filterManager     = $filterManager;
        $this->inlineTranslation = $inlineTranslation;
        $this->dateTime          = $dateTime;
        $this->messageManager    = $messageManager;
        $this->transportBuilder  = $transportBuilder;
        $this->_storeManager     = $storeManager;
        $this->timezone          = $timezone;
        $this->_layout           = $layout;
        $this->_urlBuilder       = $urlBuilder;
        $this->rfqHelper         = $rfqHelper;
    }

    /**
     * Return brand config value by key and store
     *
     * @param string $key
     * @param \Magento\Store\Model\Store|int|string $store
     * @return string|null
     */
    public function getConfig($key, $store = null)
    {
        $store = $this->_storeManager->getStore($store);
        $websiteId = $store->getWebsiteId();

        $result = $this->scopeConfig->getValue(
            'requestforquote/' . $key,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
            $store);
        return $result;
    }

    public function send( $templateName, $senderName, $senderEmail, $recipientEmail, $recipientName, $variables, $storeId, $trigger = '')
    {
        $this->inlineTranslation->suspend();
        try {
            $storeScope = \Magento\Store\Model\ScopeInterface::SCOPE_STORE;
            $transport = $this->transportBuilder
            ->setTemplateIdentifier($templateName)
            ->setTemplateOptions([
                'area'  => \Magento\Framework\App\Area::AREA_FRONTEND,
                'store' => $storeId,
                ])
            ->setTemplateVars($variables)
            ->setFrom([
                'name'  => $senderName,
                'email' => $senderEmail
                ])
            ->addTo($recipientEmail, $recipientName)
            ->setReplyTo($senderEmail)
            ->getTransport();

            $transport->sendMessage();
            $this->inlineTranslation->resume();
        } catch (\Magento\Framework\Exception\LocalizedException $e) {
            $this->messageManager->addError($e->getMessage());
        } catch (\Exception $e) {
            $this->messageManager->addError(__('We can\'t send the email quote right now.'));
            $this->logger->critical($e);
        }

        $this->inlineTranslation->resume();
        return true;
    }

    /**
     * Get formatted order created date in store timezone
     *
     * @param   string $format date format type (short|medium|long|full)
     * @return  string
     */
    public function getCreatedAtFormatted($time, $store, $format)
    {
        return $this->timezone->formatDateTime(
            new \DateTime($time),
            $format,
            $format,
            null,
            $this->timezone->getConfigTimezone('store', $store)
            );
    }

    public function sendNotificationNewQuoteEmail($mageQuote, $quote, $emailMessage = false)
    {
        $block = $this->_layout->createBlock('Lof\RequestForQuote\Block\Quote\Items');
        $templateName = $this->getConfig('email_templates/new_quote');
        $storeScope   = \Magento\Store\Model\ScopeInterface::SCOPE_STORE;
        $senderId     = $this->getConfig('general/sender_email_identity');

        if ($senderId) {
            $sender_email   = $this->scopeConfig->getValue('trans_email/ident_' . $senderId . '/name', $storeScope);
            $recipientEmail = $mageQuote->getCustomerEmail();
            $recipientName  = '';
            $variables      = [
                'increment_id' => $quote->getIncrementId(),
                'created_at'   => $this->getCreatedAtFormatted($mageQuote->getCreatedAt(), $mageQuote->getstore(), \IntlDateFormatter::MEDIUM),
                'quote'        => $mageQuote
            ];

            $storeId     = $this->_storeManager->getStore()->getId();
            $senderName  = $this->context->getScopeConfig()->getValue("trans_email/ident_" . $senderId . "/name");
            $senderEmail = $this->context->getScopeConfig()->getValue("trans_email/ident_" . $senderId . "/email");

            $this->send($templateName, $senderName, $senderEmail, $recipientEmail, $recipientName, $variables, $storeId);

            $bcc = $this->getConfig('general/bcc');
            if ($bcc) {
                $bcc = explode(",", $bcc);
                foreach ($bcc as $email) {
                    $email = trim($email);
                    $this->send($templateName, $senderName, $senderEmail, $email, $recipientName, $variables, $storeId);
                }
            }
            return true;
        }
        return false;
    }

    public function sendNotificationAcceptQuoteEmail($mageQuote, $quote, $emailMessage = false)
    {
        $block          = $this->_layout->createBlock('Lof\RequestForQuote\Block\Quote\Items');
        $templateName   = $this->getConfig('email_templates/accept_quote');
        $storeScope     = \Magento\Store\Model\ScopeInterface::SCOPE_STORE;
        $senderId       = $this->getConfig('general/sender_email_identity');
        $sender_email   = $this->scopeConfig->getValue('trans_email/ident_' . $senderId . '/name', $storeScope);
        $recipientEmail = $mageQuote->getCustomerEmail();
        $recipientName  = '';

        if ($senderId) {
            $url = $this->_storeManager->getStore()->getUrl();
            $variables      = [
                'increment_id'  => $quote->getIncrementId(),
                'created_at'    => $this->getCreatedAtFormatted($mageQuote->getCreatedAt(), $mageQuote->getstore(), \IntlDateFormatter::MEDIUM),
                'quote'         => $mageQuote,
                'purchase_link' => $url,
                'expired_at'    => $this->rfqHelper->formatDate($quote->getExpiry(), \IntlDateFormatter::SHORT)
            ];

            $storeId        = $this->_storeManager->getStore()->getId();
            $senderName     = $this->context->getScopeConfig()->getValue("trans_email/ident_" . $senderId . "/name");
            $senderEmail    = $this->context->getScopeConfig()->getValue("trans_email/ident_" . $senderId . "/email");

            $this->send($templateName, $senderName, $senderEmail, $recipientEmail, $recipientName, $variables, $storeId);

            $sendQuoteRequestNotificationTo = $this->getConfig('general/send_quote_request_notification_to');
            if ($sendQuoteRequestNotificationTo) {
                $sendQuoteRequestNotificationTo = explode(",", $sendQuoteRequestNotificationTo);
                foreach ($sendQuoteRequestNotificationTo as $email) {
                    $this->send($templateName, $senderName, $senderEmail, $email, $recipientName, $variables, $storeId);
                }
            }
            return true;
        }
        return false;
    }

    public function sendNotificationQuoteCancelledEmail($mageQuote, $quote, $emailMessage = false)
    {
        $block          = $this->_layout->createBlock('Lof\RequestForQuote\Block\Quote\Items');
        $templateName   = $this->getConfig('email_templates/quote_cancelled');
        $storeScope     = \Magento\Store\Model\ScopeInterface::SCOPE_STORE;
        $senderId       = $this->getConfig('general/sender_email_identity');
        $sender_email   = $this->scopeConfig->getValue('trans_email/ident_' . $senderId . '/name', $storeScope);
        $recipientEmail = $mageQuote->getCustomerEmail();
        $recipientName  = '';

        if ($senderId) {
            $url = $this->_storeManager->getStore()->getUrl();
            $variables      = [
                'increment_id'  => $quote->getIncrementId(),
                'created_at'    => $this->getCreatedAtFormatted($mageQuote->getCreatedAt(), $mageQuote->getstore(), \IntlDateFormatter::MEDIUM),
                'quote'         => $mageQuote,
                'purchase_link' => $url,
                'expired_at'    => $this->rfqHelper->formatDate($quote->getExpiry(), \IntlDateFormatter::SHORT)
            ];

            $storeId     = $this->_storeManager->getStore()->getId();
            $senderName  = $this->context->getScopeConfig()->getValue("trans_email/ident_" . $senderId . "/name");
            $senderEmail = $this->context->getScopeConfig()->getValue("trans_email/ident_" . $senderId . "/email");

            $this->send($templateName, $senderName, $senderEmail, $recipientEmail, $recipientName, $variables, $storeId);

            $sendQuoteRequestNotificationTo = $this->getConfig('general/send_quote_request_notification_to');
            if ($sendQuoteRequestNotificationTo) {
                $sendQuoteRequestNotificationTo = explode(",", $sendQuoteRequestNotificationTo);
                foreach ($sendQuoteRequestNotificationTo as $email) {
                    $this->send($templateName, $senderName, $senderEmail, $email, $recipientName, $variables, $storeId);
                }
            }
            return true;
        }
        return false;
    }

    public function sendNotificationQuoteReviewedEmail($mageQuote, $quote, $emailMessage = false)
    {
        $block          = $this->_layout->createBlock('Lof\RequestForQuote\Block\Quote\Items');
        $templateName   = $this->getConfig('email_templates/quote_reviewed');
        $storeScope     = \Magento\Store\Model\ScopeInterface::SCOPE_STORE;
        $senderId       = $this->getConfig('general/sender_email_identity');
        $sender_email   = $this->scopeConfig->getValue('trans_email/ident_' . $senderId . '/name', $storeScope);
        $recipientEmail = $mageQuote->getCustomerEmail();
        $recipientName  = '';

        if ($senderId) {
            $url = $this->_storeManager->getStore()->getUrl();
            $variables = [
                'increment_id'  => $quote->getIncrementId(),
                'created_at'    => $this->getCreatedAtFormatted($mageQuote->getCreatedAt(), $mageQuote->getstore(), \IntlDateFormatter::MEDIUM),
                'quote'         => $mageQuote,
                'purchase_link' => $url,
                'expired_at'    => $this->rfqHelper->formatDate($quote->getExpiry(), \IntlDateFormatter::SHORT)
            ];

            $storeId     = $this->_storeManager->getStore()->getId();
            $senderName  = $this->context->getScopeConfig()->getValue("trans_email/ident_" . $senderId . "/name");
            $senderEmail = $this->context->getScopeConfig()->getValue("trans_email/ident_" . $senderId . "/email");

            $this->send($templateName, $senderName, $senderEmail, $recipientEmail, $recipientName, $variables, $storeId);
            $sendQuoteRequestNotificationTo = $this->getConfig('general/send_quote_request_notification_to');
            if ($sendQuoteRequestNotificationTo) {
                $sendQuoteRequestNotificationTo = explode(",", $sendQuoteRequestNotificationTo);
                foreach ($sendQuoteRequestNotificationTo as $email) {
                    $this->send($templateName, $senderName, $senderEmail, $email, $recipientName, $variables, $storeId);
                }
            }
            return true;
        }
        return false;
    }

    public function sendNotificationQuoteExpiredEmail($mageQuote, $quote, $emailMessage = false)
    {
        $block          = $this->_layout->createBlock('Lof\RequestForQuote\Block\Quote\Items');
        $templateName   = $this->getConfig('email_templates/quote_expired');
        $storeScope     = \Magento\Store\Model\ScopeInterface::SCOPE_STORE;
        $senderId       = $this->getConfig('general/sender_email_identity');
        $sender_email   = $this->scopeConfig->getValue('trans_email/ident_' . $senderId . '/name', $storeScope);
        $recipientEmail = $mageQuote->getCustomerEmail();
        $recipientName  = '';

        if ($senderId) {
            $url = $this->_storeManager->getStore()->getUrl();
            $variables      = [
                'increment_id'  => $quote->getIncrementId(),
                'created_at'    => $this->getCreatedAtFormatted($mageQuote->getCreatedAt(), $mageQuote->getstore(), \IntlDateFormatter::MEDIUM),
                'quote'         => $mageQuote,
                'purchase_link' => $url,
                'expired_at'    => $this->rfqHelper->formatDate($quote->getExpiry(), \IntlDateFormatter::SHORT)
            ];

            $storeId     = $this->_storeManager->getStore()->getId();
            $senderName  = $this->context->getScopeConfig()->getValue("trans_email/ident_" . $senderId . "/name");
            $senderEmail = $this->context->getScopeConfig()->getValue("trans_email/ident_" . $senderId . "/email");
            $this->send($templateName, $senderName, $senderEmail, $recipientEmail, $recipientName, $variables, $storeId);
            $sendQuoteRequestNotificationTo = $this->getConfig('general/send_quote_request_notification_to');
            if ($sendQuoteRequestNotificationTo) {
                $sendQuoteRequestNotificationTo = explode(",", $sendQuoteRequestNotificationTo);
                foreach ($sendQuoteRequestNotificationTo as $email) {
                    $this->send($templateName, $senderName, $senderEmail, $email, $recipientName, $variables, $storeId);
                }
            }
            return true;
        }
        return false;
    }

    public function sendNotificationChangeExpiredEmail($mageQuote, $quote, $oldDate, $newDate)
    {
        $block          = $this->_layout->createBlock('Lof\RequestForQuote\Block\Quote\Items');
        $templateName   = $this->getConfig('email_templates/quote_change_expired_date');
        $storeScope     = \Magento\Store\Model\ScopeInterface::SCOPE_STORE;
        $senderId       = $this->getConfig('general/sender_email_identity');
        $sender_email   = $this->scopeConfig->getValue('trans_email/ident_' . $senderId . '/name', $storeScope);
        $recipientEmail = $mageQuote->getCustomerEmail();
        $recipientName  = '';

        if ($senderId) {
            $url = $this->_storeManager->getStore()->getUrl();
            $variables      = [
                'increment_id'  => $quote->getIncrementId(),
                'created_at'    => $this->getCreatedAtFormatted($mageQuote->getCreatedAt(), $mageQuote->getstore(), \IntlDateFormatter::MEDIUM),
                'quote'         => $mageQuote,
                'purchase_link' => $url,
                'old_date'      => $this->getCreatedAtFormatted($oldDate, $mageQuote->getstore(), \IntlDateFormatter::MEDIUM),
                'expired_at'    => $this->getCreatedAtFormatted($newDate, $mageQuote->getstore(), \IntlDateFormatter::MEDIUM)
            ];

            $storeId     = $this->_storeManager->getStore()->getId();
            $senderName  = $this->context->getScopeConfig()->getValue("trans_email/ident_" . $senderId . "/name");
            $senderEmail = $this->context->getScopeConfig()->getValue("trans_email/ident_" . $senderId . "/email");

            $this->send($templateName, $senderName, $senderEmail, $recipientEmail, $recipientName, $variables, $storeId);
            $sendQuoteRequestNotificationTo = $this->getConfig('general/send_quote_request_notification_to');
            if ($sendQuoteRequestNotificationTo) {
                $sendQuoteRequestNotificationTo = explode(",", $sendQuoteRequestNotificationTo);
                foreach ($sendQuoteRequestNotificationTo as $email) {
                    $this->send($templateName, $senderName, $senderEmail, $email, $recipientName, $variables, $storeId);
                }
            }
            return true;
        }
        return false;
    }
}