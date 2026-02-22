<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Handles AJAX requests for product filtering.
 */
class WPAF_Ajax {

    public function __construct() {
        add_action( 'wp_ajax_wpaf_filter_products', [ $this, 'filter_products' ] );
        add_action( 'wp_ajax_nopriv_wpaf_filter_products', [ $this, 'filter_products' ] );
    }

    /**
     * AJAX callback — returns filtered products HTML.
     */
    public function filter_products() {
        check_ajax_referer( 'wpaf_filter_nonce', 'nonce' );

        $params = [
            'min_price' => isset( $_POST['min_price'] ) ? floatval( $_POST['min_price'] ) : '',
            'max_price' => isset( $_POST['max_price'] ) ? floatval( $_POST['max_price'] ) : '',
            'paged'     => isset( $_POST['paged'] ) ? absint( $_POST['paged'] ) : 1,
            'orderby'   => isset( $_POST['orderby'] ) ? sanitize_text_field( $_POST['orderby'] ) : '',
            'order'     => isset( $_POST['order'] ) ? sanitize_text_field( $_POST['order'] ) : '',
            'term_id'   => isset( $_POST['term_id'] ) ? absint( $_POST['term_id'] ) : 0,
            'taxonomy'  => isset( $_POST['taxonomy'] ) ? sanitize_text_field( $_POST['taxonomy'] ) : '',
            'filters'   => [],
        ];

        // Collect taxonomy filters
        if ( ! empty( $_POST['filters'] ) && is_array( $_POST['filters'] ) ) {
            foreach ( $_POST['filters'] as $taxonomy => $slugs ) {
                $taxonomy = sanitize_text_field( $taxonomy );
                if ( is_array( $slugs ) ) {
                    $params['filters'][ $taxonomy ] = array_map( 'sanitize_text_field', $slugs );
                } else {
                    $params['filters'][ $taxonomy ] = array_map( 'sanitize_text_field', explode( ',', $slugs ) );
                }
            }
        }

        $args  = WPAF_Query::build_args( $params );
        $query = new WP_Query( $args );

        ob_start();

        if ( $query->have_posts() ) {
            woocommerce_product_loop_start();

            while ( $query->have_posts() ) {
                $query->the_post();

                /**
                 * Hook: woocommerce_shop_loop.
                 */
                do_action( 'woocommerce_shop_loop' );

                wc_get_template_part( 'content', 'product' );
            }

            woocommerce_product_loop_end();
        } else {
            echo '<li class="wpaf-no-results-item" style="list-style:none;grid-column:1/-1;text-align:center;padding:48px 24px;color:#8e8ea0;font-size:15px;">';
            echo '<p>' . esc_html__( 'לא נמצאו מוצרים התואמים לסינון שבחרת.', 'webify-products-archive-filters' ) . '</p>';
            echo '</li>';
        }

        $html = ob_get_clean();

        // Pagination — only inner links, JS will inject into existing wrapper
        $total_pages = $query->max_num_pages;
        $pagination  = '';
        if ( $total_pages > 1 ) {
            $pagination = '<nav class="woocommerce-pagination">' . paginate_links( [
                'total'     => $total_pages,
                'current'   => $params['paged'],
                'format'    => '?paged=%#%',
                'prev_text' => '&laquo;',
                'next_text' => '&raquo;',
            ] ) . '</nav>';
        }

        wp_reset_postdata();

        wp_send_json_success( [
            'html'        => $html,
            'pagination'  => $pagination,
            'found_posts' => $query->found_posts,
            'max_pages'   => $total_pages,
        ] );
    }
}
