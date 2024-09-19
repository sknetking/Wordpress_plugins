<?php 
function my_repeater_settings_page() {
    add_submenu_page(
        'pdf-uploader',
        'User Data',
		'User Data',
        'manage_options',
        'repeater-settings',
        'my_repeater_settings_page_html'
    );
}
add_action('admin_menu', 'my_repeater_settings_page');

function my_repeater_settings_page_html() {
    ?>
    <div class="wrap">
        <h1>Add User To Access PDF</h1>
		<p>Only this users can access pdf by there email and token!</p>
        <form method="post" action="options.php">
            <?php
            settings_fields('my_repeater_settings_group');
            do_settings_sections('my_repeater_settings_group');
            
            // Get the saved data
            $repeater_data = get_option('my_repeater_data',true);
		       ?>
           <div id="repeater-wrapper">
                <?php if ($repeater_data && is_array($repeater_data)) :
					$index=0;
                    foreach ($repeater_data as $index => $data) : 
						?>
                        <div class="repeater-item">
                            <input type="text" name="my_repeater_data[<?=$index?>][name]" placeholder="Name" value="<?php echo $data['name']; ?>">
                            <input type="email" name="my_repeater_data[<?=$index?>][email]" placeholder="Email" value="<?php echo $data['email']; ?>">
                            <input type="text" name="my_repeater_data[<?=$index?>][token]" placeholder="Token" value="<?php echo $data['token']; ?>">
                            <button type="button" class="remove-repeater">Remove</button>
                        </div>
    	           <?php
				$index++;
	endforeach;
                endif; ?>
            </div>

            <button type="button" id="add-repeater">Add More</button>

            <?php submit_button(); ?>
        </form>
    </div>
<style>
#repeater-wrapper {
    display: flex;
    row-gap: 8px;
    flex-direction: column;
    margin-bottom: 11px;
}
#repeater-wrapper .remove-repeater {
    font-size: 15px;
    border-radius: 31px;
    cursor: pointer;
    background-color: #fd0000;
    padding: 6px 11px;
    color: #fff;
    font-family: sans-serif;
    font-weight: bold;
}
button#add-repeater {
    padding: 2px 13px;
    color: #fff;
    background-color: green;
    font-size: 19px;
    border-radius: 4px;
}
</style>	
    <script type="text/javascript">
      document.addEventListener('DOMContentLoaded', function () {
    var repeaterWrapper = document.getElementById('repeater-wrapper');
    var addRepeaterBtn = document.getElementById('add-repeater');

    addRepeaterBtn.addEventListener('click', function () {
        var index = repeaterWrapper.children.length;
        var newItem = document.createElement('div');
        newItem.classList.add('repeater-item');
        newItem.innerHTML = `
            <input type="text" name="my_repeater_data[` + index + `][name]" placeholder="Name">
            <input type="email" name="my_repeater_data[` + index + `][email]" placeholder="Email">
            <input type="text" name="my_repeater_data[` + index + `][token]" placeholder="Token">
            <button type="button" class="remove-repeater">Remove</button>
        `;
        repeaterWrapper.appendChild(newItem);
    });

    repeaterWrapper.addEventListener('click', function (e) {
        if (e.target && e.target.classList.contains('remove-repeater')) {
            e.target.parentElement.remove();

            // Re-index remaining items
            var items = repeaterWrapper.querySelectorAll('.repeater-item');
            items.forEach(function (item, i) {
                item.querySelector('[name$="[name]"]').setAttribute('name', 'my_repeater_data[' + i + '][name]');
                item.querySelector('[name$="[email]"]').setAttribute('name', 'my_repeater_data[' + i + '][email]');
                item.querySelector('[name$="[token]"]').setAttribute('name', 'my_repeater_data[' + i + '][token]');
            });
        }
    });
});
    </script>
    <?php
}

function my_repeater_settings() {
    register_setting('my_repeater_settings_group', 'my_repeater_data');
}
add_action('admin_init', 'my_repeater_settings');

function sanitize_repeater_data($input) {
    $sanitized = array();
    if (is_array($input)) {
        foreach ($input as $key => $value) {
            $sanitized[$key] = array_map('sanitize_text_field', $value);
        }
    }
    return $sanitized;
}
add_filter('pre_update_option_my_repeater_data', 'sanitize_repeater_data');
