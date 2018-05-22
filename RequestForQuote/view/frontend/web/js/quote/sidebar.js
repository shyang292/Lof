/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
/*global define*/
define(
    [
        'jquery',
        'ko',
        'Magento_Checkout/js/view/shipping',
        'Magento_Checkout/js/model/quote'
    ],
    function (
        $,
        ko,
        Component,
        quote
    ) {
        'use strict';

        return Component.extend({
            defaults: {
                template: 'Lof_RequestForQuote/quote/sidebar',
            },

            visible: ko.observable(!quote.isVirtual()),

            /**
             * @return {exports}
             */
            initialize: function () {
                this._super();
                
                $('.submitquote').on('click', function() {
                    alert("abc");
                });
            }
        });
    }
);