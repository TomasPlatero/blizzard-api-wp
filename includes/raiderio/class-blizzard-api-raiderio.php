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
     * Validate if the realm exists in Blizzard's API.
     *
     * @since     1.0.0
     * @param     string $region The region of the realm.
     * @param     string $realm_slug The slug of the realm.
     * @return    array|WP_Error    The realm data or a WP_Error on failure.
     */
    public static function get_guild_members() {
        // Caching key
        $cache_key = 'blizzard_guild_roster_data';
        $cache_duration = 86400; // Cache duration in seconds (24 hour)

        $region = get_option('blizzard_api_region');
        $realm = get_option('blizzard_api_realm');
        $guild = rawurlencode(get_option('blizzard_api_guild_original'));
        
        $url="https://raider.io/api/v1/guilds/profile?region={$region}&realm={$realm}&name={$guild}&fields=members";

        $curl = curl_init();
        curl_setopt_array($curl, array(
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HTTPHEADER => array(
                'Content-Type: application/json'
            ),
        ));

        $response = curl_exec($curl);
        $err = curl_error($curl);
        curl_close($curl);

        if ($err) {
            return new WP_Error('api_request_failed', __('Failed to request realm data from RaiderIO.', 'blizzard-api'));
        }

        $data = json_decode($response, true);

        set_transient($cache_key, $data, $cache_duration);

        return $data;
    }
}