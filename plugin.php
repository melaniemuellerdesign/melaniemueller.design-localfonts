<?php

/**
 * Plugin Name: melaniemueller.design-localfonts
 * Plugin URI: http://melaniemueller.design
 * Description: Add local fonts to the website
 * Text Domain: melaniemueller.design-localfonts
 * Author: melaniemueller.design
 * Author URI: https://melaniemueller.design
 * License: GPL2+
 * Tested up to: 6.3.1
 * Requires PHP: 5.6
 * License URI: https://www.gnu.org/licenses/gpl-2.0.txt
 * Update URI:  https://melaniemueller.design/development/melaniemueller.design-localfonts/info.json
 * Version: v0.0.1.7
 */

/*add localfonts folder to wp-content*/

register_activation_hook(__FILE__, 'create_localfonts_folder');

function create_localfonts_folder()
{
	$localfonts_dir = WP_CONTENT_DIR . '/localfonts';

	if (!file_exists($localfonts_dir)) {
		wp_mkdir_p($localfonts_dir);
	}
}

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
		$upload_dir = WP_CONTENT_DIR . '/localfonts/';
		$files = scandir($upload_dir);

		foreach ($files as $file) {
			if ($file !== '.' && $file !== '..') {
				echo "<li>";
				echo "<a href='" . get_home_url() . '/wp-content/localfonts/' . $file . "' target='_blank'>$file</a>";
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
			<textarea name="custom_css" rows="10" style="width: 100%;"><?php echo $custom_css; ?></textarea>
			<p><input type="submit" class="button-primary" value="Save CSS"></p>
		</form>
	</div>
<?php
}

//output css in head
function custom_css_output()
{
	if (current_user_can('manage_options')) {
		$custom_css = get_option('custom_css');
		$custom_css_removed_backslash = str_replace("\\", "", $custom_css);


		if (!empty($custom_css)) {
			echo '<style type="text/css">' . $custom_css_removed_backslash . '</style>';
		}
	}
}

add_action('wp_head', 'custom_css_output');
add_action('enqueue_block_editor_assets', 'custom_css_output');



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
		$upload_dir = WP_CONTENT_DIR . '/localfonts/'; // Save files in a 'files' subdirectory of your plugin.

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
		$upload_dir = WP_CONTENT_DIR . '/localfonts/';

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
require_once($src_path . 'update.php');
require_once($src_path . 'pluginmenu.php');
require_once($src_path . 'adminicon.php');