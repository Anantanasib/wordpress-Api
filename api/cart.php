<?php
function custom_add_to_cart($request) {
    if (!WC()->cart) {
        wc_load_cart();
    }

    $product_id   = (int) $request->get_param('product_id');
    $variation_id = (int) $request->get_param('variation_id');
    $quantity     = (int) $request->get_param('quantity') ?: 1;
    $variation    = $request->get_param('variation') ?: [];

    $main_product = wc_get_product($product_id);
    if (!$main_product || !$main_product->is_purchasable()) {
        return new WP_REST_Response([
            'status' => 'error',
            'message' => 'Invalid or non-purchasable product.'
        ], 400);
    }

    if ($main_product->is_type('variable')) {
        if (!$variation_id || empty($variation)) {
            return new WP_REST_Response([
                'status' => 'error',
                'message' => 'Variation details required for variable product.'
            ], 400);
        }

        $cart_key = WC()->cart->add_to_cart($product_id, $quantity, $variation_id, $variation);
    } else {
        // For simple products, ignore any variation details passed
        $cart_key = WC()->cart->add_to_cart($product_id, $quantity);
    }

    if (!$cart_key) {
        return new WP_REST_Response([
            'status' => 'error',
            'message' => 'Failed to add product to cart.'
        ], 500);
    }

    return new WP_REST_Response([
        'status'  => 'success',
        'message' => 'Product added to cart.',
        'cart_contents' => WC()->cart->get_cart()
    ], 200);
}


?>