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
        require_once plugin_dir_path( __FILE__ ) . '../includes/class-blizzard-api-data.php'; // Asegúrate de que esta ruta sea correcta

        self::save_initial_settings();
        self::get_blizzard_guild_data();
		self::get_blizzard_guild_roster_data();

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
			'region'         => '',
            'realm_original'         => '',
            'guild_original'         => '',
			'region_original'         => '',
        );

        // Save each setting
        foreach ($default_settings as $key => $value) {
            if (get_option('blizzard_api_' . $key) === false) { // Only set if it doesn't exist
                update_option('blizzard_api_' . $key, $value);
            }
        }
    }

    /**
     * Retrieve data from Blizzard.
     *
     * @since     1.0.0
     * @return    array|null    The data received or null on error.
     */
    private static function get_blizzard_guild_data() {
        return Blizzard_Api_Data::get_blizzard_guild_data();
    }

	private static function get_blizzard_guild_roster_data() {
		return Blizzard_Api_Data::get_blizzard_guild_roster_data();
    }
}
