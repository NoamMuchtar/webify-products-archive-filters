<?php
/**
 * Plugin Name: Webify Products Archive Filters
 * Description: Advanced WooCommerce product filters for Elementor archive pages — filter by price, attributes, color, and brand with accordion UI and mobile sidebar.
 * Version: 1.0.0
 * Author: Webify
 * Text Domain: webify-products-archive-filters
 * Domain Path: /languages
 * Requires at least: 5.8
 * Requires PHP: 7.4
 * WC requires at least: 6.0
 * Elementor tested up to: 3.20
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

define( 'WPAF_VERSION', '1.0.0' );
define( 'WPAF_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );
define( 'WPAF_PLUGIN_URL', plugin_dir_url( __FILE__ ) );

/**
 * Check for required plugins.
 */
function wpaf_check_requirements() {
    $missing = [];

    if ( ! class_exists( 'WooCommerce' ) ) {
        $missing[] = 'WooCommerce';
    }

    if ( ! did_action( 'elementor/loaded' ) ) {
        $missing[] = 'Elementor';
    }

    if ( ! empty( $missing ) ) {
        add_action( 'admin_notices', function () use ( $missing ) {
            printf(
                '<div class="notice notice-error"><p>%s</p></div>',
                sprintf(
                    esc_html__( 'Webify Products Archive Filters requires the following plugins: %s', 'webify-products-archive-filters' ),
                    '<strong>' . implode( ', ', $missing ) . '</strong>'
                )
            );
        } );
        return false;
    }

    return true;
}

/**
 * Initialize the plugin.
 */
function wpaf_init() {
    if ( ! wpaf_check_requirements() ) {
        return;
    }

    require_once WPAF_PLUGIN_DIR . 'includes/class-wpaf-ajax.php';
    require_once WPAF_PLUGIN_DIR . 'includes/class-wpaf-query.php';

    new WPAF_Ajax();

    add_action( 'elementor/widgets/register', 'wpaf_register_widgets' );
    add_action( 'wp_enqueue_scripts', 'wpaf_enqueue_assets' );
}
add_action( 'plugins_loaded', 'wpaf_init' );

/**
 * Register Elementor widgets.
 */
function wpaf_register_widgets( $widgets_manager ) {
    require_once WPAF_PLUGIN_DIR . 'widgets/class-wpaf-filters-widget.php';
    $widgets_manager->register( new WPAF_Filters_Widget() );
}

/**
 * Enqueue frontend assets.
 */
function wpaf_enqueue_assets() {
    if ( ! is_shop() && ! is_product_taxonomy() ) {
        return;
    }

    wp_enqueue_style(
        'wpaf-filters',
        WPAF_PLUGIN_URL . 'assets/css/filters.css',
        [],
        WPAF_VERSION
    );

    wp_enqueue_script(
        'wpaf-filters',
        WPAF_PLUGIN_URL . 'assets/js/filters.js',
        [ 'jquery' ],
        WPAF_VERSION,
        true
    );

    wp_localize_script( 'wpaf-filters', 'wpafData', [
        'ajaxUrl'  => admin_url( 'admin-ajax.php' ),
        'nonce'    => wp_create_nonce( 'wpaf_filter_nonce' ),
        'shopUrl'  => get_permalink( wc_get_page_id( 'shop' ) ),
    ] );
}

/**
 * Register widget category.
 */
function wpaf_add_elementor_widget_categories( $elements_manager ) {
    $elements_manager->add_category(
        'webify',
        [
            'title' => esc_html__( 'Webify', 'webify-products-archive-filters' ),
            'icon'  => 'fa fa-plug',
        ]
    );
}
add_action( 'elementor/elements/categories_registered', 'wpaf_add_elementor_widget_categories' );
