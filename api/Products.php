<?php
add_action('rest_api_init', function () {
    register_rest_route('api/v1', '/products', [
        'methods'  => 'GET',
        'callback' => 'get_products_paginated',
        'permission_callback' => '__return_true',
    ]);
});

function get_products_paginated($request) {
    $page     = $request->get_param('page') ?: 1;
    $per_page = $request->get_param('per_page') ?: 10;

    $args = [
        'limit'  => $per_page,
        'page'   => $page,
        'status' => 'publish',
    ];

    $my_products = wc_get_products($args);
    $store = [];

    foreach ($my_products as $product) {
        $categories = wp_get_post_terms($product->get_id(), 'product_cat');
        $formatted_categories = [];

        foreach ($categories as $cat) {
            $category_data = [
                'id'   => $cat->term_id,
                'name' => $cat->name,
            ];

            // Check for parent category
            if ($cat->parent && $cat->parent != 0) {
                $parent = get_term($cat->parent, 'product_cat');
                if (!is_wp_error($parent)) {
                    $category_data['parent'] = [
                        'id'   => $parent->term_id,
                        'name' => $parent->name,
                    ];
                }
            }

            $formatted_categories[] = $category_data;
        }

        $store[] = [
            'product_id'         => $product->get_id(),
            'product_name'       => $product->get_name(),
            'regular_price'      => $product->get_regular_price(),
            'sale_price'         => $product->get_sale_price(),
            'product_image'      => wp_get_attachment_url($product->get_image_id()),
            'product_rating'     => $product->get_average_rating(),
            'product_categories' => $formatted_categories,
        ];
    }

    return new WP_REST_Response($store, 200);
}
?>
