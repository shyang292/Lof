<?php

namespace Lof\RequestForQuote\Plugin;

class PluginBefore
{
    protected $urlBuider;
    protected $_authorization;

    public function __construct(
        \Magento\Framework\UrlInterface $urlBuilder,
        \Magento\Framework\AuthorizationInterface $authorization
    ) {
        $this->urlBuilder = $urlBuilder;
        $this->_authorization = $authorization;
    }

    public function beforePushButtons(
        \Magento\Backend\Block\Widget\Button\Toolbar\Interceptor $subject,
        \Magento\Framework\View\Element\AbstractBlock $context,
        \Magento\Backend\Block\Widget\Button\ButtonList $buttonList
    )
    {
        $this->_request = $context->getRequest();
        $orderId = $this->_request->getParam('entity_id');
        $params = array('entity_id'=>$orderId);
        $url = $this->urlBuilder->getUrl("quotation/quote/printquote",$params);
        $message = 'Are you sure you want to do this?';
        if($this->_request->getFullActionName() == 'quotation_quote_edit'){
            $buttonList->add(
                'printquotebutton',
                [
                    'label' => __('Print'),
                    'onclick' =>  "confirmSetLocation('{$message}', '{$url}')",
                    'class' => 'go'
                ],
                -1
            );
        }
    }

    /**
     * Check permission for passed action
     *
     * @param string $resourceId
     * @return bool
     */
    public function _isAllowedAction($resourceId)
    {
        return $this->_authorization->isAllowed($resourceId);
    }

}