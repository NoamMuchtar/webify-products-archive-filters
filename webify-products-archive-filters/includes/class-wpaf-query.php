<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Builds the WP_Query args for filtered product queries.
 */
class WPAF_Query {

    /**
     * Build query args from filter parameters.
     *
     * @param array $params Sanitized filter parameters.
     * @return array WP_Query compatible args.
     */
    public static function build_args( array $params ) {
        $args = [
            'post_type'      => 'product',
            'post_status'    => 'publish',
            'posts_per_page' => (int) ( $params['per_page'] ?? get_option( 'posts_per_page', 12 ) ),
            'paged'          => (int) ( $params['paged'] ?? 1 ),
            'tax_query'      => [ 'relation' => 'AND' ],
            'meta_query'     => [ 'relation' => 'AND' ],
        ];

        // If we're on a specific taxonomy archive, keep that constraint.
        if ( ! empty( $params['term_id'] ) && ! empty( $params['taxonomy'] ) ) {
            $args['tax_query'][] = [
                'taxonomy' => sanitize_text_field( $params['taxonomy'] ),
                'field'    => 'term_id',
                'terms'    => (int) $params['term_id'],
            ];
        }

        // Price filter
        if ( ! empty( $params['min_price'] ) || ! empty( $params['max_price'] ) ) {
            $price_meta = [];

            if ( ! empty( $params['min_price'] ) ) {
                $price_meta[] = [
                    'key'     => '_price',
                    'value'   => floatval( $params['min_price'] ),
                    'compare' => '>=',
                    'type'    => 'NUMERIC',
                ];
            }

            if ( ! empty( $params['max_price'] ) ) {
                $price_meta[] = [
                    'key'     => '_price',
                    'value'   => floatval( $params['max_price'] ),
                    'compare' => '<=',
                    'type'    => 'NUMERIC',
                ];
            }

            foreach ( $price_meta as $pm ) {
                $args['meta_query'][] = $pm;
            }
        }

        // Taxonomy-based filters (color, brand, attributes)
        if ( ! empty( $params['filters'] ) && is_array( $params['filters'] ) ) {
            foreach ( $params['filters'] as $taxonomy => $slugs ) {
                $taxonomy = sanitize_text_field( $taxonomy );
                if ( ! taxonomy_exists( $taxonomy ) ) {
                    continue;
                }
                $slugs = array_map( 'sanitize_text_field', (array) $slugs );
                if ( ! empty( $slugs ) ) {
                    $args['tax_query'][] = [
                        'taxonomy' => $taxonomy,
                        'field'    => 'slug',
                        'terms'    => $slugs,
                        'operator' => 'IN',
                    ];
                }
            }
        }

        // Ordering
        $orderby = $params['orderby'] ?? 'menu_order title';
        $order   = $params['order'] ?? 'ASC';

        switch ( $orderby ) {
            case 'price':
                $args['meta_key'] = '_price';
                $args['orderby']  = 'meta_value_num';
                $args['order']    = $order;
                break;
            case 'popularity':
                $args['meta_key'] = 'total_sales';
                $args['orderby']  = 'meta_value_num';
                $args['order']    = 'DESC';
                break;
            case 'rating':
                $args['meta_key'] = '_wc_average_rating';
                $args['orderby']  = 'meta_value_num';
                $args['order']    = 'DESC';
                break;
            case 'date':
                $args['orderby'] = 'date';
                $args['order']   = 'DESC';
                break;
            default:
                $args['orderby'] = 'menu_order title';
                $args['order']   = 'ASC';
                break;
        }

        // Only show visible products
        $args['tax_query'][] = [
            'taxonomy' => 'product_visibility',
            'field'    => 'name',
            'terms'    => 'exclude-from-catalog',
            'operator' => 'NOT IN',
        ];

        return $args;
    }
}
