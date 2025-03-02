<?php

/**
 * Enqueue Update Check
 * 
 */

 function get_json_response_localfonts($url, $target)
 {
     $response = wp_safe_remote_get($url);
 
     // Überprüfung, ob die Anfrage fehlgeschlagen ist
     if (is_wp_error($response)) {
         error_log('Fehler bei der Remote-Anfrage: ' . $response->get_error_message());
         return null; // oder einen sinnvollen Default-Wert zurückgeben
     }
 
     $body = wp_remote_retrieve_body($response);
     $obj = json_decode($body, true);
 
     if (!is_array($obj)) {
         error_log('Fehler: Ungültiges JSON von ' . $url);
         return null;
     }
 
     $themeName = 'melaniemueller.design-localfonts/plugin.php';
     
     if (!isset($obj[$themeName][$target])) {
         error_log("Fehlender Schlüssel '$target' im JSON");
         return null;
     }
 
     return $obj[$themeName][$target];
 }

function localfonts_check_for_updates($plugin)
{
    //get current version
    $currentJSON = get_home_url() . '/wp-content/plugins/' . $plugin . '/info.json'; // Get the currently active plugin
    $current_version = get_json_response_localfonts($currentJSON, 'version'); // Get the version of the plugin

    //get update version
    $updateURL = get_json_response_localfonts($currentJSON, 'updateURL');
    $remote_version  = get_json_response_localfonts($updateURL, 'version');

    // Stelle sicher, dass beide Werte gültig sind
    if (empty($current_version) || empty($remote_version)) {
        error_log("Fehler: Version konnte nicht abgerufen werden. Aktuell: $current_version, Remote: $remote_version");
        return false;
    }

    if (version_compare($current_version, $remote_version, '<')) {
        return true;
    }
}

function localfonts_pre_set_site_transient_update_plugins($transient)
{
    $updateCheck = localfonts_check_for_updates('melaniemueller.design-localfonts');

    $currentJSON = get_home_url() . '/wp-content/plugins/melaniemueller.design-localfonts/info.json'; // Get the currently active plugin
    $localfonts_current_version = get_json_response_localfonts($currentJSON, 'version'); // Get the version of the plugin

    //get update version
    $updateJSON = get_json_response_localfonts($currentJSON, 'updateURL');
    $localfonts_future_version  = get_json_response_localfonts($updateJSON, 'version');
    $localfonts_future_package  = get_json_response_localfonts($updateJSON, 'package');
    $localfonts_future_requires = get_json_response_localfonts($updateJSON, 'requires');
    $localfonts_future_requires_php = get_json_response_localfonts($updateJSON, 'requires_php');


    // Query premium/private repo for updates.
    if ($updateCheck === true) {
        // Update is available.
        // $update should be an array containing all of the fields in $item below.
        $update = (object) array(
            'id'            => 'melaniemueller.design-localfonts/plugin.php',
            'slug'          => 'melaniemueller.design-localfonts',
            'plugin'        => 'melaniemueller.design-localfonts/plugin.php',
            'new_version'   => $localfonts_future_version,
            'url'           => $currentJSON,
            'package'       => $localfonts_future_package,
            'tested'        => $localfonts_future_requires,
            'requires_php'  => $localfonts_future_requires_php,
            'compatibility' => new stdClass(),
        );

        $transient->response['melaniemueller.design-localfonts/plugin.php'] = $update;
    } else {
        // No update is available.
        $item = (object) array(
            'id'            => 'melaniemueller.design-localfonts/plugin.php',
            'slug'          => 'melaniemueller.design-localfonts',
            'plugin'        => 'melaniemueller.design-localfonts/plugin.php',
            'new_version'   => $localfonts_current_version,
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

add_filter('pre_set_site_transient_update_plugins', 'localfonts_pre_set_site_transient_update_plugins');