/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
define([
    'uiComponent',
    'Magento_Customer/js/customer-data',
    'jquery',
    'ko',
    'underscore',
    'sidebar'
], function (Component, customerData, $, ko, _) {
    'use strict';

    var sidebarInitialized = false,
        addToQuoteCalls = 0,
        miniQuote;

    miniQuote = $('[data-block=\'miniquote\']');
    miniQuote.on('dropdowndialogopen', function () {
        initQuoteSidebar();
    });

    /**
     * @return {Boolean}
     */
    function initQuoteSidebar() {
        if (miniQuote.data('mageSidebar')) {
            miniQuote.sidebar('update');
        }

        if (!$('[data-role=product-item]').length) {
            return false;
        }
        miniQuote.trigger('contentUpdated');

        if (sidebarInitialized) {
            return false;
        }
        sidebarInitialized = true;

        miniQuote.sidebar({
            'targetElement': 'div.block.block-miniquote',
            'url': {
                'checkout': window.quotation.submitQuoteUrl,
                'update': window.quotation.updateItemQtyUrl,
                'remove': window.quotation.removeItemUrl,
                'loginUrl': window.quotation.customerLoginUrl,
                'isRedirectRequired': window.quotation.isRedirectRequired
            },
            'button': {
                'checkout': '#top-quote-btn-sumit',
                'remove': '#mini-quote a.action.delete',
                'close': '#btn-miniquote-close'
            },
            'showquote': {
                'parent': 'span.counter',
                'qty': 'span.counter-number',
                'label': 'span.counter-label'
            },
            'minicart': {
                'list': '#mini-quote',
                'content': '#miniquote-content-wrapper',
                'qty': 'div.items-total',
                'subtotal': 'div.subtotal span.price',
                'maxItemsVisible': window.quotation.miniquoteMaxItemsVisible
            },
            'miniquote': {
                'list': '#mini-quote',
                'content': '#miniquote-content-wrapper',
                'qty': 'div.items-total',
                'subtotal': 'div.subtotal span.price',
                'maxItemsVisible': window.quotation.miniquoteMaxItemsVisible
            },
            'item': {
                'qty': ':input.quote-item-qty',
                'button': ':button.update-quote-item'
            },
            'confirmMessage': $.mage.__(
                'Are you sure you would like to remove this item from your quote list?'
            )
        });
    }

    return Component.extend({
        submitQuoteUrl: window.quotation.submitQuoteUrl,
        quote: {},

        /**
         * @override
         */
        initialize: function () {
            var self = this,
                quoteData = customerData.get('quote');

            this.update(quoteData());
            quoteData.subscribe(function (updatedQuote) {
                addToQuoteCalls--;
                this.isLoading(addToQuoteCalls > 0);
                sidebarInitialized = false;
                this.update(updatedQuote);
                initQuoteSidebar();
            }, this);
            $('[data-block="miniquote"]').on('contentLoading', function (event) {
                addToQuoteCalls++;
                self.isLoading(true);
            });
            if (quoteData().website_id !== window.quotation.websiteId) {
                customerData.reload(['quote'], false);
            }

            return this._super();
        },
        isLoading: ko.observable(false),
        initSidebar: initQuoteSidebar,

        /**
         * @return {Boolean}
         */
        closeSidebar: function () {
            var miniquote = $('[data-block="miniquote"]');
            miniquote.on('click', '[data-action="close"]', function (event) {
                event.stopPropagation();
                miniquote.find('[data-role="dropdownDialog"]').dropdownDialog('close');
            });

            return true;
        },

        /**
         * @param {String} productType
         * @return {*|String}
         */
        getItemRenderer: function (productType) {
            return this.itemRenderer[productType] || 'defaultRenderer';
        },

        /**
         * Update mini shopping quote content.
         *
         * @param {Object} updatedQuote
         * @returns void
         */
        update: function (updatedQuote) {
            _.each(updatedQuote, function (value, key) {
                if (!this.quote.hasOwnProperty(key)) {
                    this.quote[key] = ko.observable();
                }
                this.quote[key](value);
            }, this);
        },

        /**
         * Get quote param by name.
         * @param {String} name
         * @returns {*}
         */
        getQuoteParam: function (name) {
            if (!_.isUndefined(name)) {
                if (!this.quote.hasOwnProperty(name)) {
                    this.quote[name] = ko.observable();
                }
            }

            return this.quote[name]();
        }
    });
});
