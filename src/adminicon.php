<?php

function enqueue_localfonts_styles()
{
    wp_enqueue_style('plugin-icon', plugin_dir_url(__FILE__) . 'css/icon.css');
}
add_action('admin_enqueue_scripts', 'enqueue_localfonts_styles');