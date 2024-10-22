<?php 
// Register the Options Page
function lsc_register_options_page() {
    add_menu_page(
        'Live Support Chat Settings', 
        'Chat Settings', 
        'manage_options', 
        'lsc-settings', 
        'lsc_render_options_page', 
        'dashicons-format-chat', 
        100
    );
}
add_action('admin_menu', 'lsc_register_options_page');

// Render the Options Page
function lsc_render_options_page() {
    ?>
    <div class="wrap">
        <h1>Live Support Chat Settings</h1>
        <form method="post" action="options.php">
            <?php
            settings_fields('lsc_settings_group');
            do_settings_sections('lsc-settings');
            submit_button();
            ?>
        </form>
    </div>
    <?php
}

function lsc_register_settings() {
    register_setting('lsc_settings_group', 'lsc_auto_refresh_time');
    register_setting('lsc_settings_group', 'lsc_user_chat');
    register_setting('lsc_settings_group', 'lsc_file_upload');
    register_setting('lsc_settings_group', 'lsc_enable_delete');

    add_settings_section(
        'lsc_main_settings', 
        'Main Settings', 
        null, 
        'lsc-settings'
    );

    add_settings_field(
        'lsc_auto_refresh_time', 
        'Auto Refresh Time (in seconds)', 
        'lsc_auto_refresh_time_callback', 
        'lsc-settings', 
        'lsc_main_settings'
    );

    add_settings_field(
        'lsc_user_chat', 
        'Allow Users to Chat with Anyone.', 
        'lsc_user_chat_callback', 
        'lsc-settings', 
        'lsc_main_settings'
    );

    add_settings_field(
        'lsc_file_upload', 
        'Enable File Upload', 
        'lsc_file_upload_callback', 
        'lsc-settings', 
        'lsc_main_settings'
    );

    add_settings_field(
        'lsc_enable_delete', 
        'Enable Message Delete Option', 
        'lsc_enable_delete_callback', 
        'lsc-settings', 
        'lsc_main_settings'
    );
    add_settings_field(
        'lsc_clear_database', 
        'Clear Message Database!', 
        'delete_database_call', 
        'lsc-settings', 
        'lsc_main_settings'
    );

}
add_action('admin_init', 'lsc_register_settings');

// Callback Functions for Settings Fields
function lsc_auto_refresh_time_callback() {
    $value = get_option('lsc_auto_refresh_time', 5); // Default 5 seconds
    echo '<input type="number" name="lsc_auto_refresh_time" value="' . esc_attr($value) . '" min="1" />';
}

function lsc_user_chat_callback() {
    $checked = get_option('lsc_user_chat', false);
    echo '<input type="checkbox" name="lsc_user_chat" value="1"' . checked(1, $checked, false) . ' />';
}

function lsc_file_upload_callback() {
    $checked = get_option('lsc_file_upload', false);
    echo '<input type="checkbox" name="lsc_file_upload" value="1"' . checked(1, $checked, false) . ' />';
}

function lsc_enable_delete_callback() {
    $checked = get_option('lsc_enable_delete', false);
    echo '<input type="checkbox" name="lsc_enable_delete" value="1"' . checked(1, $checked, false) . ' />';
}

function delete_database_call(){
 ?>
    <button id="clear-table-btn" class="button button-danger">Clear Data</button>
    <div id="table-status"></div>
<script type="text/javascript">
    document.getElementById('clear-table-btn').addEventListener('click', function() {
        if (confirm('Are you sure you want to clear all data from the wp_lsc_messages table?')) {
            fetch(ajaxurl, {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: 'action=clear_custom_table',
            })
            .then(response => response.json())
            .then(data => {
                document.getElementById('table-status').innerText = data.message;
            });
        }
    });
</script>
<?php 

}

// AJAX Action to Clear Table Data
function clear_custom_table() {
    global $wpdb;
    $table_name = $wpdb->prefix . 'lsc_messages'; // Use prefix for security
    
    // Run SQL to Delete All Data
    $result = $wpdb->query("TRUNCATE TABLE $table_name");

    if ($result === false) {
        wp_send_json_error(['message' => 'Failed to clear table data.']);
    } else {
        wp_send_json_success(['message' => 'Table data cleared successfully!']);
    }

    wp_die(); // Required to terminate the AJAX request properly
}
add_action('wp_ajax_clear_custom_table', 'clear_custom_table');
