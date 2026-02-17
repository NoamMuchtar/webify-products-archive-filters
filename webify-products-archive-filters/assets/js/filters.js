(function ($) {
    'use strict';

    var WPAF = {
        isLoading: false,

        init: function () {
            this.cacheDOM();
            this.bindEvents();
            this.restoreFromURL();
        },

        cacheDOM: function () {
            this.$wrapper = $('.wpaf-filters-wrapper');
            this.$accordion = this.$wrapper.find('.wpaf-accordion');
            this.$applyBtn = this.$wrapper.find('.wpaf-apply-filters');
            this.$clearBtn = this.$wrapper.find('.wpaf-clear-filters');
            this.$mobileTrigger = $('.wpaf-mobile-trigger');
            this.$overlay = $('.wpaf-sidebar-overlay');
            this.$closeBtn = this.$wrapper.find('.wpaf-sidebar-close');
            this.$productsContainer = null;
            this.$paginationContainer = null;

            // Find the products container (Elementor archive products widget)
            var $products = $('.products');
            if ($products.length) {
                this.$productsContainer = $products.first().parent();
            }
        },

        bindEvents: function () {
            // Accordion toggle
            this.$accordion.on('click', '.wpaf-accordion-header', this.toggleAccordion.bind(this));

            // Apply filters
            this.$applyBtn.on('click', this.applyFilters.bind(this));

            // Clear filters
            this.$clearBtn.on('click', this.clearFilters.bind(this));

            // Mobile sidebar
            this.$mobileTrigger.on('click', this.openSidebar.bind(this));
            this.$closeBtn.on('click', this.closeSidebar.bind(this));
            this.$overlay.on('click', this.closeSidebar.bind(this));

            // Color swatch toggle
            this.$wrapper.on('click', '.wpaf-color-swatch', this.toggleColorSwatch);

            // Handle pagination clicks on AJAX-loaded pagination
            $(document).on('click', '.woocommerce-pagination a', this.handlePagination.bind(this));

            // Close sidebar on ESC
            $(document).on('keydown', function (e) {
                if (e.key === 'Escape') {
                    WPAF.closeSidebar();
                }
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
                $body.slideUp(250);
            } else {
                $item.addClass('wpaf-active');
                $header.attr('aria-expanded', 'true');
                $body.slideDown(250);
            }
        },

        toggleColorSwatch: function (e) {
            e.preventDefault();
            var $swatch = $(this);
            var $checkbox = $swatch.find('input[type="checkbox"]');
            var isChecked = $checkbox.prop('checked');
            $checkbox.prop('checked', !isChecked);
            $swatch.toggleClass('wpaf-selected');
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

        collectFilters: function () {
            var data = {
                filters: {}
            };

            // Price
            var minPrice = this.$wrapper.find('input[name="min_price"]').val();
            var maxPrice = this.$wrapper.find('input[name="max_price"]').val();
            if (minPrice) data.min_price = minPrice;
            if (maxPrice) data.max_price = maxPrice;

            // Color filter
            this.$wrapper.find('.wpaf-color-filter').each(function () {
                var taxonomy = $(this).data('taxonomy');
                var selected = [];
                $(this).find('input[type="checkbox"]:checked').each(function () {
                    selected.push($(this).val());
                });
                if (selected.length) {
                    data.filters[taxonomy] = selected;
                }
            });

            // Checkbox filters (brands & attributes)
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

            $.ajax({
                url: wpafData.ajaxUrl,
                type: 'POST',
                data: ajaxData,
                success: function (response) {
                    if (response.success && WPAF.$productsContainer) {
                        WPAF.$productsContainer.html(response.data.html);

                        // Update pagination
                        var $existingPagination = $('.woocommerce-pagination');
                        if ($existingPagination.length) {
                            $existingPagination.replaceWith(response.data.pagination);
                        } else if (response.data.pagination) {
                            WPAF.$productsContainer.after(response.data.pagination);
                        }

                        // Update product count if visible
                        var $resultCount = $('.woocommerce-result-count');
                        if ($resultCount.length) {
                            $resultCount.text(response.data.found_posts + ' מוצרים');
                        }

                        // Scroll to products
                        $('html, body').animate({
                            scrollTop: WPAF.$productsContainer.offset().top - 100
                        }, 300);
                    }
                },
                error: function () {
                    console.error('WPAF: Filter request failed.');
                },
                complete: function () {
                    WPAF.isLoading = false;
                    WPAF.hideLoader();
                    WPAF.closeSidebar();
                }
            });
        },

        clearFilters: function () {
            // Clear price inputs
            this.$wrapper.find('input[name="min_price"], input[name="max_price"]').val('');

            // Uncheck all checkboxes
            this.$wrapper.find('input[type="checkbox"]').prop('checked', false);

            // Remove selected state from swatches
            this.$wrapper.find('.wpaf-color-swatch').removeClass('wpaf-selected');

            // Apply cleared filters
            this.applyFilters(1);
        },

        handlePagination: function (e) {
            e.preventDefault();
            var href = $(e.currentTarget).attr('href');
            var pageMatch = href.match(/paged[=\/](\d+)/);
            var page = pageMatch ? parseInt(pageMatch[1], 10) : 1;

            // Also check for /page/N/ format
            if (!pageMatch) {
                pageMatch = href.match(/\/page\/(\d+)/);
                page = pageMatch ? parseInt(pageMatch[1], 10) : 1;
            }

            this.applyFilters(page);
        },

        updateURL: function (filterData, paged) {
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

            window.history.pushState({}, '', newUrl);
        },

        restoreFromURL: function () {
            var params = new URLSearchParams(window.location.search);
            var hasFilters = false;

            // Restore price
            if (params.has('min_price')) {
                this.$wrapper.find('input[name="min_price"]').val(params.get('min_price'));
                hasFilters = true;
            }
            if (params.has('max_price')) {
                this.$wrapper.find('input[name="max_price"]').val(params.get('max_price'));
                hasFilters = true;
            }

            // Restore taxonomy filters from URL
            params.forEach(function (value, key) {
                if (key.indexOf('filter_') === 0) {
                    var taxonomy = key.replace('filter_', '');
                    var slugs = value.split(',');

                    slugs.forEach(function (slug) {
                        // Color swatches
                        var $colorInput = WPAF.$wrapper.find('.wpaf-color-filter[data-taxonomy="' + taxonomy + '"] input[value="' + slug + '"]');
                        if ($colorInput.length) {
                            $colorInput.prop('checked', true);
                            $colorInput.closest('.wpaf-color-swatch').addClass('wpaf-selected');
                        }

                        // Checkbox filters
                        var $checkInput = WPAF.$wrapper.find('.wpaf-checkbox-filter[data-taxonomy="' + taxonomy + '"] input[value="' + slug + '"]');
                        if ($checkInput.length) {
                            $checkInput.prop('checked', true);
                        }
                    });

                    hasFilters = true;
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
            this.$productsContainer.find('.wpaf-loader-overlay').addClass('wpaf-loading');
        },

        hideLoader: function () {
            if (!this.$productsContainer) return;
            this.$productsContainer.find('.wpaf-loader-overlay').remove();
        }
    };

    $(document).ready(function () {
        WPAF.init();
    });

})(jQuery);
