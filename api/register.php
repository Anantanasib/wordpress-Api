<?php
add_action('rest_api_init', function () {
    register_rest_route('api/v1', '/register', [
        'methods'  => 'POST',
        'callback' => 'custom_user_registration_no_website',
        'permission_callback' => '__return_true',
    ]);
});

function custom_user_registration_no_website($request) {
    $username   = sanitize_text_field($request->get_param('username'));
    $email      = sanitize_email($request->get_param('email'));
    $password   = $request->get_param('password');
    $first_name = sanitize_text_field($request->get_param('first_name'));
    $last_name  = sanitize_text_field($request->get_param('last_name'));

    // Validate required fields
    if (empty($username) || empty($email) || empty($password)) {
        return new WP_REST_Response(['status' => 'error', 'message' => 'Username, email, and password are required.'], 400);
    }

    if (username_exists($username)) {
        return new WP_REST_Response(['status' => 'error', 'message' => 'Username already exists.'], 409);
    }

    if (email_exists($email)) {
        return new WP_REST_Response(['status' => 'error', 'message' => 'Email already exists.'], 409);
    }

    // Create user
    $user_id = wp_create_user($username, $password, $email);

    if (is_wp_error($user_id)) {
        return new WP_REST_Response(['status' => 'error', 'message' => $user_id->get_error_message()], 500);
    }

    // Set first and last name
    wp_update_user([
        'ID'         => $user_id,
        'first_name' => $first_name,
        'last_name'  => $last_name,
        'role'       => 'subscriber',
    ]);

    return new WP_REST_Response([
        'status'     => 'success',
        'message'    => 'User registered successfully.',
        'user_id'    => $user_id,
        'username'   => $username,
        'email'      => $email,
        'first_name' => $first_name,
        'last_name'  => $last_name,
    ], 200);
}
?>
