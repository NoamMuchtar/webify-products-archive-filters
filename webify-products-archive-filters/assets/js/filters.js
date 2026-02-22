(function ($) {
    'use strict';

    var WPAF = {
        navigating: false,

        init: function () {
            this.$wrapper = $('.wpaf-filters-wrapper');
            if (!this.$wrapper.length) return;

            this.$accordion = this.$wrapper.find('.wpaf-accordion');
            this.$mobileTrigger = $('.wpaf-mobile-trigger');
            this.$overlay = $('.wpaf-sidebar-overlay');
            this.$closeBtn = this.$wrapper.find('.wpaf-sidebar-close');

            this.bindEvents();
        },

        bindEvents: function () {
            var self = this;

            // Accordion
            this.$accordion.on('click', '.wpaf-accordion-header', function (e) {
                var $item = $(e.currentTarget).closest('.wpaf-accordion-item');
                var isActive = $item.hasClass('wpaf-active');
                $item.toggleClass('wpaf-active', !isActive);
                $(e.currentTarget).attr('aria-expanded', String(!isActive));
                $item.find('.wpaf-accordion-body')[isActive ? 'slideUp' : 'slideDown'](280);
            });

            // Mobile sidebar
            this.$mobileTrigger.on('click', function () { self.openSidebar(); });
            this.$closeBtn.on('click', function () { self.closeSidebar(); });
            this.$overlay.on('click', function () { self.closeSidebar(); });
            $(document).on('keydown', function (e) {
                if (e.key === 'Escape') self.closeSidebar();
            });

            // Checkbox change → navigate (one-shot guard prevents infinite loop)
            this.$wrapper.on('change', 'input[type="checkbox"]', function () {
                self.navigate();
            });

            // Price: navigate on Enter key or blur
            this.$wrapper.on('change', '.wpaf-price-input', function () {
                self.navigate();
            });
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

        navigate: function () {
            // One-shot guard: only navigate once per page load
            if (this.navigating) return;
            this.navigating = true;

            // Start from current URL params to preserve WooCommerce params (orderby, etc)
            var params = new URLSearchParams(window.location.search);

            // Clear old filter params
            var toDelete = [];
            params.forEach(function (val, key) {
                if (key === 'min_price' || key === 'max_price' || key.indexOf('filter_') === 0) {
                    toDelete.push(key);
                }
            });
            for (var i = 0; i < toDelete.length; i++) {
                params.delete(toDelete[i]);
            }

            // Reset pagination when filters change
            params.delete('paged');
            params.delete('product-page');

            // Collect current filter values
            var minPrice = this.$wrapper.find('input[name="min_price"]').val();
            var maxPrice = this.$wrapper.find('input[name="max_price"]').val();
            if (minPrice) params.set('min_price', minPrice);
            if (maxPrice) params.set('max_price', maxPrice);

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

            var url = window.location.pathname;
            var qs = params.toString();
            if (qs) url += '?' + qs;

            window.location.href = url;
        }
    };

    $(document).ready(function () {
        WPAF.init();
    });

})(jQuery);
