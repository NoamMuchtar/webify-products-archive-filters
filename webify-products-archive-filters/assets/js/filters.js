(function ($) {
    'use strict';

    var WPAF = {
        priceDebounceTimer: null,
        ready: false,

        init: function () {
            this.$wrapper = $('.wpaf-filters-wrapper');
            if (!this.$wrapper.length) return;

            this.$accordion = this.$wrapper.find('.wpaf-accordion');
            this.$mobileTrigger = $('.wpaf-mobile-trigger');
            this.$overlay = $('.wpaf-sidebar-overlay');
            this.$closeBtn = this.$wrapper.find('.wpaf-sidebar-close');

            this.bindEvents();

            // Mark ready after a tick so page-load checkbox states don't trigger navigation
            var self = this;
            setTimeout(function () { self.ready = true; }, 100);
        },

        bindEvents: function () {
            var self = this;

            // Accordion
            this.$accordion.on('click', '.wpaf-accordion-header', function (e) {
                var $item = $(e.currentTarget).closest('.wpaf-accordion-item');
                var isActive = $item.hasClass('wpaf-active');
                $item.toggleClass('wpaf-active', !isActive);
                $(e.currentTarget).attr('aria-expanded', !isActive);
                $item.find('.wpaf-accordion-body')[isActive ? 'slideUp' : 'slideDown'](280);
            });

            // Mobile sidebar
            this.$mobileTrigger.on('click', function () { self.openSidebar(); });
            this.$closeBtn.on('click', function () { self.closeSidebar(); });
            this.$overlay.on('click', function () { self.closeSidebar(); });
            $(document).on('keydown', function (e) {
                if (e.key === 'Escape') self.closeSidebar();
            });

            // Filter changes → navigate
            this.$wrapper.on('change', 'input[type="checkbox"]', function () {
                if (self.ready) self.navigate();
            });

            this.$wrapper.on('change', '.wpaf-price-input', function () {
                if (self.ready) self.navigate();
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

        buildUrl: function () {
            var params = new URLSearchParams();

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
            return qs ? url + '?' + qs : url;
        },

        navigate: function () {
            var newUrl = this.buildUrl();
            var currentUrl = window.location.pathname + window.location.search;

            // Only navigate if URL actually changed
            if (newUrl !== currentUrl) {
                window.location.href = newUrl;
            }
        }
    };

    $(document).ready(function () {
        WPAF.init();
    });

})(jQuery);
