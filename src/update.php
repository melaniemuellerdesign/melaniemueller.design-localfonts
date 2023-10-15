<?php

/**
 * Enqueue Update Check
 * 
 */


function myplugin_check_for_updates($plugin)
{
    //get current version
    $currentURL = plugin_dir_path(ABSPATH) . '/wp-content/plugins/melaniemueller.design-localfonts/info.json'; // Get the currently active plugin
    $current_version = get_json_response($currentURL, 'version'); // Get the version of the plugin
    $currentJSON = plugin_dir_path(ABSPATH) . '/wp-content/plugins/melaniemueller.design-localfonts/info.json'; // Get the currently active plugin

    var_dump($currentURL);
    exit;

    //get update version
    $updateURL = get_json_response($currentJSON, 'updateURL');
    $remote_version  = get_json_response($updateURL, 'version');

    if (version_compare($current_version, $remote_version, '<')) {
        return true;
    }
}

function myplugin_pre_set_site_transient_update_plugins($transient)
{
    $update = myplugin_check_for_updates('melaniemueller.design-localfonts');

    $currentJSON = plugin_dir_path(ABSPATH) . '/wp-content/plugins/melaniemueller.design-localfonts/info.json'; // Get the currently active plugin  
    $current_version = get_json_response($currentJSON, 'version'); // Get the version of the plugin


    //get update version
    $updateJSON = get_json_response($currentJSON, 'updateURL');
    $remote_version  = get_json_response($updateJSON, 'version');
    $remote_package  = get_json_response($updateJSON, 'package');
    $requires = get_json_response($updateJSON, 'requires');
    $requires_php = get_json_response($updateJSON, 'requires_php');


    // Query premium/private repo for updates.
    if ($update === true) {
        // Update is available.
        // $update should be an array containing all of the fields in $item below.
        $update = array(
            'id'            => 'melaniemueller.design-localfonts/plugin.php',
            'slug'          => 'melaniemueller.design-localfonts',
            'plugin'        => 'melaniemueller.design-localfonts/plugin.php',
            'new_version'  => $remote_version,
            'url'          => $currentJSON,
            'package'      => $remote_package,
            'icons'         => array(),
            'banners'       => array(),
            'banners_rtl'   => array(),
            'requires'     => $requires,
            'requires_php' => $requires_php,
            'compatibility' => new stdClass(),

        );
        $transient->response['melaniemueller.design-localfonts/plugin.php'] = $update;
    } else {
        // No update is available.
        $item = array(
            'id'            => 'melaniemueller.design-localfonts/plugin.php',
            'slug'          => 'melaniemueller.design-localfonts',
            'plugin'        => 'melaniemueller.design-localfonts/plugin.php',
            'new_version'   => $current_version,
            'url'           => '',
            'package'       => '',
            'icons'         => array(),
            'banners'       => array(),
            'banners_rtl'   => array(),
            'tested'        => '',
            'requires_php'  => '',
            'compatibility' => new stdClass(),
        );
        // Adding the "mock" item to the `no_update` property is required
        // for the enable/disable auto-updates links to correctly appear in UI.
        $transient->no_update['melaniemueller.design-localfonts/plugin.php'] = $item;
    }

    return $transient;
}

add_filter('pre_set_site_transient_update_plugins', 'myplugin_pre_set_site_transient_update_plugins');