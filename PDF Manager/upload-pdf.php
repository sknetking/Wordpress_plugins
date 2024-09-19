<?php 

add_action('admin_menu', 'pdf_uploader_menu');

function pdf_uploader_menu() {
    add_menu_page(
        'PDF Uploader',
        'PDF Manager',
        'manage_options',
        'pdf-uploader',
        'pdf_uploader_options_page'
    );
}

// Create the options page
function pdf_uploader_options_page() {
    ?>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-EVSTQN3/azprG1Anm3QDgpJLIm9Nao0Yz1ztcQTwFspd3yD65VohhpuuCOmLASjC" crossorigin="anonymous">
    <div class="wrap">
        
   <div class="row">
      <div class="col-md-4">
        <form method="post" action="options.php">
            <?php
            settings_fields('adp_options_group');  // Match the group registered with register_setting()
            do_settings_sections('adp');  // Use the slug of your options page
            submit_button();
            ?>
        </form>
    </div>
    <div class="col-md-8">
        <form method="post" enctype="multipart/form-data">
            <table class="form-table">
                <tr valign="top">
                    <th scope="row">Upload PDF File</th>
                    <td>
                        <input type="file" name="pdf_file" accept="application/pdf" required>
                    </td>
                </tr>
            </table>

            <input type="submit" name="submit_pdf" class="button button-primary" value="Upload PDF">
        </form>
    </div>
</div>
<h3>Upload PDFs</h3>
<?php
if (isset($_POST['submit_pdf'])) {
    pdf_uploader_handle_file_upload();
}

$uploaded_files = get_option('pdf_uploader_files',[]);

$index=0;
if ($uploaded_files) {
    echo '<ul class="list-group">';
    foreach ($uploaded_files as $file) {
        echo '<li class="list-group-item">' . esc_html($file)."  ";
        echo ' <form method="post" style="display:inline; float:right;">';
        echo '<input type="hidden" name="delete_file_index" value="' . esc_attr($index) . '">';
        echo '<input type="submit" name="delete_pdf" value="Delete" class="btn btn-danger" onclick="return confirm(\'Are you sure you want to delete this file?\');">';
        echo '</form> </li>';
        $index++;
    }
    echo '</ul>';
}
?>

</div> 


    <?php
}

// Handle file upload
function pdf_uploader_handle_file_upload() {
    if (!function_exists('wp_handle_upload')) {
        require_once(ABSPATH . 'wp-admin/includes/file.php');
    }

    $uploaded_file = $_FILES['pdf_file'];

    // Check if the file is a PDF
    $file_type = wp_check_filetype($uploaded_file['name']);
    if ($file_type['ext'] !== 'pdf') {
        echo '<div class="error"><p>Only PDF files are allowed.</p></div>';
        return;
    }

    // Define the directory to store the PDF files (inside your plugin directory)
     $upload_dir = plugin_dir_path(__FILE__) . 'docs/';
    // if (!file_exists($upload_dir)) {
    //     mkdir($upload_dir, 0755, true);
    // }

    // Move the file to the /docs/ directory
    $uploaded_file_path = $upload_dir . basename($uploaded_file['name']);

    if (move_uploaded_file($uploaded_file['tmp_name'], $uploaded_file_path)) {
        // Store the file name in the option
        $uploaded_files = get_option('pdf_uploader_files', []);
        $uploaded_files[] = basename($uploaded_file['name']);
        update_option('pdf_uploader_files', $uploaded_files);
		if (chmod($uploaded_file_path, 0777)) {
            echo "Permissions set to 777 for the uploaded file.";
        }
        echo '<div class="updated"><p>File uploaded successfully.</p></div>';
    } else {
        echo '<div class="error"><p>File upload failed.</p></div>';
    }
}

// Check if delete form has been submitted
if (isset($_POST['delete_pdf'])) {
    $delete_file_index = intval($_POST['delete_file_index']);
    // Fetch the uploaded files option
   $pdf_uploader_files = get_option('pdf_uploader_files');

// Check if the option is an array and has an index 0
	if (is_array($pdf_uploader_files) && isset($pdf_uploader_files[$delete_file_index])) {
		// Remove the entry at index 0
		unset($pdf_uploader_files[$delete_file_index]);

		// Reindex the array (optional, depending on how you want to handle indexes)
		$pdf_uploader_files = array_values($pdf_uploader_files);
		// Update the option with the modified array
		update_option('pdf_uploader_files', $pdf_uploader_files);
		unlink( plugin_dir_path(__FILE__) . 'docs/'.$pdf_uploader_files[$delete_file_index]);
	}
}
