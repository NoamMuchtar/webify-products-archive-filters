<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Modifies the main WooCommerce product query based on URL filter parameters.
 */
class WPAF_Ajax {

    public function __construct() {
        add_action( 'pre_get_posts', [ $this, 'filter_main_query' ] );
    }

    /**
     * Modify the main WooCommerce query when filter URL params are present.
     */
    public function filter_main_query( $query ) {
        if ( is_admin() || ! $query->is_main_query() ) {
            return;
        }

        if ( ! is_shop() && ! is_product_taxonomy() ) {
            return;
        }

        // Price filter
        $min_price = isset( $_GET['min_price'] ) ? floatval( $_GET['min_price'] ) : '';
        $max_price = isset( $_GET['max_price'] ) ? floatval( $_GET['max_price'] ) : '';

        if ( $min_price !== '' && $min_price > 0 ) {
            $meta_query   = $query->get( 'meta_query', [] );
            $meta_query[] = [
                'key'     => '_price',
                'value'   => $min_price,
                'compare' => '>=',
                'type'    => 'NUMERIC',
            ];
            $query->set( 'meta_query', $meta_query );
        }

        if ( $max_price !== '' && $max_price > 0 ) {
            $meta_query   = $query->get( 'meta_query', [] );
            $meta_query[] = [
                'key'     => '_price',
                'value'   => $max_price,
                'compare' => '<=',
                'type'    => 'NUMERIC',
            ];
            $query->set( 'meta_query', $meta_query );
        }

        // Taxonomy filters (filter_pa_color, filter_product_brand, etc.)
        $tax_query = $query->get( 'tax_query', [] );
        $has_tax_filters = false;

        foreach ( $_GET as $key => $value ) {
            if ( strpos( $key, 'filter_' ) !== 0 || empty( $value ) ) {
                continue;
            }

            $taxonomy = sanitize_text_field( str_replace( 'filter_', '', $key ) );

            if ( ! taxonomy_exists( $taxonomy ) ) {
                continue;
            }

            $slugs = array_map( 'sanitize_text_field', explode( ',', $value ) );

            if ( ! empty( $slugs ) ) {
                $tax_query[] = [
                    'taxonomy' => $taxonomy,
                    'field'    => 'slug',
                    'terms'    => $slugs,
                    'operator' => 'IN',
                ];
                $has_tax_filters = true;
            }
        }

        if ( $has_tax_filters ) {
            $tax_query['relation'] = 'AND';
            $query->set( 'tax_query', $tax_query );
        }
    }
}
