<?php
/*
Plugin Name: PDF Access Plugin
Description: A simple plugin to restrict access to PDFs based on user credentials.
Version: 1.0
Author: New Plugin
*/

defined('ABSPATH') or die('No script kiddies please!');

// Shortcode to display the access form
function pdf_access_form_shortcode() {
    ob_start();
    ?>
    <form id="access-form">
        <input type="email" name="email" required placeholder="Email" required>
        <input type="text" name="token" required placeholder="Token" required>
        <select name='selected_doc' required>
            <option value=''> Select Doc</option>
            <?php $uploaded_files = get_option('pdf_uploader_files',[]); 
           
            if ($uploaded_files) {
             foreach ($uploaded_files as $file) {
                if(!empty($file))
                echo "<option value='".$file."'>".$file."</option>";
             }
             }
            ?>
        </select>
        <button type="submit">Access PDF</button>
    </form>
    <div id="response-message"></div>
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.7.1/jquery.min.js"></script>
    <script>
        jQuery('#access-form').on('submit', function(e) {
            e.preventDefault();
            var formData = jQuery(this).serialize();
            jQuery.post("https://dddemo.net/wordpress/2024/test_site/wp-admin/admin-ajax.php", formData + '&action=check_user_2224', function(response) {
                if (response.success) {
                    console.log(response);
                    window.location.href = response.data.redirect;
                } else {
                    jQuery('#response-message').text(response.data.message);
                }
            });
        });
    </script>
    <?php
    return ob_get_clean();
}
add_shortcode('pdf_access_form', 'pdf_access_form_shortcode');

// Handle the form submission
add_action('wp_ajax_nopriv_check_user_2224', 'check_user_info');
add_action('wp_ajax_check_user_2224', 'check_user_info');

function check_user_info() {
     $email = sanitize_email($_POST['email']);
     $token = sanitize_text_field($_POST['token']);
    $selected_doc = sanitize_text_field($_POST['selected_doc']);
	 $repeater_data = get_option('my_repeater_data',true);
	  foreach($repeater_data as $data): 
        if ($data['email'] == $email && $data['token']== $token) {
            // Grant access and redirect
            wp_send_json_success(['redirect' => home_url('/?pdf='.$selected_doc)]);
        }
 	endforeach;

    // Invalid credentials
    wp_send_json_error(['message' => 'Invalid email or token.']);
}

// Serve the PDF securely
add_action('template_redirect', 'serve_protected_pdf');

function serve_protected_pdf() {
    if (isset($_GET['pdf'])) {
        $file_path = plugin_dir_path(__FILE__) . 'docs/'.$_GET['pdf']; // Ensure the file exists
       // echo $file_path;
        if (file_exists($file_path)) {
            header('Content-Type: application/pdf');
            header('Content-Disposition: inline; filename="' . basename($file_path) . '"');
            readfile($file_path);
            exit;
        } else {
            wp_die('File not found.');
        }
    }
}

include_once("upload-pdf.php");
include_once("add-user-data.php");
