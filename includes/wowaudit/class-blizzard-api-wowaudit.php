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

class Blizzard_Api_WowAudit {

/**
     * Validate if the realm exists in Blizzard's API.
     *
     * @since     1.0.0
     * @param     string $region The region of the realm.
     * @param     string $realm_slug The slug of the realm.
     * @return    array|WP_Error    The realm data or a WP_Error on failure.
     */
    public static function get_applications($region, $realm_slug) {
        $url = "https://wowaudit.com/v1/applications";
        // $token = Blizzard_Api_Data::get_blizzard_token();
        $token = "5edfc9f2f699c210639de785f6ceeedb4dd396831ebc901f5c942685b52794ab";

        if (!$token) {
            return new WP_Error('no_token', __('Unable to retrieve WoWAudit API token.', 'blizzard-api'));
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
            return new WP_Error('api_request_failed', __('Failed to request realm data from WoWAudit.', 'blizzard-api'));
        }

        $data = json_decode($response, true);

        return $data;
    }
}