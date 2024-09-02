<?php
/*
Plugin Name: User Profile Picture
Description: Allows users to select a profile picture from the WordPress media library.
Version: 1.0
Author: Shyam Sahani
*/

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}



// Enqueue the media uploader script
function upp_enqueue_media_uploader($hook_suffix) {
    // Only load the media uploader on the profile and user-edit pages
    if ('profile.php' !== $hook_suffix && 'user-edit.php' !== $hook_suffix) {
        return;
    }

    // Enqueue the WordPress media uploader
    wp_enqueue_media();

    // Include the script for handling the media picker
    ?>
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.7.1/jquery.min.js"></script>
    <script type="text/javascript">
        jQuery(document).ready(function($){
            $('#upload-profile-picture-button').on('click', function(e) {
                e.preventDefault();

                var image_frame;

                // If the frame already exists, reopen it
                if (image_frame) {
                    image_frame.open();
                    return;
                }

                // Define the image_frame as wp.media object
                image_frame = wp.media({
                    title: 'Select Profile Picture',
                    multiple : false,
                    library : {
                        type : 'image',
                    }
                });

                // Handle image selection
                image_frame.on('select', function() {
                    var attachment = image_frame.state().get('selection').first().toJSON();
                    $('#profile_picture').val(attachment.id);
                    $('#profile-picture-preview').attr('src', attachment.url);
                });

                // Open the media frame
                image_frame.open();
            });
        });
    </script>
    <?php
}

// Load the media uploader script in the admin
add_action('admin_enqueue_scripts', 'upp_enqueue_media_uploader');

// Add the media picker to the user profile page
function upp_add_media_picker($user) {
    ?>
    <h3><?php _e('Profile Picture', 'user-profile-picture'); ?></h3>
    <table class="form-table">
        <tr>
            <th><label for="profile_picture"><?php _e('Profile Picture', 'user-profile-picture'); ?></label></th>
            <td class="custom-profile-pic">
                <input type="hidden" name="profile_picture" id="profile_picture" value="<?php echo esc_attr(get_user_meta($user->ID, 'profile_picture', true)); ?>" />
                <img id="profile-picture-preview" src="<?php echo esc_url(get_user_meta($user->ID, 'profile_picture_url', true)); ?>" style="max-width: 150px;" />
                <input type="button" id="upload-profile-picture-button" class="button" value="<?php _e('Upload Profile Picture', 'user-profile-picture'); ?>" />
            </td>
        </tr>
    </table>
<style>
	tr.user-profile-picture {
    display: none;
}
td.custom-profile-pic {
    display: inline-grid;
    gap: 10px;
}

</style>
    <?php
}

// Show the media picker in the profile page
add_action('show_user_profile', 'upp_add_media_picker');
add_action('edit_user_profile', 'upp_add_media_picker');

// Save the profile picture
function upp_save_profile_picture($user_id) {
    if (isset($_POST['profile_picture'])) {
        update_user_meta($user_id, 'profile_picture', $_POST['profile_picture']);
        update_user_meta($user_id, 'profile_picture_url', wp_get_attachment_url($_POST['profile_picture']));
    }
}

// Save profile picture on profile update
add_action('personal_options_update', 'upp_save_profile_picture');
add_action('edit_user_profile_update', 'upp_save_profile_picture');

// Filter the default avatar
function upp_custom_user_avatar($avatar, $id_or_email, $size, $default, $alt) {
    $user = false;

    if (is_numeric($id_or_email)) {
        $user = get_user_by('id', $id_or_email);
    } elseif (is_string($id_or_email)) {
        $user = get_user_by('email', $id_or_email);
    } elseif (is_object($id_or_email)) {
        if (!empty($id_or_email->user_id)) {
            $user = get_user_by('id', $id_or_email->user_id);
        }
    }

    if ($user && is_object($user)) {
        $profile_picture_url = get_user_meta($user->ID, 'profile_picture_url', true);
        if ($profile_picture_url) {
            $avatar = '<img src="' . esc_url($profile_picture_url) . '" alt="' . esc_attr($alt) . '" class="avatar avatar-' . esc_attr($size) . ' photo" width="' . esc_attr($size) . '" height="' . esc_attr($size) . '" />';
        }
    }

    return $avatar;
}

// Hook into the 'get_avatar' filter
add_filter('get_avatar', 'upp_custom_user_avatar', 10, 5);
