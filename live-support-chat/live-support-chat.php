<?php
/**
 * Plugin Name: Live Support Chat
 * Description: A live chat plugin for users to chat with admin or other users based on permissions. Add the shortcode [live_support_chat] to any page where you want the chat to appear.
 * Version: 1.0
 * Author: Shyam
 */

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}


add_action('wp_enqueue_scripts', 'lsc_enqueue_assets');

function lsc_enqueue_assets() {
    wp_enqueue_style('lsc-style', plugin_dir_url(__FILE__) . 'assets/css/style.css');
    wp_enqueue_script('lsc-chat', plugin_dir_url(__FILE__) . 'assets/js/chat.js', ['jquery'], false, true);

    wp_localize_script('lsc-chat', 'LSC_Ajax', [
        'ajax_url' => admin_url('admin-ajax.php'),
        'current_user_id' => get_current_user_id(),
    ]);

    wp_localize_script('lsc-chat', 'LSC_Settings', [
        'auto_refresh_time' => get_option('lsc_auto_refresh_time', 5),
        'user_chat_allowed' => get_option('lsc_user_chat', false),
        'file_upload_enabled' => get_option('lsc_file_upload', false),
        'delete_enabled' => get_option('lsc_enable_delete', false),
    ]);
    
}

// Create Chat Table on Activation
function lsc_create_chat_table() {
    global $wpdb;
    $table_name = $wpdb->prefix . 'lsc_messages';
    $charset_collate = $wpdb->get_charset_collate();

    $sql = "CREATE TABLE $table_name (
        id BIGINT(20) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
        sender_id BIGINT(20) NOT NULL,
        receiver_id BIGINT(20) NOT NULL,
        message TEXT NOT NULL,
        timestamp DATETIME DEFAULT CURRENT_TIMESTAMP
    ) $charset_collate;";

    require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
    dbDelta($sql);
}
register_activation_hook(__FILE__, 'lsc_create_chat_table');


include ("admin/settings.php");

// AJAX to Handle Messages
function lsc_send_message() {
    global $wpdb;

    $sender_id = get_current_user_id();
    $receiver_id = intval($_POST['receiver_id']);
    $message = sanitize_text_field($_POST['message']);

    if (!$sender_id || !$message) {
        wp_send_json_error('Invalid data');
    }

    $wpdb->insert(
        $wpdb->prefix . 'lsc_messages',
        [
            'sender_id' => $sender_id,
            'receiver_id' => $receiver_id,
            'message' => $message,
        ]
    );

    wp_send_json_success(['message' => $message]);
}
add_action('wp_ajax_send_message', 'lsc_send_message');
add_action('wp_ajax_nopriv_send_message', 'lsc_send_message');

// AJAX to Load Chat Messages
function lsc_load_messages() {
    global $wpdb;

    $user_id = get_current_user_id();
    $receiver_id = intval($_POST['receiver_id']);

    $results = $wpdb->get_results(
        $wpdb->prepare(
            "SELECT * FROM {$wpdb->prefix}lsc_messages 
             WHERE (sender_id = %d AND receiver_id = %d) 
                OR (sender_id = %d AND receiver_id = %d) 
             ORDER BY timestamp ASC",
            $user_id, $receiver_id, $receiver_id, $user_id
        )
    );


    if (!empty($results)) {
        wp_send_json_success($results);
    } else {
        wp_send_json_error('No messages found.');
    }
}
add_action('wp_ajax_load_messages', 'lsc_load_messages');
add_action('wp_ajax_nopriv_load_messages', 'lsc_load_messages');

// Shortcode to Display Chat Window
function lsc_chat_window() {
    ob_start();
    include plugin_dir_path(__FILE__) . 'templates/chat-window.php';
    return ob_get_clean();
}
add_shortcode('live_support_chat', 'lsc_chat_window');

include_once 'admin/delete_message.php';