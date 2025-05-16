<?php
add_action('rest_api_init', function (){
    register_rest_route('api/v1', '/categories', [
        'methods' => 'GET',
        'callback' => 'get_product_categories',
        'permission_callback' => '__return_true',
    ]);
});

function get_product_categories($request){
    $terms = get_terms([
        'taxonomy'   => 'product_cat',
        'hide_empty' => false,
        'orderby'    => 'name',
        'order'      => 'ASC',
    ]);

    $categories = [];

    foreach ($terms as $term) {
        $categories[] = [
            'id'          => $term->term_id,
            'name'        => $term->name,
            'slug'        => $term->slug,
            'description' => $term->description,
            'parent'      => $term->parent,
            'count'       => $term->count,
            'link'        => get_term_link($term),
            'image'       => get_category_image_url($term), // ✅ fixed function name
        ];
    }

    return new WP_REST_Response($categories, 200);
}

function get_category_image_url($term) {
    $thumbnail_id = get_term_meta($term->term_id, 'thumbnail_id', true);
    if ($thumbnail_id) {
        return wp_get_attachment_url($thumbnail_id);
    }
    return null;
}
?>
