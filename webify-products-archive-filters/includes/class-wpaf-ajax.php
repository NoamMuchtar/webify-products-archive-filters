<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Modifies the WooCommerce product query based on URL filter parameters.
 *
 * Uses the woocommerce_product_query hook which fires specifically for
 * product archive queries, avoiding conflicts with other queries.
 */
class WPAF_Ajax {

    public function __construct() {
        add_action( 'woocommerce_product_query', [ $this, 'apply_filters' ] );
    }

    /**
     * Apply URL-based filters to the WooCommerce product query.
     *
     * @param WC_Query|WP_Query $query
     */
    public function apply_filters( $query ) {

        // Price filter
        $min_price = isset( $_GET['min_price'] ) ? floatval( $_GET['min_price'] ) : 0;
        $max_price = isset( $_GET['max_price'] ) ? floatval( $_GET['max_price'] ) : 0;

        if ( $min_price > 0 ) {
            $meta_query = $query->get( 'meta_query', [] );
            $meta_query[] = [
                'key'     => '_price',
                'value'   => $min_price,
                'compare' => '>=',
                'type'    => 'NUMERIC',
            ];
            $query->set( 'meta_query', $meta_query );
        }

        if ( $max_price > 0 ) {
            $meta_query = $query->get( 'meta_query', [] );
            $meta_query[] = [
                'key'     => '_price',
                'value'   => $max_price,
                'compare' => '<=',
                'type'    => 'NUMERIC',
            ];
            $query->set( 'meta_query', $meta_query );
        }

        // Taxonomy filters (filter_pa_color=red,blue&filter_pa_size=large)
        $tax_query = $query->get( 'tax_query', [] );
        $added     = false;

        foreach ( $_GET as $key => $value ) {
            if ( strpos( $key, 'filter_' ) !== 0 || empty( $value ) ) {
                continue;
            }

            $taxonomy = sanitize_text_field( str_replace( 'filter_', '', $key ) );
            if ( ! taxonomy_exists( $taxonomy ) ) {
                continue;
            }

            $slugs = array_filter( array_map( 'sanitize_text_field', explode( ',', $value ) ) );
            if ( empty( $slugs ) ) {
                continue;
            }

            $tax_query[] = [
                'taxonomy' => $taxonomy,
                'field'    => 'slug',
                'terms'    => $slugs,
                'operator' => 'IN',
            ];
            $added = true;
        }

        if ( $added ) {
            $tax_query['relation'] = 'AND';
            $query->set( 'tax_query', $tax_query );
        }
    }
}
