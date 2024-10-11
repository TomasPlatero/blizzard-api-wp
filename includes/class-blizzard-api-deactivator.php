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
            'blizzard_api_character_name'
        );

        // Loop through options and delete them
        foreach ($options as $option) {
            delete_option($option);
        }

        // Remove class, race, and avatar images from the media library
        self::delete_class_and_race_images();
        self::delete_character_avatar_images();
    }

    /**
     * Delete class and race images from the WordPress media library.
     *
     * @since 1.0.0
     */
    private static function delete_class_and_race_images() {
        // IDs of class and race images stored during activation
        $class_ids = array(1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11, 12, 13); // Add all class IDs here
        $race_ids = array(1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11, 22, 24, 27, 28, 29, 30, 31, 34, 36, 52, 70, 85); // Add all race IDs here, including 85 for Earthen

        // Delete class images
        foreach ($class_ids as $class_id) {
            $attachment_id = get_option('blizzard_class_image_' . $class_id);
            if ($attachment_id) {
                wp_delete_attachment($attachment_id, true); // True to remove the file from the server
                delete_option('blizzard_class_image_' . $class_id); // Clean up the option
            }
        }

        // Delete race images
        foreach ($race_ids as $race_id) {
            $attachment_id = get_option('blizzard_race_image_' . $race_id);
            if ($attachment_id) {
                wp_delete_attachment($attachment_id, true); // True to remove the file from the server
                delete_option('blizzard_race_image_' . $race_id); // Clean up the option
            }
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
