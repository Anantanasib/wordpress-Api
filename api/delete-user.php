<?php
// Register the REST API route
add_action('rest_api_init', function () {
    register_rest_route('api/v1', '/delete-user', [
        'methods'  => 'POST', // Use 'DELETE' if preferred
        'callback' => 'custom_delete_user',
        'permission_callback' => '__return_true', // For production, replace with proper auth
    ]);
});

/**
 * Callback to delete a user by ID
 */
function custom_delete_user($request) {
    $user_id = (int) $request->get_param('user_id');

    // Validate user ID
    if (empty($user_id) || !get_userdata($user_id)) {
        return new WP_REST_Response([
            'status'  => 'error',
            'message' => 'Invalid or missing user ID.'
        ], 400);
    }

    // Prevent deletion of self (optional)
    if (get_current_user_id() === $user_id) {
        return new WP_REST_Response([
            'status'  => 'error',
            'message' => 'You cannot delete your own account.'
        ], 403);
    }

    // Optional: 0 to delete posts or set to another user ID to reassign
    $reassign = 0;

    // Attempt to delete user
    $deleted = wp_delete_user($user_id, $reassign);

    if ($deleted) {
        return new WP_REST_Response([
            'status'  => 'success',
            'message' => 'User deleted successfully.'
        ], 200);
    } else {
        return new WP_REST_Response([
            'status'  => 'error',
            'message' => 'Failed to delete user. User may have admin privileges or be protected.'
        ], 500);
    }
}
?>
