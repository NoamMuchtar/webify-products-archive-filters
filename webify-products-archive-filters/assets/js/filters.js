(function ($) {
    'use strict';

    var WPAF = {
        isLoading: false,
        priceDebounceTimer: null,

        init: function () {
            this.cacheDOM();
            if (!this.$wrapper.length) return;
            this.bindEvents();
            this.restoreFromURL();
            this.updateActiveCountBadge();
        },

        cacheDOM: function () {
            this.$wrapper = $('.wpaf-filters-wrapper');
            this.$accordion = this.$wrapper.find('.wpaf-accordion');
            this.$mobileTrigger = $('.wpaf-mobile-trigger');
            this.$overlay = $('.wpaf-sidebar-overlay');
            this.$closeBtn = this.$wrapper.find('.wpaf-sidebar-close');
            this.$productsContainer = null;

            // Find the products container (Elementor archive products widget)
            var $products = $('.products');
            if ($products.length) {
                this.$productsContainer = $products.first().parent();
            }
        },

        bindEvents: function () {
            var self = this;

            // Accordion toggle
            this.$accordion.on('click', '.wpaf-accordion-header', this.toggleAccordion.bind(this));

            // Mobile sidebar
            this.$mobileTrigger.on('click', this.openSidebar.bind(this));
            this.$closeBtn.on('click', this.closeSidebar.bind(this));
            this.$overlay.on('click', this.closeSidebar.bind(this));

            // Handle pagination clicks on AJAX-loaded pagination
            $(document).on('click', '.woocommerce-pagination a', this.handlePagination.bind(this));

            // Close sidebar on ESC
            $(document).on('keydown', function (e) {
                if (e.key === 'Escape') {
                    self.closeSidebar();
                }
            });

            // Auto-apply on checkbox change
            this.$wrapper.on('change', 'input[type="checkbox"]', function () {
                self.updateActiveCountBadge();
                self.applyFilters(1);
            });

            // Auto-apply on price change (debounced)
            this.$wrapper.on('input', '.wpaf-price-input', function () {
                self.updateActiveCountBadge();
                clearTimeout(self.priceDebounceTimer);
                self.priceDebounceTimer = setTimeout(function () {
                    self.applyFilters(1);
                }, 600);
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
         * Count the number of active filters and update the badge on the mobile trigger.
         */
        updateActiveCountBadge: function () {
            var count = 0;

            // Count checked checkboxes
            this.$wrapper.find('input[type="checkbox"]:checked').each(function () {
                count++;
            });

            // Count filled price fields
            var min = this.$wrapper.find('input[name="min_price"]').val();
            var max = this.$wrapper.find('input[name="max_price"]').val();
            if (min) count++;
            if (max) count++;

            // Update badge
            var $badge = this.$mobileTrigger.find('.wpaf-active-count');
            if (count > 0) {
                if ($badge.length) {
                    $badge.text(count);
                } else {
                    this.$mobileTrigger.append('<span class="wpaf-active-count">' + count + '</span>');
                }
            } else {
                $badge.remove();
            }
        },

        collectFilters: function () {
            var data = {
                filters: {}
            };

            // Price
            var minPrice = this.$wrapper.find('input[name="min_price"]').val();
            var maxPrice = this.$wrapper.find('input[name="max_price"]').val();
            if (minPrice) data.min_price = minPrice;
            if (maxPrice) data.max_price = maxPrice;

            // Checkbox filters (colors, brands & attributes)
            this.$wrapper.find('.wpaf-checkbox-filter').each(function () {
                var taxonomy = $(this).data('taxonomy');
                var selected = [];
                $(this).find('input[type="checkbox"]:checked').each(function () {
                    selected.push($(this).val());
                });
                if (selected.length) {
                    data.filters[taxonomy] = selected;
                }
            });

            return data;
        },

        applyFilters: function (paged) {
            if (this.isLoading) return;

            var filterData = this.collectFilters();
            var pageNum = typeof paged === 'number' ? paged : 1;

            this.isLoading = true;
            this.showLoader();
            this.updateURL(filterData, pageNum);

            var ajaxData = {
                action: 'wpaf_filter_products',
                nonce: wpafData.nonce,
                paged: pageNum,
                min_price: filterData.min_price || '',
                max_price: filterData.max_price || '',
                filters: filterData.filters || {},
                term_id: this.$wrapper.data('term-id') || 0,
                taxonomy: this.$wrapper.data('taxonomy') || '',
            };

            var self = this;

            $.ajax({
                url: wpafData.ajaxUrl,
                type: 'POST',
                data: ajaxData,
                success: function (response) {
                    if (response.success && self.$productsContainer) {
                        // Fade out old content
                        self.$productsContainer.css('opacity', '0.3');

                        setTimeout(function () {
                            self.$productsContainer.html(response.data.html);

                            // Fade in new content
                            self.$productsContainer.css({
                                'opacity': '0',
                                'transition': 'opacity 0.3s ease'
                            });

                            // Trigger reflow
                            self.$productsContainer[0].offsetHeight;
                            self.$productsContainer.css('opacity', '1');

                            // Update pagination
                            var $existingPagination = $('.woocommerce-pagination');
                            if ($existingPagination.length) {
                                $existingPagination.replaceWith(response.data.pagination);
                            } else if (response.data.pagination) {
                                self.$productsContainer.after(response.data.pagination);
                            }

                            // Update product count if visible
                            var $resultCount = $('.woocommerce-result-count');
                            if ($resultCount.length && response.data.found_posts !== undefined) {
                                $resultCount.text(response.data.found_posts + ' ' + (response.data.found_posts === 1 ? 'מוצר' : 'מוצרים'));
                            }

                            // Scroll to products
                            $('html, body').animate({
                                scrollTop: self.$productsContainer.offset().top - 100
                            }, 350);
                        }, 150);
                    }
                },
                error: function () {
                    if (self.$productsContainer) {
                        self.$productsContainer.css('opacity', '1');
                    }
                },
                complete: function () {
                    self.isLoading = false;
                    self.hideLoader();
                    self.closeSidebar();
                    self.updateActiveCountBadge();
                }
            });
        },

        handlePagination: function (e) {
            e.preventDefault();
            var href = $(e.currentTarget).attr('href');
            var page = 1;

            var pageMatch = href.match(/\/page\/(\d+)/);
            if (pageMatch) {
                page = parseInt(pageMatch[1], 10);
            } else {
                pageMatch = href.match(/paged[=\/](\d+)/);
                if (pageMatch) {
                    page = parseInt(pageMatch[1], 10);
                }
            }

            this.applyFilters(page);
        },

        updateURL: function (filterData, paged) {
            if (!window.history || !window.history.pushState) return;

            var params = new URLSearchParams();

            if (filterData.min_price) params.set('min_price', filterData.min_price);
            if (filterData.max_price) params.set('max_price', filterData.max_price);

            if (filterData.filters) {
                for (var taxonomy in filterData.filters) {
                    if (filterData.filters.hasOwnProperty(taxonomy)) {
                        params.set('filter_' + taxonomy, filterData.filters[taxonomy].join(','));
                    }
                }
            }

            if (paged && paged > 1) params.set('paged', paged);

            var newUrl = window.location.pathname;
            var queryString = params.toString();
            if (queryString) newUrl += '?' + queryString;

            window.history.pushState({ wpaf: true }, '', newUrl);
        },

        restoreFromURL: function () {
            var params = new URLSearchParams(window.location.search);

            // Restore price
            if (params.has('min_price')) {
                this.$wrapper.find('input[name="min_price"]').val(params.get('min_price'));
            }
            if (params.has('max_price')) {
                this.$wrapper.find('input[name="max_price"]').val(params.get('max_price'));
            }

            // Restore taxonomy filters from URL
            var self = this;
            params.forEach(function (value, key) {
                if (key.indexOf('filter_') === 0) {
                    var taxonomy = key.replace('filter_', '');
                    var slugs = value.split(',');

                    slugs.forEach(function (slug) {
                        var $checkInput = self.$wrapper.find('.wpaf-checkbox-filter[data-taxonomy="' + taxonomy + '"] input[value="' + slug + '"]');
                        if ($checkInput.length) {
                            $checkInput.prop('checked', true);
                        }
                    });
                }
            });
        },

        showLoader: function () {
            if (!this.$productsContainer) return;

            if (!this.$productsContainer.find('.wpaf-loader-overlay').length) {
                this.$productsContainer.css('position', 'relative').append(
                    '<div class="wpaf-loader-overlay"><div class="wpaf-spinner"></div></div>'
                );
            }

            // Trigger reflow before adding class
            this.$productsContainer.find('.wpaf-loader-overlay')[0].offsetHeight;
            this.$productsContainer.find('.wpaf-loader-overlay').addClass('wpaf-loading');
        },

        hideLoader: function () {
            if (!this.$productsContainer) return;
            var $overlay = this.$productsContainer.find('.wpaf-loader-overlay');
            $overlay.removeClass('wpaf-loading');
            setTimeout(function () {
                $overlay.remove();
            }, 300);
        }
    };

    $(document).ready(function () {
        WPAF.init();
    });

    // Handle browser back/forward navigation
    $(window).on('popstate', function (e) {
        if (e.originalEvent.state && e.originalEvent.state.wpaf) {
            window.location.reload();
        }
    });

})(jQuery);
