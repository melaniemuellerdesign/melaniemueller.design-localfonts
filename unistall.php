<?php

if (!defined('WP_UNINSTALL_PLUGIN')) {
exit();
}

// Define the activation hook function
function my_plugin_deletion()
{
    $localfonts_dir = WP_CONTENT_DIR . '/localfonts';

    if (!file_exists($localfonts_dir)) {
        rmdir($localfonts_dir);
    }
}

// Register the activation hook
register_activation_hook(__FILE__, 'my_plugin_deletion');
