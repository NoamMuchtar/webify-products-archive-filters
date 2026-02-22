(function ($) {
    'use strict';

    var WPAF = {
        priceDebounceTimer: null,

        init: function () {
            this.cacheDOM();
            if (!this.$wrapper.length) return;
            this.bindEvents();
        },

        cacheDOM: function () {
            this.$wrapper = $('.wpaf-filters-wrapper');
            this.$accordion = this.$wrapper.find('.wpaf-accordion');
            this.$mobileTrigger = $('.wpaf-mobile-trigger');
            this.$overlay = $('.wpaf-sidebar-overlay');
            this.$closeBtn = this.$wrapper.find('.wpaf-sidebar-close');
        },

        bindEvents: function () {
            var self = this;

            // Accordion toggle
            this.$accordion.on('click', '.wpaf-accordion-header', this.toggleAccordion.bind(this));

            // Mobile sidebar
            this.$mobileTrigger.on('click', this.openSidebar.bind(this));
            this.$closeBtn.on('click', this.closeSidebar.bind(this));
            this.$overlay.on('click', this.closeSidebar.bind(this));

            // Close sidebar on ESC
            $(document).on('keydown', function (e) {
                if (e.key === 'Escape') {
                    self.closeSidebar();
                }
            });

            // Auto-navigate on checkbox change
            this.$wrapper.on('change', 'input[type="checkbox"]', function () {
                self.navigate();
            });

            // Auto-navigate on price change (debounced)
            this.$wrapper.on('input', '.wpaf-price-input', function () {
                clearTimeout(self.priceDebounceTimer);
                self.priceDebounceTimer = setTimeout(function () {
                    self.navigate();
                }, 800);
            });
        },

        toggleAccordion: function (e) {
            var $header = $(e.currentTarget);
            var $item = $header.closest('.wpaf-accordion-item');
            var $body = $item.find('.wpaf-accordion-body');
            var isActive = $item.hasClass('wpaf-active');

            if (isActive) {
                $item.removeClass('wpaf-active');
                $header.attr('aria-expanded', 'false');
                $body.slideUp(280);
            } else {
                $item.addClass('wpaf-active');
                $header.attr('aria-expanded', 'true');
                $body.slideDown(280);
            }
        },

        openSidebar: function () {
            this.$wrapper.addClass('wpaf-sidebar-open');
            this.$overlay.addClass('wpaf-overlay-visible');
            $('body').addClass('wpaf-sidebar-active');
        },

        closeSidebar: function () {
            this.$wrapper.removeClass('wpaf-sidebar-open');
            this.$overlay.removeClass('wpaf-overlay-visible');
            $('body').removeClass('wpaf-sidebar-active');
        },

        /**
         * Collect all current filter selections and navigate to the filtered URL.
         */
        navigate: function () {
            var params = new URLSearchParams();

            // Price
            var minPrice = this.$wrapper.find('input[name="min_price"]').val();
            var maxPrice = this.$wrapper.find('input[name="max_price"]').val();
            if (minPrice) params.set('min_price', minPrice);
            if (maxPrice) params.set('max_price', maxPrice);

            // Checkbox filters (color, brand, attributes)
            this.$wrapper.find('.wpaf-checkbox-filter').each(function () {
                var taxonomy = $(this).data('taxonomy');
                var selected = [];
                $(this).find('input[type="checkbox"]:checked').each(function () {
                    selected.push($(this).val());
                });
                if (selected.length) {
                    params.set('filter_' + taxonomy, selected.join(','));
                }
            });

            var newUrl = window.location.pathname;
            var queryString = params.toString();
            if (queryString) newUrl += '?' + queryString;

            window.location.href = newUrl;
        }
    };

    $(document).ready(function () {
        WPAF.init();
    });

})(jQuery);
