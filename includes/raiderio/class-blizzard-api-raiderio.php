<?php

/**
 * Handles the interaction with the Blizzard API.
 *
 * @link       https://artictempest.es
 * @since      1.0.0
 *
 * @package    Blizzard_Api
 * @subpackage Blizzard_Api/includes
 */

class Blizzard_Api_RaiderIO {

    /**
     * Retrieve guild members from RaiderIO and update the transient if data changes.
     *
     * @since 1.0.0
     * @return array|WP_Error The guild member data or a WP_Error on failure.
     */
    public static function get_guild_members() {
        $cache_key = 'blizzard_guild_roster_data';
        $cache_duration = 86400; // Cache for 24 hours

        $region = get_option('blizzard_api_region');
        $realm = get_option('blizzard_api_realm');
        $guild = rawurlencode(get_option('blizzard_api_guild_original'));
        
        $url = "https://raider.io/api/v1/guilds/profile?region={$region}&realm={$realm}&name={$guild}&fields=members";

        // Make the request to the Raider.IO API.
        $response = wp_remote_get($url);
        if (is_wp_error($response)) {
            return $response;
        }

        $body = wp_remote_retrieve_body($response);
        $data = json_decode($body, true);

        // Retrieve the current cached data.
        $cached_data = get_transient($cache_key);

        // Compare the new data with the cached data.
        if ($cached_data !== $data) {
            // If the data is different, update the transient.
            set_transient($cache_key, $data, $cache_duration);
        }

        // Return the current data (either new or cached).
        return $data;
    }

    /**
     * Retrieve information about a specific member from RaiderIO.
     *
     * @since 1.0.0
     * @param string $realm The realm of the member.
     * @param string $member The name of the member.
     * @return array|WP_Error The member data or a WP_Error on failure.
     */
public static function get_member_info($realm, $member) {
    $region = get_option('blizzard_api_region');
    $cache_key = "raiderio_member_{$member}";
    $cache_duration = 86400; // Cache duration in seconds (24 hours)
    $realm = sanitize_title($realm);
    $member = rawurlencode($member);

    $url = "https://raider.io/api/v1/characters/profile?region={$region}&realm={$realm}&name={$member}";

    $response = wp_remote_get($url);
    if (is_wp_error($response)) {
        return $response;
    }

    $body = wp_remote_retrieve_body($response);
    $data = json_decode($body, true);

    // Obtener datos en caché
    $cached_data = get_transient($cache_key);

    // Reutilizar el local_image_id si ya existe y la URL de la imagen es la misma.
    if (isset($cached_data['local_image_id']) && isset($cached_data['thumbnail_url']) && $cached_data['thumbnail_url'] === $data['thumbnail_url']) {
        $data['local_image_id'] = $cached_data['local_image_id'];
    } else {
        // De lo contrario, guarda la nueva imagen localmente y actualiza el ID.
        if (isset($data['thumbnail_url'])) {
            $image_url = esc_url($data['thumbnail_url']);
            $image_id = self::save_image_locally($image_url, $member);
            $data['local_image_id'] = $image_id;
        }
    }

    // Actualizar el transient solo si los datos han cambiado.
    if (serialize($cached_data) !== serialize($data)) {
        set_transient($cache_key, $data, $cache_duration);
    }

    return $data;
}


    /**
     * Save an image locally and add it to the WordPress media library.
     *
     * @since 1.0.0
     * @param string $image_url The URL of the image to download.
     * @param string $member_name The name of the member for naming the image file.
     * @return int|false The attachment ID of the saved image, or false on failure.
     */
    public static function save_image_locally($image_url, $member_name) {
        $response = wp_remote_get($image_url, array('timeout' => 10));

        if (is_wp_error($response)) {
            return false;
        }

        $image_data = wp_remote_retrieve_body($response);
        if (empty($image_data)) {
            return false;
        }

        // Set the filename for the image.
        $filename = sanitize_file_name($member_name) . '.jpg';
        $upload_dir = wp_upload_dir();

        // Determine the file path.
        $file_path = $upload_dir['path'] . '/' . $filename;

        // Save the image to the local filesystem.
        file_put_contents($file_path, $image_data);

        // Prepare the file for WordPress media library.
        $file_type = wp_check_filetype($filename, null);
        $attachment = array(
            'post_mime_type' => $file_type['type'],
            'post_title'     => sanitize_file_name($filename),
            'post_content'   => '',
            'post_status'    => 'inherit',
        );

        // Insert the attachment into the WordPress media library.
        $attachment_id = wp_insert_attachment($attachment, $file_path);
        require_once(ABSPATH . 'wp-admin/includes/image.php');
        $attach_data = wp_generate_attachment_metadata($attachment_id, $file_path);
        wp_update_attachment_metadata($attachment_id, $attach_data);

        return $attachment_id;
    }

    public static function get_guild_raid_progress($region, $realm, $guild_slug) {
        $url = "https://raider.io/api/v1/guilds/profile?region=$region&realm=$realm&name=$guild_slug&fields=raid_progression";
    
        $response = wp_remote_get($url);
    
        if (is_wp_error($response)) {
            return $response;
        }
    
        $body = wp_remote_retrieve_body($response);
        return json_decode($body, true);
    }
    
}
