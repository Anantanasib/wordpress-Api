<?php
add_action('rest_api_init', function () {
    register_rest_route('api/v1', '/users', [
        'methods'  => 'GET',
        'callback' => 'get_all_users_data',
        'permission_callback' => '__return_true', // Make private if needed
    ]);
});

function get_all_users_data($request) {
    $args = [
        'number' => -1, // Get all users
        'orderby' => 'ID',
        'order' => 'ASC',
    ];

    $users = get_users($args);
    $data = [];

    foreach ($users as $user) {
        $data[] = [
            'user_id'     => $user->ID,
            'username'    => $user->user_login,
            'email'       => $user->user_email,
            'first_name'  => get_user_meta($user->ID, 'first_name', true),
            'last_name'   => get_user_meta($user->ID, 'last_name', true),
            'role'        => implode(', ', $user->roles),
        ];
    }

    return new WP_REST_Response($data, 200);
}
?>
