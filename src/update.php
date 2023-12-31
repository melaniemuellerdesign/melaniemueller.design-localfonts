<?php

/**
 * Enqueue Update Check
 * 
 */

function get_json_response_localfonts($url, $target)
{
    $response = wp_safe_remote_get($url);

    $obj = json_decode($response['body'], true);
    $themeName = 'melaniemueller.design-localfonts/plugin.php';
    $themeArray = ($obj[$themeName]);
    $jsonResult = $themeArray[$target];

    return $jsonResult;
}

function myplugin_check_for_updates($plugin)
{
    //get current version
    $currentJSON = get_home_url() . '/wp-content/plugins/' . $plugin . '/info.json'; // Get the currently active plugin
    $current_version = get_json_response_localfonts($currentJSON, 'version'); // Get the version of the plugin

    //get update version
    $updateURL = get_json_response_localfonts($currentJSON, 'updateURL');
    $remote_version  = get_json_response_localfonts($updateURL, 'version');

    if (version_compare($current_version, $remote_version, '<')) {
        return true;
    }
}

function myplugin_pre_set_site_transient_update_plugins($transient)
{
    $updateCheck = myplugin_check_for_updates('melaniemueller.design-localfonts');

    $currentJSON = get_home_url() . '/wp-content/plugins/melaniemueller.design-localfonts/info.json'; // Get the currently active plugin
    $myplugin_current_version = get_json_response_localfonts($currentJSON, 'version'); // Get the version of the plugin

    //get update version
    $updateJSON = get_json_response_localfonts($currentJSON, 'updateURL');
    $myplugin_future_version  = get_json_response_localfonts($updateJSON, 'version');
    $myplugin_future_package  = get_json_response_localfonts($updateJSON, 'package');
    $myplugin_future_requires = get_json_response_localfonts($updateJSON, 'requires');
    $myplugin_future_requires_php = get_json_response_localfonts($updateJSON, 'requires_php');


    // Query premium/private repo for updates.
    if ($updateCheck === true) {
        // Update is available.
        // $update should be an array containing all of the fields in $item below.
        $update = (object) array(
            'id'            => 'melaniemueller.design-localfonts/plugin.php',
            'slug'          => 'melaniemueller.design-localfonts',
            'plugin'        => 'melaniemueller.design-localfonts/plugin.php',
            'new_version'   => $myplugin_future_version,
            'url'           => $currentJSON,
            'package'       => $myplugin_future_package,
            'tested'        => $myplugin_future_requires,
            'requires_php'  => $myplugin_future_requires_php,
            'compatibility' => new stdClass(),
        );

        $transient->response['melaniemueller.design-localfonts/plugin.php'] = $update;
    } else {
        // No update is available.
        $item = (object) array(
            'id'            => 'melaniemueller.design-localfonts/plugin.php',
            'slug'          => 'melaniemueller.design-localfonts',
            'plugin'        => 'melaniemueller.design-localfonts/plugin.php',
            'new_version'   => $myplugin_current_version,
            'url'           => $currentJSON,
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