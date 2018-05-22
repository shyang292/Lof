<?php /**
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
 * @copyright  Copyright (c) 2018 Landofcoder (http://www.landofcoder.com/)
 * @license    http://www.landofcoder.com/LICENSE-1.0.html
 */

namespace Lof\RequestForQuote\Controller\Quote;

use Magento\Customer\Controller\AccountInterface;
use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\View\Result\PageFactory;

class State extends \Magento\Framework\App\Action\Action
{
    /**
     * @var \Magento\Framework\Registry
     */
    protected $_coreRegistry;

    /**
     * @var \Lof\Formbuilder\Model\Model
     */
    protected $_model;

    /**
     * @var \Magento\Framework\Escaper
     */
    protected $_escaper;
     /**
     * @var \Magento\Directory\Helper\Data 
     */
    protected $_helper;

    protected $_quoteHelper;


    public function __construct(
        Context $context,
        \Magento\Framework\View\Result\PageFactory $resultPageFactory,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Escaper $escaper,
        \Magento\Directory\Helper\Data $helper,
        \Lof\RequestForQuote\Helper\Data $helperData
    ){
        $this->resultPageFactory = $resultPageFactory;
        $this->_coreRegistry     = $registry;
        $this->_escaper          = $escaper;
        $this->_helper           = $helper;
        $this->_quoteHelper      = $helperData;
        parent::__construct($context);
    }

    public function execute()
    {
        header('Content-Type: text/javascript');
        $post = $this->getRequest()->getPost();
        $field_name = $post['field_name'];
        $scopeHelper = $this->_helper;
        $_regionsData = $scopeHelper->getRegionData();
        $countries = $scopeHelper->getCountryCollection()->toOptionArray(false);
        $code = isset($post['country_id'])?$post['country_id']:'';
        $output = [];
        $require_region           = $this->_quoteHelper->getConfig("quote_sidebar/require_region");
        $require_field_str      = ' data-validate="{required:true}" aria-required="true"';
        //$output[$code]['name'] = $post['country_name'];
        $data_return ='';

        $data_return .='<label class="label" for="'.$field_name.'">';
        $data_return .='<span>'.__('State/Province').'</span>';
        $data_return .='        </label>';
        $data_return .='<div class="control">';


        if (isset($code)) {
            if (array_key_exists($code, $_regionsData) && isset($code)) {
                foreach ($_regionsData[$code] as $key => $region) {
                    $output[$code]['regions'][$key]['code'] = $region['code'];
                    $output[$code]['regions'][$key]['name'] = $region['name'];
                }

                if ($output) {
                    $data_return .='<select id="'.$field_name.'"class="required-entry" name="'.$field_name.'" '.($require_region?$require_field_str:'').'>';
                    $data_return .='<option value="">-- '.__("Please Select").' --</option>';
                    foreach ($output[$code]['regions'] as $key => $_output) {
                        $data_return .='<option value="'.$_output['name'].'">'.$_output['name'].'</option>';
                    }
                    $data_return .='<select>';
                }
            } else {
                $data_return .='<input class="input-text validate-state" type="text" id="'.$field_name.'" name="'.$field_name.'" '.($require_region?$require_field_str:'').'/>';
            }
        } else {
             $data_return .='<input class="input-text validate-state" type="text" id="'.$field_name.'" name="'.$field_name.'"  '.($require_region?$require_field_str:'').'/>';
        }
        $data_return .='</div>';
        echo  json_encode($data_return);

        exit;
    }
}
