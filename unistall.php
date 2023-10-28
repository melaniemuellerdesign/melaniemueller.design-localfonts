<?php

if (!defined('WP_UNINSTALL_PLUGIN')) {
die;
}

// Define the path to the folder you want to delete
$folder_path = WP_CONTENT_DIR . '/localfonts';

// Check if the folder exists before attempting to delete it
if (is_dir($folder_path)) {
    // Delete all files in the folder
    $files = glob($folder_path . '/*');
    foreach ($files as $file) {
        unlink($file);
    }

    // Delete the folder itself
    rmdir($folder_path);
}
