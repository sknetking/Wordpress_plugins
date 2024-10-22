<?php 

function delete_message() {
    global $wpdb;

    $sender_id = get_current_user_id();
    $message_id = intval($_POST['message_id']); // Ensure message_id is an integer

    // Validate the data
    if (!$sender_id || !$message_id) {
        wp_send_json_error('Invalid data');
    }

    // Check if the message exists and belongs to the current user
    $table = $wpdb->prefix . 'lsc_messages';
    $message = $wpdb->get_row(
        $wpdb->prepare(
            "SELECT * FROM {$table} WHERE id = %d AND sender_id = %d", 
            $message_id, 
            $sender_id
        )
    );

    if (!$message) {
        wp_send_json_error('Message not found or permission denied');
    }

    // Delete the message
    $deleted = $wpdb->delete(
        $table, 
        ['id' => $message_id, 'sender_id' => $sender_id], 
        ['%d', '%d']
    );

    if ($deleted) {
        wp_send_json_success(['message' => 'Message deleted successfully']);
    } else {
        wp_send_json_error('Failed to delete message');
    }
}
add_action('wp_ajax_delete_message', 'delete_message');
add_action('wp_ajax_nopriv_delete_message', 'delete_message');
