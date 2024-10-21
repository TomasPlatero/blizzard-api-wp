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
     * @since 1.0.0
     */
    public static function deactivate() {
        require_once plugin_dir_path( __FILE__ ) . '../includes/raiderio/class-blizzard-api-raiderio.php';
        
        // Array of options to delete
        $options = array(
            'blizzard_api_client_id',
            'blizzard_api_client_secret',
            'blizzard_api_realm',
            'blizzard_api_guild',
            'blizzard_api_region',
            'blizzard_api_realm_original',
            'blizzard_api_guild_original',
            'blizzard_guild_roster_data',
            'blizzard_guild_members',
        );

        // Loop through options and delete them
        foreach ($options as $option) {
            delete_option($option);
        }

        // Remove scheduled CRON events for updating guild members
        $timestamp = wp_next_scheduled('blizzard_update_guild_members_cron');
        if ($timestamp) {
            wp_unschedule_event($timestamp, 'blizzard_update_guild_members_cron');
        }

        // Delete only avatar images in the assets/images directory
        $images_dir = get_stylesheet_directory() . '/assets/images';

        if (is_dir($images_dir)) {
            // Use a pattern to identify avatar files, for example files with names that start with "avatar_" or any specific pattern
            $avatar_files = glob($images_dir . '/Avatar_*.jpg'); // Example pattern: "avatar_" prefix with .jpg extension

            // Loop through and delete each avatar file
            foreach ($avatar_files as $file) {
                if (is_file($file)) {
                    unlink($file); // Delete the file
                }
            }
        }
    }
}
