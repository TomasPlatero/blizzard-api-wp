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

        // Delete the transients that cache the Blizzard data
        delete_transient('blizzard_guild_data');
        delete_transient('blizzard_guild_roster_data');

        // Array of options to delete
        $options = array(
            'blizzard_api_client_id',
            'blizzard_api_client_secret',
            'blizzard_api_realm',
            'blizzard_api_guild',
            'blizzard_api_region',
            'blizzard_api_realm_original',
            'blizzard_api_guild_original',
            'blizzard_api_games_original',
            'blizzard_api_games_slug',
            'blizzard_api_game',
            'blizzard_api_character_name',
            'blizzard_api_current_step'
        );

        // Loop through options and delete them
        foreach ($options as $option) {
            delete_option($option);
        }

        // Remove class, race, and avatar images from the media library
        self::delete_character_avatar_images();

        // Remove scheduled cron event
        $timestamp = wp_next_scheduled('blizzard_update_guild_members_cron');
        if ($timestamp) {
            wp_unschedule_event($timestamp, 'blizzard_update_guild_members_cron');
        }
    }

    /**
     * Delete character avatar images from the WordPress media library.
     *
     * @since 1.0.0
     */
    private static function delete_character_avatar_images() {
        global $wpdb;

        // Get all transient keys related to character avatars
        $avatar_transients = $wpdb->get_col(
            $wpdb->prepare(
                "SELECT option_name FROM {$wpdb->options} WHERE option_name LIKE %s",
                '_transient_raiderio_member_%'
            )
        );

        // Loop through each transient and delete the associated image
        foreach ($avatar_transients as $transient_key) {
            $member_name = str_replace('_transient_raiderio_member_', '', $transient_key);
            $cached_data = get_transient('raiderio_member_' . $member_name);

            if (isset($cached_data['local_image_id'])) {
                wp_delete_attachment($cached_data['local_image_id'], true);
            }

            // Delete the transient
            delete_transient('raiderio_member_' . $member_name);
        }
    }
}
