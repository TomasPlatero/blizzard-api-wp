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

class Blizzard_Api_Data {

    /**
     * Retrieve Authorization from Blizzard.
     *
     * @since     1.0.0
     * @return    string|null    The access token or null on error.
     */
    public static function get_blizzard_token() {
        $curl = curl_init();
        $url = "https://oauth.battle.net/token";

        // Client credentials
        $client_id = get_option('blizzard_api_client_id', '0a82dee3741846b19594e57957f5b81f');
        $client_secret = get_option('blizzard_api_client_secret', 'I91hRxX42R2dZ31omkLUf2MX2cSReUcQ');

        // Basic authentication
        $authorization = base64_encode($client_id . ':' . $client_secret);

        curl_setopt_array($curl, array(
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_ENCODING => "",
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => "POST",
            CURLOPT_HTTPHEADER => array(
                'Authorization: Basic ' . $authorization,
                'Content-Type: application/x-www-form-urlencoded'
            ),
            CURLOPT_POSTFIELDS => http_build_query(array(
                'grant_type' => 'client_credentials'
            )),
        ));

        $response = curl_exec($curl);
        $err = curl_error($curl);

        curl_close($curl);

        if ($err) {
            error_log("Error retrieving token: " . $err);
            return null;
        } else {
            $data = json_decode($response, true);
            return isset($data['access_token']) ? $data['access_token'] : null;
        }
    }

    /**
     * Retrieve data from Blizzard.
     *
     * @since     1.0.0
     * @return    array|null    The data received or null on error.
     */
    public static function get_blizzard_guild_data() {
        // Caching key
        $cache_key = 'blizzard_guild_data';
        $cache_duration = 86400; // Cache duration in seconds (24 hour)

        // Check if data is already cached
        $cached_data = get_transient($cache_key);
        if ($cached_data !== false) {
            return $cached_data; // Return cached data if available
        }

        // Get the token
        $token = self::get_blizzard_token();

        // Check if the token is valid
        if (!$token) {
            error_log('Could not retrieve token from Blizzard.');
            return null;
        }

        // Parameters for the URL
        $region = get_option('blizzard_api_region', 'eu');
        $realm = get_option('blizzard_api_realm', 'dun-modr');
        $guild = get_option('blizzard_api_guild', 'artic-tempest');

        // Construct the URL with parameters
        $url = "https://eu.api.blizzard.com/data/wow/guild/{$realm}/{$guild}?namespace=profile-{$region}";

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

        // Handle cURL errors
        if ($err) {
            error_log("Error in request to Blizzard API: " . $err);
            return null;
        }

        // Decode the response if it's JSON
        $data = json_decode($response, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            error_log("Error decoding JSON response: " . json_last_error_msg());
            return null;
        }

        // Cache the data for the specified duration
        set_transient($cache_key, $data, $cache_duration);

        // Return the received data
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
        $token = self::get_blizzard_token();

        // Check if the token is valid
        if (!$token) {
            error_log('Could not retrieve token from Blizzard.');
            return null;
        }

        // Parameters for the URL
        $region = get_option('blizzard_api_region', 'eu');
        $realm = get_option('blizzard_api_realm', 'dun-modr');
        $guild = get_option('blizzard_api_guild', 'artic-tempest');

        // Construct the URL with parameters
        $url = "https://eu.api.blizzard.com/data/wow/guild/{$realm}/{$guild}/roster?namespace=profile-{$region}";

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

        // Handle cURL errors
        if ($err) {
            error_log("Error in request to Blizzard API: " . $err);
            return null;
        }

        // Decode the response if it's JSON
        $data = json_decode($response, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            error_log("Error decoding JSON response: " . json_last_error_msg());
            return null;
        }

        // Cache the data for the specified duration
        set_transient($cache_key, $data, $cache_duration);

        // Return the received data
        return $data;
    }
}
