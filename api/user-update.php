<?php
add_action('rest_api_init', function () {
    register_rest_route('api/v1', '/update-user', [
        'methods'  => 'POST',
        'callback' => 'custom_user_update',
        'permission_callback' => '__return_true', // Add auth if needed
    ]);
});

function custom_user_update($request) {
    $user_id    = $request->get_param('user_id'); // Required
    $email      = sanitize_email($request->get_param('email'));
    $first_name = sanitize_text_field($request->get_param('first_name'));
    $last_name  = sanitize_text_field($request->get_param('last_name'));
    $password   = $request->get_param('password');

    // Validate
    if (empty($user_id) || !get_userdata($user_id)) {
        return new WP_REST_Response(['status' => 'error', 'message' => 'Invalid or missing user ID.'], 400);
    }

    $user_data = ['ID' => $user_id];

    if (!empty($email)) {
        if (!is_email($email)) {
            return new WP_REST_Response(['status' => 'error', 'message' => 'Invalid email address.'], 400);
        }
        $user_data['user_email'] = $email;
    }

    if (!empty($first_name)) {
        $user_data['first_name'] = $first_name;
    }

    if (!empty($last_name)) {
        $user_data['last_name'] = $last_name;
    }

    $update_result = wp_update_user($user_data);

    if (is_wp_error($update_result)) {
        return new WP_REST_Response(['status' => 'error', 'message' => $update_result->get_error_message()], 500);
    }

    if (!empty($password)) {
        wp_set_password($password, $user_id);
    }

    return new WP_REST_Response([
        'status'  => 'success',
        'message' => 'User updated successfully.',
    ], 200);
}
?>
