<?php
add_action('rest_api_init', function () {
    register_rest_route('api/v1', '/login', [
        'methods'  => 'POST',
        'callback' => 'custom_user_login',
        'permission_callback' => '__return_true',
    ]);
});

function custom_user_login($request) {
    $username = sanitize_text_field($request->get_param('username'));
    $password = $request->get_param('password');

    if (empty($username) || empty($password)) {
        return new WP_REST_Response(['status' => 'error', 'message' => 'Username and password are required.'], 400);
    }

    $creds = [
        'user_login'    => $username,
        'user_password' => $password,
        'remember'      => true,
    ];

    $user = wp_signon($creds, false);

    if (is_wp_error($user)) {
        return new WP_REST_Response(['status' => 'error', 'message' => 'Invalid username or password.'], 401);
    }

    return new WP_REST_Response([
        'status'      => 'success',
        'message'     => 'Login successful.',
        'user_id'     => $user->ID,
        'username'    => $user->user_login,
        'email'       => $user->user_email,
        'first_name'  => get_user_meta($user->ID, 'first_name', true),
        'last_name'   => get_user_meta($user->ID, 'last_name', true),
    ], 200);
}
?>
