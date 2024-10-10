<?php
require_once plugin_dir_path( __DIR__ ) . '../includes/class-blizzard-api-data.php';

/**
 * Handles the interaction with the Blizzard API.
 *
 * @link       https://artictempest.es
 * @since      1.0.0
 *
 * @package    Blizzard_Api
 * @subpackage Blizzard_Api/includes
 */

class Blizzard_Api_Wow {

/**
     * Validate if the realm exists in Blizzard's API.
     *
     * @since     1.0.0
     * @param     string $region The region of the realm.
     * @param     string $realm_slug The slug of the realm.
     * @return    array|WP_Error    The realm data or a WP_Error on failure.
     */
    public static function validate_realm($region, $realm_slug) {
        $url = "https://{$region}.api.blizzard.com/data/wow/realm/{$realm_slug}?namespace=dynamic-{$region}";
        $token = Blizzard_Api_Data::get_blizzard_token();

        if (!$token) {
            return new WP_Error('no_token', __('Unable to retrieve Blizzard API token.', 'blizzard-api'));
        }

        $curl = curl_init();
        curl_setopt_array($curl, array(
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HTTPHEADER => array(
                'Authorization: Bearer ' . $token,
                'Content-Type: application/json'
            ),
        ));

        $response = curl_exec($curl);
        $err = curl_error($curl);
        curl_close($curl);

        if ($err) {
            return new WP_Error('api_request_failed', __('Failed to request realm data from Blizzard.', 'blizzard-api'));
        }

        $data = json_decode($response, true);

        if (json_last_error() !== JSON_ERROR_NONE || empty($data) || !isset($data['name'])) {
            return new WP_Error('invalid_realm', __('Invalid realm provided.', 'blizzard-api'));
        }

        return $data; // Return the valid realm data
    }

    /**
     * Retrieve Authorization from Blizzard.
     *
     * @since     1.0.0
     * @return    string|null    The access token or null on error.
     */
    public static function get_blizzard_guild_data() {
        $cache_key = 'blizzard_guild_data';
        $cache_duration = 86400;

        $token = Blizzard_Api_Data::get_blizzard_token();

        if (!$token) {
            error_log('Could not retrieve token from Blizzard.');
            return null;
        }

        $region = get_option('blizzard_api_region');
        $realm = get_option('blizzard_api_realm');
        $guild = get_option('blizzard_api_guild');

        $url = "https://{$region}.api.blizzard.com/data/wow/guild/{$realm}/{$guild}?namespace=profile-{$region}";

        $curl = curl_init();
        curl_setopt_array($curl, array(
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTPHEADER => array(
                'Authorization: Bearer ' . $token,
                'Content-Type: application/json'
            ),
        ));

        $response = curl_exec($curl);
        $err = curl_error($curl);
        curl_close($curl);

        if ($err) {
            error_log("Error in request to Blizzard API: " . $err);
            return null;
        }

        $data = json_decode($response, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            error_log("Error decoding JSON response: " . json_last_error_msg());
            return null;
        }

        if (empty($data)) {
            error_log("Warning: Blizzard API returned an empty response.");
            return null;
        }

        set_transient($cache_key, $data, $cache_duration);
        return $data;
    }

    /**
     * Retrieve data from Blizzard.
     *
     * @since     1.0.0
     * @return    array|null    The data received or null on error.
     */
    public static function get_blizzard_guild_roster_data() {
        // Caching key
        $cache_key = 'blizzard_guild_roster_data';
        $cache_duration = 86400; // Cache duration in seconds (24 hour)

        // Check if data is already cached
        $cached_data = get_transient($cache_key);
        if ($cached_data !== false) {
            return $cached_data; // Return cached data if available
        }

        // Get the token
        $token = Blizzard_Api_Data::get_blizzard_token();

        // Check if the token is valid
        if (!$token) {
            error_log('Could not retrieve token from Blizzard.');
            return null;
        }

        // Parameters for the URL
        $region = get_option('blizzard_api_region');
        $realm = get_option('blizzard_api_realm');
        $guild = get_option('blizzard_api_guild');

        // Construct the URL with parameters
        $url = "https://{$region}.api.blizzard.com/data/wow/guild/{$realm}/{$guild}/roster?namespace=profile-{$region}";

        // Initialize cURL
        $curl = curl_init();

        curl_setopt_array($curl, array(
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_ENCODING => "",
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => "GET",
            CURLOPT_HTTPHEADER => array(
                'Authorization: Bearer ' . $token,
                'Content-Type: application/json'
            ),
        ));

        $response = curl_exec($curl);
        $err = curl_error($curl);
        curl_close($curl);

        if ($err) {
            error_log("Error in request to Blizzard API: " . $err);
            return null;
        }

        $data = json_decode($response, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            error_log("Error decoding JSON response: " . json_last_error_msg());
            return null;
        }

        if (empty($data)) {
            error_log("Warning: Blizzard API returned an empty response.");
            return null; // You can return null or an empty array depending on how you want to handle this
        }

        set_transient($cache_key, $data, $cache_duration);
        return $data;
    }
}