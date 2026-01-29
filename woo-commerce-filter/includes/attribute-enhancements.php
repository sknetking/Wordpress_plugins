<?php
/**
 * WooCommerce Product Filter - Attribute Enhancement
 * 
 * This script adds support for color swatches and enhanced attribute display
 * Add this to your theme's functions.php or as a separate plugin
 */

// Add custom meta fields for product attributes
add_action('pa_color_add_form_fields', 'add_color_meta_field');
add_action('pa_color_edit_form_fields', 'edit_color_meta_field', 10, 2);
add_action('created_pa_color', 'save_color_meta_field');
add_action('edited_pa_color', 'save_color_meta_field');

function add_color_meta_field($taxonomy) {
    ?>
<div class="form-field">
    <label for="color_value"><?php _e('Color Value', 'woo-product-filter'); ?></label>
    <input type="text" name="color_value" id="color_value" placeholder="#FF0000 or red">
    <p class="description">
        <?php _e('Enter hex color code (e.g., #FF0000) or color name (e.g., red)', 'woo-product-filter'); ?></p>
</div>
<?php
}

function edit_color_meta_field($term, $taxonomy) {
    $color_value = get_term_meta($term->term_id, 'color', true);
    ?>
<tr class="form-field">
    <th scope="row" valign="top">
        <label for="color_value"><?php _e('Color Value', 'woo-product-filter'); ?></label>
    </th>
    <td>
        <input type="text" name="color_value" id="color_value" value="<?php echo esc_attr($color_value); ?>"
            placeholder="#FF0000 or red">
        <p class="description">
            <?php _e('Enter hex color code (e.g., #FF0000) or color name (e.g., red)', 'woo-product-filter'); ?></p>
    </td>
</tr>
<?php
}

function save_color_meta_field($term_id) {
    if (isset($_POST['color_value'])) {
        update_term_meta($term_id, 'color', sanitize_text_field($_POST['color_value']));
    }
}

// Add image support for attributes
add_action('pa_size_add_form_fields', 'add_size_meta_field');
add_action('pa_size_edit_form_fields', 'edit_size_meta_field', 10, 2);
add_action('created_pa_size', 'save_size_meta_field');
add_action('edited_pa_size', 'save_size_meta_field');

function add_size_meta_field($taxonomy) {
    ?>
<div class="form-field">
    <label for="size_display"><?php _e('Display Type', 'woo-product-filter'); ?></label>
    <select name="size_display" id="size_display">
        <option value="badge"><?php _e('Badge', 'woo-product-filter'); ?></option>
        <option value="text"><?php _e('Text', 'woo-product-filter'); ?></option>
    </select>
    <p class="description"><?php _e('How to display this attribute in filters', 'woo-product-filter'); ?></p>
</div>
<?php
}

function edit_size_meta_field($term, $taxonomy) {
    $size_display = get_term_meta($term->term_id, 'display', true);
    ?>
<tr class="form-field">
    <th scope="row" valign="top">
        <label for="size_display"><?php _e('Display Type', 'woo-product-filter'); ?></label>
    </th>
    <td>
        <select name="size_display" id="size_display">
            <option value="badge" <?php selected($size_display, 'badge'); ?>><?php _e('Badge', 'woo-product-filter'); ?>
            </option>
            <option value="text" <?php selected($size_display, 'text'); ?>><?php _e('Text', 'woo-product-filter'); ?>
            </option>
        </select>
        <p class="description"><?php _e('How to display this attribute in filters', 'woo-product-filter'); ?></p>
    </td>
</tr>
<?php
}

function save_size_meta_field($term_id) {
    if (isset($_POST['size_display'])) {
        update_term_meta($term_id, 'display', sanitize_text_field($_POST['size_display']));
    }
}

// Add image support for any attribute type
add_action('add_form_fields', 'add_attribute_image_field');
add_action('edit_form_fields', 'edit_attribute_image_field', 10, 2);
add_action('created_term', 'save_attribute_image_field');
add_action('edited_term', 'save_attribute_image_field');

function add_attribute_image_field($taxonomy) {
    if (strpos($taxonomy, 'pa_') !== 0) return;
    ?>
<div class="form-field">
    <label for="attribute_image"><?php _e('Attribute Image', 'woo-product-filter'); ?></label>
    <input type="hidden" name="attribute_image_id" id="attribute_image_id" value="">
    <input type="url" name="attribute_image" id="attribute_image" placeholder="https://example.com/image.jpg">
    <button type="button" class="button"
        id="upload_attribute_image"><?php _e('Upload Image', 'woo-product-filter'); ?></button>
    <p class="description"><?php _e('Optional image for this attribute value', 'woo-product-filter'); ?></p>
</div>
<script>
jQuery(document).ready(function($) {
    $('#upload_attribute_image').click(function(e) {
        e.preventDefault();
        var image = wp.media({
                title: '<?php _e('Select Attribute Image', 'woo-product-filter'); ?>',
                multiple: false
            }).open()
            .on('select', function(e) {
                var uploaded_image = image.state().get('selection').first();
                var image_url = uploaded_image.toJSON().url;
                var image_id = uploaded_image.toJSON().id;
                $('#attribute_image').val(image_url);
                $('#attribute_image_id').val(image_id);
            });
    });
});
</script>
<?php
}

function edit_attribute_image_field($term, $taxonomy) {
    if (strpos($taxonomy, 'pa_') !== 0) return;
    
    $image_id = get_term_meta($term->term_id, 'image', true);
    $image_url = $image_id ? wp_get_attachment_image_url($image_id, 'thumbnail') : '';
    ?>
<tr class="form-field">
    <th scope="row" valign="top">
        <label for="attribute_image"><?php _e('Attribute Image', 'woo-product-filter'); ?></label>
    </th>
    <td>
        <input type="hidden" name="attribute_image_id" id="attribute_image_id"
            value="<?php echo esc_attr($image_id); ?>">
        <input type="url" name="attribute_image" id="attribute_image" value="<?php echo esc_url($image_url); ?>"
            placeholder="https://example.com/image.jpg">
        <button type="button" class="button"
            id="upload_attribute_image"><?php _e('Upload Image', 'woo-product-filter'); ?></button>
        <?php if ($image_url) : ?>
        <div style="margin-top: 10px;">
            <img src="<?php echo esc_url($image_url); ?>"
                style="max-width: 50px; height: auto; border: 1px solid #ddd;">
        </div>
        <?php endif; ?>
        <p class="description"><?php _e('Optional image for this attribute value', 'woo-product-filter'); ?></p>
    </td>
</tr>
<script>
jQuery(document).ready(function($) {
    $('#upload_attribute_image').click(function(e) {
        e.preventDefault();
        var image = wp.media({
                title: '<?php _e('Select Attribute Image', 'woo-product-filter'); ?>',
                multiple: false
            }).open()
            .on('select', function(e) {
                var uploaded_image = image.state().get('selection').first();
                var image_url = uploaded_image.toJSON().url;
                var image_id = uploaded_image.toJSON().id;
                $('#attribute_image').val(image_url);
                $('#attribute_image_id').val(image_id);
                location.reload(); // Reload to show the new image
            });
    });
});
</script>
<?php
}

function save_attribute_image_field($term_id) {
    if (isset($_POST['attribute_image_id'])) {
        update_term_meta($term_id, 'image', intval($_POST['attribute_image_id']));
    }
}