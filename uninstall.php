<?php
// Check if uninstalling is allowed
if (!defined('WP_UNINSTALL_PLUGIN')) {
    exit;
}

// Define the folder to be removed
$folder_to_remove = WP_CONTENT_DIR . '/localfonts';

// Check if the folder exists
if (is_dir($folder_to_remove)) {
    // Remove the folder and its contents
    function remove_localfonts_folder()
    {
        global $folder_to_remove;
        if (is_dir($folder_to_remove)) {
            // Remove the folder recursively
            if (false === rmdir($folder_to_remove)) {
                error_log('Error deleting localfonts folder.');
            }
        }
    }

    // Execute the removal function
    remove_localfonts_folder();
}