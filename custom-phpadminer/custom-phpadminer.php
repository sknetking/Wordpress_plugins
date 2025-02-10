<?php
/*
Plugin Name: Custom phpadminer
Description: Integrates Adminer with WordPress and pre-fills database credentials securely.
Version: 1.1
Author: SK NetKing
*/

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}


// Register the Adminer menu page
function adminer_integration_menu() {
    add_menu_page(
        'Adminer', // Page title
        'Adminer', // Menu title
        'manage_options', // Capability
        'adminer-integration', // Menu slug
        'adminer_integration_page', // Callback function
        'dashicons-database', // Icon
        6 // Position
    );
}
add_action('admin_menu', 'adminer_integration_menu');



// Display Adminer inside an iframe within the plugin settings page
function adminer_integration_page() {
    if (!current_user_can('manage_options')) {
        wp_die(__('You do not have sufficient permissions to access this page.'));
    }

    // Get database credentials
    $db_name = DB_NAME;
    $db_user = DB_USER;
    $db_password = DB_PASSWORD;
    $db_host = DB_HOST;

    // Adminer path inside the plugin directory
    $adminer_url = plugins_url('php-adminer.php', __FILE__);
    ?>
	<style>
		form#adminer-form {
    display: flex;
    flex-direction: column;
    row-gap: 19px;
    width: max-content;
}
</style>	
    <div class="wrap">
        <h1>Adminer Database Manager</h1>
        <p>Click the button below to open Adminer with pre-filled database credentials.</p>
        
        <!-- Form to submit credentials to Adminer -->
       <form id="adminer-form" action="<?php echo esc_url($adminer_url); ?>" method="post" target="_blank">
           <label> server <input type="text" name="auth[server]" value="<?php echo esc_attr($db_host); ?>"> </label>
          <label>  username <input type="text" name="auth[username]" value="<?php echo esc_attr($db_user); ?>"></label>
          <label>  password <input type="text" name="auth[password]" value="<?php echo esc_attr($db_password); ?>"></label>
           <label>DB  <input type="text" name="auth[db]" value="<?php echo esc_attr($db_name); ?>"></label>
            <input type="submit" value="Open Adminer" class="button button-primary">
        </form>

    </div>
    <?php
}