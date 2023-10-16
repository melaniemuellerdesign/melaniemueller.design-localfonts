<?php

/**
 * Plugin Name: melaniemuellerdesign-localfonts
 * Description: Add local fonts to the website
 * Author: melaniemueller.design
 * Author URI: https://melaniemueller.design
 * License: GPL2+
 * License URI: https://www.gnu.org/licenses/gpl-2.0.txt
 */

function custom_file_upload_form()
{
	ob_start();
?>
	<form method="post" enctype="multipart/form-data">
		<label for="file">Choose a font file:</label>
		<input type="file" name="file" id="file">
		<input type="submit" name="upload" value="Upload File">
	</form>

	<!-- Display the list of uploaded font files -->
	<h3>Uploaded Font Files:</h3>
	<ul>
		<?php
		$upload_dir = plugin_dir_path(__FILE__) . 'files/';
		$files = scandir($upload_dir);

		foreach ($files as $file) {
			if ($file !== '.' && $file !== '..') {
				echo "<li>";
				echo "<a href='" . plugins_url('files/' . $file, __FILE__) . "' target='_blank'>$file</a>";
				echo " <a href='?delete_file=$file'>Delete</a>";
				echo "</li>";
			}
		}
		?>
	</ul>
<?php
	echo ob_get_clean();
}

function custom_css_editor_page()
{
	if (isset($_POST['custom_css'])) {
		update_option('custom_css', $_POST['custom_css']);
	}

	$custom_css = get_option('custom_css');
?>
	<div class="wrap">
		<h2>Custom CSS Editor</h2>
		<form method="post">
			<textarea name="custom_css" rows="10" style="width: 100%;"><?php echo esc_textarea($custom_css); ?></textarea>
			<p><input type="submit" class="button-primary" value="Save CSS"></p>
		</form>
	</div>
<?php
}

function custom_css_editor_menu()
{
	add_menu_page('Custom CSS Editor', 'CSS Editor', 'manage_options', 'custom-css-editor', 'custom_css_editor_page');
}

add_action('admin_menu', 'custom_css_editor_menu');

//output css in head
function custom_css_output()
{
	if (current_user_can('manage_options')) {
		$custom_css = get_option('custom_css');

		if (!empty($custom_css)) {
			echo '<style type="text/css">' . esc_html($custom_css) . '</style>';
		}
	}
}

add_action('wp_head', 'custom_css_output');



function my_custom_localfonts_page()
{
	// Your menu content goes here
	echo '<div class="wrap"><h2>Localfonts Menu</h2></div>';
	custom_file_upload_form();
	custom_css_editor_page();
}

function handle_file_upload()
{
	if (isset($_POST['upload'])) {
		$uploaded_file = $_FILES['file'];
		$upload_dir = plugin_dir_path(__FILE__) . 'files/'; // Save files in a 'files' subdirectory of your plugin.

		// Ensure the target directory exists, or create it if it doesn't.
		if (!file_exists($upload_dir)) {
			mkdir($upload_dir, 0755, true);
		}

		// Check if the uploaded file is a font file (e.g., .ttf, .woff, .woff2).
		$allowed_font_types = array('ttf', 'woff', 'woff2', 'eot', 'svg');
		$file_info = pathinfo($uploaded_file['name']);

		if (in_array($file_info['extension'], $allowed_font_types)) {
			$target_path = $upload_dir . basename($uploaded_file['name']);

			if (move_uploaded_file($uploaded_file['tmp_name'], $target_path)) {
				// File uploaded successfully.
				echo "Font file uploaded successfully!";
			} else {
				// Error handling for a failed upload.
				echo "Font file upload failed!";
			}
		} else {
			// File is not a supported font file.
			echo "Please upload a supported font file (TTF, WOFF, WOFF2).";
		}
	}

	if (isset($_GET['delete_file'])) {
		$file_to_delete = sanitize_text_field($_GET['delete_file']);
		$upload_dir = plugin_dir_path(__FILE__) . 'files/';

		if (file_exists($upload_dir . $file_to_delete)) {
			unlink($upload_dir . $file_to_delete);
		}

		// Redirect to the Localfonts submenu page after deleting.
		$localfonts_page_url = admin_url('admin.php?page=localfonts-slug');
		wp_safe_redirect($localfonts_page_url);
		exit();
	}
}

add_action('admin_init', 'handle_file_upload');

// Define the path to the src folder
$src_path = plugin_dir_path(__FILE__) . 'src/';

// Include a specific file
include($src_path . 'update.php');
include($src_path . 'pluginmenu.php');