<?php

/**
 * Fired during plugin deactivation
 *
 * @link       https://artictempest.es
 * @since      1.0.0
 *
 * @package    Blizzard_Api
 * @subpackage Blizzard_Api/includes
 */
class Blizzard_Api_Deactivator {

    /**
     * Code to run during plugin deactivation.
     *
     * @since    1.0.0
     */
    public static function deactivate() {
        // Delete the transient that caches the Blizzard data
        delete_transient('blizzard_guild_data');
		delete_transient('blizzard_guild_roster_data');

        // Array of options to delete
        $options = array(
            'blizzard_api_client_id',
            'blizzard_api_client_secret',
            'blizzard_api_realm',
            'blizzard_api_guild',
			'blizzard_api_region',
        );

        // Loop through options and delete them
        foreach ($options as $option) {
            delete_option($option);
        }
    }
}
