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

class Blizzard_Api_Activator {

    /**
     * Activate the plugin.
     *
     * @since 1.0.0
     */
    public static function activate() {
        require_once plugin_dir_path( __FILE__ ) . '../includes/class-blizzard-api-data.php';
        require_once plugin_dir_path( __FILE__ ) . '../includes/world-of-warcraft/class-blizzard-api-wow.php';
        require_once plugin_dir_path( __FILE__ ) . '../includes/raiderio/class-blizzard-api-raiderio.php';

        self::save_initial_settings();

        // Programar el cron job para actualizar transients de la hermandad cada 12 horas
        if (!wp_next_scheduled('blizzard_update_guild_members_cron')) {
            wp_schedule_event(time(), 'twicedaily', 'blizzard_update_guild_members_cron');
        }
    }

    /**
     * Save initial settings to the database.
     *
     * @since 1.0.0
     */
    private static function save_initial_settings() {
        $default_settings = array(
            'client_id'     => '0a82dee3741846b19594e57957f5b81f',
            'client_secret' => 'I91hRxX42R2dZ31omkLUf2MX2cSReUcQ',
            'realm'         => '',
            'guild'         => '',
            'region'        => '',
            'realm_original' => '',
            'guild_original' => ''
        );

        foreach ($default_settings as $key => $value) {
            add_option('blizzard_api_' . $key, $value); 
        }
    }
}
