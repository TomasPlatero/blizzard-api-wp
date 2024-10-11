<?php

/**
 * Fired during plugin activation
 *
 * @link       https://artictempest.es
 * @since      1.0.0
 *
 * @package    Blizzard_Api
 * @subpackage Blizzard_Api/includes
 */

/**
 * Class that handles plugin activation.
 *
 * @since      1.0.0
 * @package    Blizzard_Api
 * @subpackage Blizzard_Api/includes
 */
class Blizzard_Api_Activator {

    /**
     * Activate the plugin.
     *
     * @since 1.0.0
     */
    public static function activate() {
        require_once plugin_dir_path( __FILE__ ) . '../includes/class-blizzard-api-data.php';
        require_once plugin_dir_path( __FILE__ ) . '../includes/world-of-warcraft/class-blizzard-api-wow.php';

        self::save_initial_settings();

    }

    /**
     * Save initial settings to the database.
     *
     * @since 1.0.0
     */
    private static function save_initial_settings() {
        // Default values
        $default_settings = array(
            'client_id'     => '0a82dee3741846b19594e57957f5b81f',
            'client_secret' => 'I91hRxX42R2dZ31omkLUf2MX2cSReUcQ',
            'realm'         => '',
            'guild'         => '',
            'region'        => '',
            'realm_original' => '',
            'guild_original' => '',
            'games_original' => array('World of Warcraft', 'Diablo 3', 'Hearthstone', 'Starcraft 2'),
            'games_slug'    => array('wow', 'd3', 'hearthstone', 'sc2')
        );

        // Save each setting
        foreach ($default_settings as $key => $value) {
            if (get_option('blizzard_api_' . $key) === false) { // Only set if it doesn't exist
                update_option('blizzard_api_' . $key, $value);
            }
        }
    }
}
