<?php

function add_plugin_menu()
{
    add_submenu_page(
        'themes.php',          // Parent menu slug (Appearance)
        'Localfonts',         // Page title
        'Localfonts',         // Menu title
        'manage_options',      // Capability required to access the menu
        'localfonts-slug',    // Menu slug
        'my_custom_localfonts_page',  // Callback function to display the menu content
        2 // This will position the submenu item at position 20
    );
}
add_action('admin_menu', 'add_plugin_menu');
