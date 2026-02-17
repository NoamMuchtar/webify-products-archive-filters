<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class WPAF_Filters_Widget extends \Elementor\Widget_Base {

    public function get_name() {
        return 'wpaf_product_filters';
    }

    public function get_title() {
        return esc_html__( 'Product Filters', 'webify-products-archive-filters' );
    }

    public function get_icon() {
        return 'eicon-filter';
    }

    public function get_categories() {
        return [ 'webify' ];
    }

    public function get_keywords() {
        return [ 'filter', 'product', 'woocommerce', 'archive', 'price', 'attribute', 'color', 'brand' ];
    }

    protected function register_controls() {

        // === Content: General ===
        $this->start_controls_section( 'section_general', [
            'label' => esc_html__( 'General', 'webify-products-archive-filters' ),
            'tab'   => \Elementor\Controls_Manager::TAB_CONTENT,
        ] );

        $this->add_control( 'show_price_filter', [
            'label'        => esc_html__( 'Price Filter', 'webify-products-archive-filters' ),
            'type'         => \Elementor\Controls_Manager::SWITCHER,
            'default'      => 'yes',
            'label_on'     => esc_html__( 'Show', 'webify-products-archive-filters' ),
            'label_off'    => esc_html__( 'Hide', 'webify-products-archive-filters' ),
        ] );

        $this->add_control( 'price_filter_title', [
            'label'     => esc_html__( 'Price Filter Title', 'webify-products-archive-filters' ),
            'type'      => \Elementor\Controls_Manager::TEXT,
            'default'   => esc_html__( 'מחיר', 'webify-products-archive-filters' ),
            'condition' => [ 'show_price_filter' => 'yes' ],
        ] );

        $this->add_control( 'show_color_filter', [
            'label'        => esc_html__( 'Color Filter', 'webify-products-archive-filters' ),
            'type'         => \Elementor\Controls_Manager::SWITCHER,
            'default'      => 'yes',
            'label_on'     => esc_html__( 'Show', 'webify-products-archive-filters' ),
            'label_off'    => esc_html__( 'Hide', 'webify-products-archive-filters' ),
        ] );

        $this->add_control( 'color_filter_title', [
            'label'     => esc_html__( 'Color Filter Title', 'webify-products-archive-filters' ),
            'type'      => \Elementor\Controls_Manager::TEXT,
            'default'   => esc_html__( 'צבע', 'webify-products-archive-filters' ),
            'condition' => [ 'show_color_filter' => 'yes' ],
        ] );

        $this->add_control( 'color_attribute', [
            'label'       => esc_html__( 'Color Attribute Slug', 'webify-products-archive-filters' ),
            'type'        => \Elementor\Controls_Manager::TEXT,
            'default'     => 'pa_color',
            'description' => esc_html__( 'The taxonomy slug for the color attribute (e.g. pa_color).', 'webify-products-archive-filters' ),
            'condition'   => [ 'show_color_filter' => 'yes' ],
        ] );

        $this->add_control( 'show_brand_filter', [
            'label'        => esc_html__( 'Brand Filter', 'webify-products-archive-filters' ),
            'type'         => \Elementor\Controls_Manager::SWITCHER,
            'default'      => 'yes',
            'label_on'     => esc_html__( 'Show', 'webify-products-archive-filters' ),
            'label_off'    => esc_html__( 'Hide', 'webify-products-archive-filters' ),
        ] );

        $this->add_control( 'brand_filter_title', [
            'label'     => esc_html__( 'Brand Filter Title', 'webify-products-archive-filters' ),
            'type'      => \Elementor\Controls_Manager::TEXT,
            'default'   => esc_html__( 'מותג', 'webify-products-archive-filters' ),
            'condition' => [ 'show_brand_filter' => 'yes' ],
        ] );

        $this->add_control( 'show_attributes_filter', [
            'label'        => esc_html__( 'Attributes Filter', 'webify-products-archive-filters' ),
            'type'         => \Elementor\Controls_Manager::SWITCHER,
            'default'      => 'yes',
            'label_on'     => esc_html__( 'Show', 'webify-products-archive-filters' ),
            'label_off'    => esc_html__( 'Hide', 'webify-products-archive-filters' ),
        ] );

        $this->add_control( 'excluded_attributes', [
            'label'       => esc_html__( 'Exclude Attribute Slugs', 'webify-products-archive-filters' ),
            'type'        => \Elementor\Controls_Manager::TEXT,
            'description' => esc_html__( 'Comma-separated taxonomy slugs to exclude (the color attribute is automatically separated).', 'webify-products-archive-filters' ),
            'condition'   => [ 'show_attributes_filter' => 'yes' ],
        ] );

        $this->add_control( 'mobile_button_text', [
            'label'   => esc_html__( 'Mobile Button Text', 'webify-products-archive-filters' ),
            'type'    => \Elementor\Controls_Manager::TEXT,
            'default' => esc_html__( 'סינון מוצרים', 'webify-products-archive-filters' ),
        ] );

        $this->end_controls_section();

        // === Style: Accordion ===
        $this->start_controls_section( 'section_style_accordion', [
            'label' => esc_html__( 'Accordion', 'webify-products-archive-filters' ),
            'tab'   => \Elementor\Controls_Manager::TAB_STYLE,
        ] );

        $this->add_control( 'heading_color', [
            'label'     => esc_html__( 'Heading Color', 'webify-products-archive-filters' ),
            'type'      => \Elementor\Controls_Manager::COLOR,
            'default'   => '#333333',
            'selectors' => [
                '{{WRAPPER}} .wpaf-accordion-header' => 'color: {{VALUE}};',
            ],
        ] );

        $this->add_group_control( \Elementor\Group_Control_Typography::get_type(), [
            'name'     => 'heading_typography',
            'label'    => esc_html__( 'Heading Typography', 'webify-products-archive-filters' ),
            'selector' => '{{WRAPPER}} .wpaf-accordion-header',
        ] );

        $this->add_control( 'border_color', [
            'label'     => esc_html__( 'Border Color', 'webify-products-archive-filters' ),
            'type'      => \Elementor\Controls_Manager::COLOR,
            'default'   => '#e0e0e0',
            'selectors' => [
                '{{WRAPPER}} .wpaf-accordion-item' => 'border-color: {{VALUE}};',
            ],
        ] );

        $this->end_controls_section();

        // === Style: Mobile Button ===
        $this->start_controls_section( 'section_style_mobile', [
            'label' => esc_html__( 'Mobile Button', 'webify-products-archive-filters' ),
            'tab'   => \Elementor\Controls_Manager::TAB_STYLE,
        ] );

        $this->add_control( 'mobile_btn_bg', [
            'label'     => esc_html__( 'Background Color', 'webify-products-archive-filters' ),
            'type'      => \Elementor\Controls_Manager::COLOR,
            'default'   => '#333333',
            'selectors' => [
                '{{WRAPPER}} .wpaf-mobile-trigger' => 'background-color: {{VALUE}};',
            ],
        ] );

        $this->add_control( 'mobile_btn_color', [
            'label'     => esc_html__( 'Text Color', 'webify-products-archive-filters' ),
            'type'      => \Elementor\Controls_Manager::COLOR,
            'default'   => '#ffffff',
            'selectors' => [
                '{{WRAPPER}} .wpaf-mobile-trigger' => 'color: {{VALUE}};',
            ],
        ] );

        $this->add_control( 'mobile_btn_radius', [
            'label'      => esc_html__( 'Border Radius', 'webify-products-archive-filters' ),
            'type'       => \Elementor\Controls_Manager::DIMENSIONS,
            'size_units' => [ 'px', '%' ],
            'default'    => [
                'top'    => '8',
                'right'  => '8',
                'bottom' => '8',
                'left'   => '8',
                'unit'   => 'px',
            ],
            'selectors'  => [
                '{{WRAPPER}} .wpaf-mobile-trigger' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
            ],
        ] );

        $this->end_controls_section();

        // === Style: Price Inputs ===
        $this->start_controls_section( 'section_style_price', [
            'label' => esc_html__( 'Price Inputs', 'webify-products-archive-filters' ),
            'tab'   => \Elementor\Controls_Manager::TAB_STYLE,
        ] );

        $this->add_control( 'price_input_border_color', [
            'label'     => esc_html__( 'Input Border Color', 'webify-products-archive-filters' ),
            'type'      => \Elementor\Controls_Manager::COLOR,
            'default'   => '#cccccc',
            'selectors' => [
                '{{WRAPPER}} .wpaf-price-input' => 'border-color: {{VALUE}};',
            ],
        ] );

        $this->end_controls_section();

        // === Style: Apply Button ===
        $this->start_controls_section( 'section_style_apply_btn', [
            'label' => esc_html__( 'Apply Button', 'webify-products-archive-filters' ),
            'tab'   => \Elementor\Controls_Manager::TAB_STYLE,
        ] );

        $this->add_control( 'apply_btn_bg', [
            'label'     => esc_html__( 'Background Color', 'webify-products-archive-filters' ),
            'type'      => \Elementor\Controls_Manager::COLOR,
            'default'   => '#333333',
            'selectors' => [
                '{{WRAPPER}} .wpaf-apply-filters' => 'background-color: {{VALUE}};',
            ],
        ] );

        $this->add_control( 'apply_btn_color', [
            'label'     => esc_html__( 'Text Color', 'webify-products-archive-filters' ),
            'type'      => \Elementor\Controls_Manager::COLOR,
            'default'   => '#ffffff',
            'selectors' => [
                '{{WRAPPER}} .wpaf-apply-filters' => 'color: {{VALUE}};',
            ],
        ] );

        $this->end_controls_section();
    }

    protected function render() {
        $settings = $this->get_settings_for_display();

        $color_attr     = ! empty( $settings['color_attribute'] ) ? $settings['color_attribute'] : 'pa_color';
        $excluded_raw   = ! empty( $settings['excluded_attributes'] ) ? $settings['excluded_attributes'] : '';
        $excluded_slugs = array_filter( array_map( 'trim', explode( ',', $excluded_raw ) ) );

        // Always exclude the color attribute from the generic attributes section
        $excluded_slugs[] = $color_attr;

        $filters = [];

        // 1. Price filter
        if ( 'yes' === $settings['show_price_filter'] ) {
            $filters[] = [
                'type'  => 'price',
                'title' => $settings['price_filter_title'],
            ];
        }

        // 2. Color filter
        if ( 'yes' === $settings['show_color_filter'] ) {
            $terms = get_terms( [
                'taxonomy'   => $color_attr,
                'hide_empty' => true,
            ] );
            if ( ! is_wp_error( $terms ) && ! empty( $terms ) ) {
                $filters[] = [
                    'type'  => 'color',
                    'title' => $settings['color_filter_title'],
                    'slug'  => $color_attr,
                    'terms' => $terms,
                ];
            }
        }

        // 3. Brand filter
        if ( 'yes' === $settings['show_brand_filter'] ) {
            $brand_taxonomies = [ 'product_brand', 'pwb-brand', 'yith_product_brand' ];
            $brand_tax        = null;
            foreach ( $brand_taxonomies as $tax ) {
                if ( taxonomy_exists( $tax ) ) {
                    $brand_tax = $tax;
                    break;
                }
            }
            if ( $brand_tax ) {
                $terms = get_terms( [
                    'taxonomy'   => $brand_tax,
                    'hide_empty' => true,
                ] );
                if ( ! is_wp_error( $terms ) && ! empty( $terms ) ) {
                    $filters[] = [
                        'type'  => 'brand',
                        'title' => $settings['brand_filter_title'],
                        'slug'  => $brand_tax,
                        'terms' => $terms,
                    ];
                }
            }
        }

        // 4. Product attributes
        if ( 'yes' === $settings['show_attributes_filter'] ) {
            $attribute_taxonomies = wc_get_attribute_taxonomies();
            foreach ( $attribute_taxonomies as $attribute ) {
                $taxonomy = wc_attribute_taxonomy_name( $attribute->attribute_name );
                if ( in_array( $taxonomy, $excluded_slugs, true ) ) {
                    continue;
                }
                $terms = get_terms( [
                    'taxonomy'   => $taxonomy,
                    'hide_empty' => true,
                ] );
                if ( ! is_wp_error( $terms ) && ! empty( $terms ) ) {
                    $filters[] = [
                        'type'  => 'attribute',
                        'title' => $attribute->attribute_label,
                        'slug'  => $taxonomy,
                        'terms' => $terms,
                    ];
                }
            }
        }

        if ( empty( $filters ) ) {
            if ( \Elementor\Plugin::$instance->editor->is_edit_mode() ) {
                echo '<p style="padding:20px;text-align:center;color:#999;">' . esc_html__( 'No filters available. Make sure WooCommerce attributes, colors, and brands are configured.', 'webify-products-archive-filters' ) . '</p>';
            }
            return;
        }

        // Current query params for pre-selecting active filters
        $current_min   = isset( $_GET['min_price'] ) ? floatval( $_GET['min_price'] ) : '';
        $current_max   = isset( $_GET['max_price'] ) ? floatval( $_GET['max_price'] ) : '';

        // Determine the current queried object for the AJAX request
        $queried = get_queried_object();
        $current_term_id  = 0;
        $current_taxonomy = '';
        if ( $queried instanceof WP_Term ) {
            $current_term_id  = $queried->term_id;
            $current_taxonomy = $queried->taxonomy;
        }
        ?>

        <!-- Mobile trigger button -->
        <button class="wpaf-mobile-trigger" aria-label="<?php esc_attr_e( 'Open filters', 'webify-products-archive-filters' ); ?>">
            <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="4" y1="21" x2="4" y2="14"/><line x1="4" y1="10" x2="4" y2="3"/><line x1="12" y1="21" x2="12" y2="12"/><line x1="12" y1="8" x2="12" y2="3"/><line x1="20" y1="21" x2="20" y2="16"/><line x1="20" y1="12" x2="20" y2="3"/><line x1="1" y1="14" x2="7" y2="14"/><line x1="9" y1="8" x2="15" y2="8"/><line x1="17" y1="16" x2="23" y2="16"/></svg>
            <span><?php echo esc_html( $settings['mobile_button_text'] ); ?></span>
        </button>

        <!-- Overlay for mobile sidebar -->
        <div class="wpaf-sidebar-overlay"></div>

        <!-- Filters container -->
        <div class="wpaf-filters-wrapper" data-term-id="<?php echo esc_attr( $current_term_id ); ?>" data-taxonomy="<?php echo esc_attr( $current_taxonomy ); ?>">

            <!-- Mobile sidebar close button -->
            <div class="wpaf-sidebar-header">
                <span class="wpaf-sidebar-title"><?php echo esc_html( $settings['mobile_button_text'] ); ?></span>
                <button class="wpaf-sidebar-close" aria-label="<?php esc_attr_e( 'Close filters', 'webify-products-archive-filters' ); ?>">
                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
                </button>
            </div>

            <div class="wpaf-accordion">
                <?php foreach ( $filters as $index => $filter ) : ?>
                    <div class="wpaf-accordion-item<?php echo 0 === $index ? ' wpaf-active' : ''; ?>">
                        <button class="wpaf-accordion-header" type="button" aria-expanded="<?php echo 0 === $index ? 'true' : 'false'; ?>">
                            <span class="wpaf-accordion-title"><?php echo esc_html( $filter['title'] ); ?></span>
                            <span class="wpaf-accordion-arrow">
                                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="6 9 12 15 18 9"/></svg>
                            </span>
                        </button>
                        <div class="wpaf-accordion-body" <?php echo 0 !== $index ? 'style="display:none;"' : ''; ?>>
                            <?php
                            switch ( $filter['type'] ) {
                                case 'price':
                                    $this->render_price_filter( $current_min, $current_max );
                                    break;
                                case 'color':
                                    $this->render_color_filter( $filter );
                                    break;
                                case 'brand':
                                    $this->render_checkbox_filter( $filter, 'brand' );
                                    break;
                                case 'attribute':
                                    $this->render_checkbox_filter( $filter, 'attribute' );
                                    break;
                            }
                            ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>

            <div class="wpaf-actions">
                <button type="button" class="wpaf-apply-filters"><?php esc_html_e( 'החל סינון', 'webify-products-archive-filters' ); ?></button>
                <button type="button" class="wpaf-clear-filters"><?php esc_html_e( 'נקה סינון', 'webify-products-archive-filters' ); ?></button>
            </div>
        </div>
        <?php
    }

    /**
     * Render price filter inputs.
     */
    private function render_price_filter( $current_min, $current_max ) {
        ?>
        <div class="wpaf-price-filter" data-filter-type="price">
            <div class="wpaf-price-inputs">
                <div class="wpaf-price-field">
                    <label for="wpaf-min-price"><?php esc_html_e( 'מ-', 'webify-products-archive-filters' ); ?></label>
                    <input type="number" id="wpaf-min-price" class="wpaf-price-input" name="min_price" placeholder="<?php esc_attr_e( 'מינימום', 'webify-products-archive-filters' ); ?>" value="<?php echo esc_attr( $current_min ); ?>" min="0" step="1">
                </div>
                <span class="wpaf-price-separator">-</span>
                <div class="wpaf-price-field">
                    <label for="wpaf-max-price"><?php esc_html_e( 'עד', 'webify-products-archive-filters' ); ?></label>
                    <input type="number" id="wpaf-max-price" class="wpaf-price-input" name="max_price" placeholder="<?php esc_attr_e( 'מקסימום', 'webify-products-archive-filters' ); ?>" value="<?php echo esc_attr( $current_max ); ?>" min="0" step="1">
                </div>
            </div>
        </div>
        <?php
    }

    /**
     * Render color filter as swatches.
     */
    private function render_color_filter( $filter ) {
        $active_terms = isset( $_GET[ 'filter_' . $filter['slug'] ] )
            ? array_map( 'sanitize_text_field', explode( ',', $_GET[ 'filter_' . $filter['slug'] ] ) )
            : [];
        ?>
        <div class="wpaf-color-filter" data-filter-type="color" data-taxonomy="<?php echo esc_attr( $filter['slug'] ); ?>">
            <div class="wpaf-color-swatches">
                <?php foreach ( $filter['terms'] as $term ) :
                    $color_value = get_term_meta( $term->term_id, 'color', true );
                    if ( empty( $color_value ) ) {
                        $color_value = get_term_meta( $term->term_id, 'product_attribute_color', true );
                    }
                    $is_active = in_array( $term->slug, $active_terms, true );
                    ?>
                    <label class="wpaf-color-swatch<?php echo $is_active ? ' wpaf-selected' : ''; ?>" title="<?php echo esc_attr( $term->name ); ?>">
                        <input type="checkbox" name="filter_color[]" value="<?php echo esc_attr( $term->slug ); ?>" <?php checked( $is_active ); ?> hidden>
                        <?php if ( ! empty( $color_value ) ) : ?>
                            <span class="wpaf-swatch-circle" style="background-color: <?php echo esc_attr( $color_value ); ?>;"></span>
                        <?php else : ?>
                            <span class="wpaf-swatch-circle wpaf-swatch-text"><?php echo esc_html( mb_substr( $term->name, 0, 2 ) ); ?></span>
                        <?php endif; ?>
                        <span class="wpaf-swatch-label"><?php echo esc_html( $term->name ); ?></span>
                    </label>
                <?php endforeach; ?>
            </div>
        </div>
        <?php
    }

    /**
     * Render a checkbox-based filter (for brands and attributes).
     */
    private function render_checkbox_filter( $filter, $type ) {
        $param_key    = 'filter_' . $filter['slug'];
        $active_terms = isset( $_GET[ $param_key ] )
            ? array_map( 'sanitize_text_field', explode( ',', $_GET[ $param_key ] ) )
            : [];
        ?>
        <div class="wpaf-checkbox-filter" data-filter-type="<?php echo esc_attr( $type ); ?>" data-taxonomy="<?php echo esc_attr( $filter['slug'] ); ?>">
            <ul class="wpaf-checkbox-list">
                <?php foreach ( $filter['terms'] as $term ) :
                    $is_active = in_array( $term->slug, $active_terms, true );
                    ?>
                    <li>
                        <label class="wpaf-checkbox-label">
                            <input type="checkbox" name="filter_<?php echo esc_attr( $filter['slug'] ); ?>[]" value="<?php echo esc_attr( $term->slug ); ?>" <?php checked( $is_active ); ?>>
                            <span class="wpaf-checkmark"></span>
                            <span class="wpaf-label-text"><?php echo esc_html( $term->name ); ?></span>
                            <span class="wpaf-count">(<?php echo esc_html( $term->count ); ?>)</span>
                        </label>
                    </li>
                <?php endforeach; ?>
            </ul>
        </div>
        <?php
    }
}
