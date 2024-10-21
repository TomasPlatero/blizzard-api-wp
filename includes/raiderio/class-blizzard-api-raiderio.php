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
     * Retrieve guild members from RaiderIO and store the data in the database.
     *
     * @since 1.0.0
     * @return array|WP_Error The guild member data or a WP_Error on failure.
     */
    public static function get_guild_members() {
        $region = get_option('blizzard_api_region');
        $realm = get_option('blizzard_api_realm');
        $guild = rawurlencode(get_option('blizzard_api_guild_original'));
        
        $url = "https://raider.io/api/v1/guilds/profile?region={$region}&realm={$realm}&name={$guild}&fields=members";
    
        // Hacer la solicitud a la API de Raider.IO.
        $response = wp_remote_get($url);
        if (is_wp_error($response)) {
            return $response;
        }
    
        $body = wp_remote_retrieve_body($response);
        $data = json_decode($body, true);
    
        // Filtrar los miembros de Raider.IO por rangos permitidos
        $allowed_ranks = array(0, 1, 2, 4, 5, 6);
        $filtered_members = array();
    
        if (isset($data['members']) && is_array($data['members']) && !empty($data['members'])) {
            foreach ($data['members'] as $member) {
                if (isset($member['character']['name']) && in_array($member['rank'], $allowed_ranks)) {
                    // Solo agregamos los miembros permitidos basados en los rangos permitidos
                    $filtered_members[] = $member;
                }
            }
        }
    
        // Guardar los miembros filtrados de Raider.IO en el option 'blizzard_guild_raiderio'
        update_option('blizzard_guild_raiderio', $filtered_members);
    
        // Obtener los datos actuales de los miembros (o inicializar como un array vacío)
        $saved_members_data = get_option('blizzard_guild_members', array());
    
        // Procesar los miembros filtrados para comparar y actualizar datos
        foreach ($filtered_members as $member) {
            $member_name = $member['character']['name'];

            // Obtener la información adicional del miembro desde RaiderIO
            $member_info = self::get_member_info($member['character']['realm'], $member_name);
    
            // Si no hubo errores al obtener la información del miembro
            if (!is_wp_error($member_info)) {
                // Verificar si hay cambios en los datos (rango, clase, raza, imagen)
                $updated = false;
                if (isset($saved_members_data[$member_name])) {
                    $existing_member = $saved_members_data[$member_name];

                    // Comprobar si hay cambios en rango, clase, raza o imagen
                    if ($existing_member['rank'] != $member['rank']) {
                        $saved_members_data[$member_name]['rank'] = $member['rank'];
                        $updated = true;
                    }
                    if ($existing_member['class'] != $member['character']['class']) {
                        $saved_members_data[$member_name]['class'] = $member['character']['class'];
                        $updated = true;
                    }
                    if ($existing_member['race'] != $member['character']['race']) {
                        $saved_members_data[$member_name]['race'] = $member['character']['race'];
                        $updated = true;
                    }
                    if (isset($member_info['thumbnail_url']) && $existing_member['thumbnail_url'] != $member_info['thumbnail_url']) {
                        $thumbnail_path = self::download_image_locally($member_info['thumbnail_url'], $member_name);
                        $saved_members_data[$member_name]['thumbnail_url'] = $thumbnail_path;
                        $updated = true;
                    }
                } else {
                    // Si el miembro no está en los datos guardados, agregarlo
                    $updated = true;
                }
    
                // Si hubo cambios o el miembro es nuevo, actualizar los datos
                if ($updated) {
                    $member_data = array(
                        'name' => $member_name,
                        'realm' => $member['character']['realm'],
                        'rank' => $member['rank'],
                        'race' => $member['character']['race'],
                        'class' => $member['character']['class'],
                        'gender' => $member['character']['gender'],
                        'thumbnail_url' => isset($member_info['thumbnail_url']) ? $thumbnail_path : null
                    );
    
                    // Guardar o actualizar los datos del miembro en el array de miembros
                    $saved_members_data[$member_name] = $member_data;
                }
            }
        }
    
        // Guardar los datos procesados de los miembros en el option 'blizzard_guild_members'
        update_option('blizzard_guild_members', $saved_members_data);
    
        // Retornar los datos actuales procesados
        return $saved_members_data;
    }

    /**
     * Descargar la imagen desde la URL y guardarla en el directorio del theme.
     *
     * @since 1.0.0
     * @param string $image_url URL de la imagen a descargar.
     * @param string $member_name Nombre del miembro (para nombrar la imagen).
     * @return string La ruta completa de la imagen guardada.
     */
    public static function download_image_locally($image_url, $member_name) {
        // Directorio donde se guardarán las imágenes
        $upload_dir = get_stylesheet_directory() . '/assets/images';

        // Crear el directorio si no existe
        if (!file_exists($upload_dir)) {
            wp_mkdir_p($upload_dir);
        }

        // Nombre del archivo
        $filename = 'Avatar_' . sanitize_file_name($member_name) . '.jpg';
        $file_path = $upload_dir . '/' . $filename;

        // Descargar la imagen desde la URL
        $response = wp_remote_get($image_url, array('timeout' => 10));
        if (is_wp_error($response)) {
            return '';
        }

        // Guardar el contenido de la imagen en un archivo local
        $image_data = wp_remote_retrieve_body($response);
        if (!empty($image_data)) {
            file_put_contents($file_path, $image_data);
        }

        // Devolver la ruta relativa al tema para usar en las plantillas
        return get_stylesheet_directory_uri() . '/assets/images/' . $filename;
    }

    /**
     * Retrieve information about a specific member from RaiderIO and return the data.
     *
     * @since 1.0.0
     * @param string $realm The realm of the member.
     * @param string $member The name of the member.
     * @return array|WP_Error The member data or a WP_Error on failure.
     */
    public static function get_member_info($realm, $member) {
        $region = get_option('blizzard_api_region');
        $realm = sanitize_title($realm);
        $member = rawurlencode($member);

        $url = "https://raider.io/api/v1/characters/profile?region={$region}&realm={$realm}&name={$member}";

        // Hacer la solicitud a la API de Raider.IO.
        $response = wp_remote_get($url);
        if (is_wp_error($response)) {
            return $response;
        }

        $body = wp_remote_retrieve_body($response);
        $data = json_decode($body, true);

        // Retornar los datos del miembro obtenidos de Raider.IO
        return $data;
    }

    /**
     * Retrieve the raid progression data for a guild from Raider.IO.
     *
     * @param string $region The region where the guild is located (e.g., 'us', 'eu').
     * @param string $realm The name of the realm where the guild is located (e.g., 'stormrage').
     * @param string $guild_slug The URL-friendly version (slug) of the guild name (e.g., 'my-guild').
     *
     * @return array|WP_Error The decoded raid progression data from Raider.IO on success, or a WP_Error on failure.
     */
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
