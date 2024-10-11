<?php

/**
 * Provide an admin area view for the plugin.
 *
 * This file is used to markup the admin-facing aspects of the plugin.
 *
 * @link       https://artictempest.es
 * @since      1.0.0
 *
 * @package    Blizzard_Api
 * @subpackage Blizzard_Api/admin/partials
 */
require_once plugin_dir_path(__DIR__) . '../includes/class-blizzard-api-data.php';
require_once plugin_dir_path(__DIR__) . '../includes/world-of-warcraft/class-blizzard-api-wow.php';
require_once plugin_dir_path(__DIR__) . '../includes/raiderio/class-blizzard-api-raiderio.php';

// Verifica si se ha enviado el formulario.
if (isset($_POST['submit'])) {
    // Verifica los permisos del usuario.
    if (!current_user_can('manage_options')) {
        return;
    }

    // Sanitize y guarda las opciones.
    update_option('blizzard_api_client_id', sanitize_text_field($_POST['client_id']));
    update_option('blizzard_api_client_secret', sanitize_text_field($_POST['client_secret']));
    update_option('blizzard_api_guild', sanitize_title($_POST['guild']));
    update_option('blizzard_api_guild_original', sanitize_text_field($_POST['guild']));
    $region = sanitize_text_field($_POST['region']);
    update_option('blizzard_api_region', $region);
    $realm_slug = sanitize_title($_POST['realm']);
    update_option('blizzard_api_realm', $realm_slug);
    update_option('blizzard_api_realm_original', sanitize_text_field($_POST['realm']));

    // Validar el reino.
    $validation_result = Blizzard_Api_Wow::validate_realm($region, $realm_slug);
    if (is_wp_error($validation_result)) {
        add_settings_error(
            'blizzard_api_messages',
            'blizzard_api_message',
            $validation_result->get_error_message(),
            'error'
        );
    } else {
        add_settings_error(
            'blizzard_api_messages',
            'blizzard_api_message',
            __('Settings saved. Fetching data...', 'blizzard-api'),
            'updated'
        );

        // Guardar el estado inicial.
        update_option('blizzard_api_current_step', 'fetch_guild_data');
    }
}

// Ejecutar el siguiente paso.
$current_step = get_option('blizzard_api_current_step', 'none');
switch ($current_step) {
    case 'fetch_guild_data':
        $guild_data = Blizzard_Api_Wow::get_blizzard_guild_data();
        add_settings_error(
            'blizzard_api_messages',
            'blizzard_api_message',
            __('Guild data fetched. Proceeding to fetch roster data...', 'blizzard-api'),
            'updated'
        );
        update_option('blizzard_api_current_step', 'fetch_roster_data');
        break;

    case 'fetch_roster_data':
        $roster_data = Blizzard_Api_RaiderIO::get_guild_members();
        add_settings_error(
            'blizzard_api_messages',
            'blizzard_api_message',
            __('Roster data fetched. Proceeding to download images...', 'blizzard-api'),
            'updated'
        );
        update_option('blizzard_api_current_step', 'download_avatars');
        break;

    case 'download_avatars':

        // Descargar los avatares de los miembros del roster (solo rangos deseados).
        foreach ($roster_data['members'] as $member) {
            if (in_array($member['rank'], array(0, 1, 2, 4, 5, 6))) {
                $character_info = Blizzard_Api_RaiderIO::get_member_info($realm_slug, $member['character']['name']);
                if (!is_wp_error($character_info) && isset($character_info['local_image_id'])) {
                    update_option('blizzard_member_image_' . $member['character']['name'], $character_info['local_image_id']);
                }
            }
        }
        
        add_settings_error(
            'blizzard_api_messages',
            'blizzard_api_message',
            __('All images downloaded. Setup complete.', 'blizzard-api'),
            'updated'
        );
        update_option('blizzard_api_current_step', 'complete');
        break;

    case 'complete':
        add_settings_error(
            'blizzard_api_messages',
            'blizzard_api_message',
            __('Setup is complete. All data fetched and images downloaded.', 'blizzard-api'),
            'updated'
        );
        break;

    default:
        // No hacer nada si no hay pasos pendientes.
        break;
}

?>

<div class="wrap">
    <form method="post" action="">
        <h1><?php _e('Blizzard API Settings', 'blizzard-api'); ?></h1>
        <?php settings_errors('blizzard_api_messages'); ?>
        
        <h3><?php _e('Data from Blizzard', 'blizzard-api'); ?></h3>
        <p><?php _e('Add the client and secret IDs that you can get from ', 'blizzard-api'); ?>
            <a href="https://develop.battle.net/access/clients" target="_blank">
                <?php _e('Blizzard API Access Client', 'blizzard-api'); ?>
            </a>.
        </p>
        
        <table class="form-table">
            <tr valign="top">
                <th scope="row"><?php _e('Client ID', 'blizzard-api'); ?></th>
                <td><input type="text" name="client_id" value="<?php echo esc_attr(get_option('blizzard_api_client_id')); ?>" required /></td>
            </tr>
            <tr valign="top">
                <th scope="row"><?php _e('Client Secret', 'blizzard-api'); ?></th>
                <td><input type="text" name="client_secret" value="<?php echo esc_attr(get_option('blizzard_api_client_secret')); ?>" required /></td>
            </tr>
        </table>

        <h3><?php _e('Select Blizzard Game', 'blizzard-api'); ?></h3>
        <p><?php _e('Select the Blizzard game you want to configure:', 'blizzard-api'); ?></p>

        <table class="form-table">
            <tr valign="top">
                <th scope="row"><?php _e('Select Game', 'blizzard-api'); ?></th>
                <td>
                    <select name="blizzard_game" id="blizzard-game-select">
                        <option value="wow" <?php selected(get_option('blizzard_api_game'), 'wow'); ?>>World of Warcraft</option>
                        <option value="diablo3" <?php selected(get_option('blizzard_api_game'), 'diablo3'); ?>>Diablo 3</option>
                        <option value="starcraft2" <?php selected(get_option('blizzard_api_game'), 'starcraft2'); ?>>Starcraft 2</option>
                        <option value="hearthstone" <?php selected(get_option('blizzard_api_game'), 'hearthstone'); ?>>Hearthstone</option>
                    </select>
                </td>
            </tr>
        </table>

        <!-- Opciones adicionales dependiendo del juego -->
        <div id="wow-options" style="display:none;">
            <h3><?php _e('World of Warcraft Data', 'blizzard-api'); ?></h3>
            <p><?php _e('Add the next data of your guild:', 'blizzard-api'); ?></p>
            <table class="form-table">
                <tr valign="top">
                    <th scope="row"><?php _e('Region', 'blizzard-api'); ?></th>
                    <td>
                        <select name="region" id="region-select">
                            <option value="us" <?php selected(get_option('blizzard_api_region'), 'us'); ?>>US</option>
                            <option value="eu" <?php selected(get_option('blizzard_api_region'), 'eu'); ?>>EU</option>
                            <option value="kr" <?php selected(get_option('blizzard_api_region'), 'kr'); ?>>KR</option>
                            <option value="cn" <?php selected(get_option('blizzard_api_region'), 'cn'); ?>>CN</option>
                            <option value="tw" <?php selected(get_option('blizzard_api_region'), 'tw'); ?>>TW</option>
                        </select>
                    </td>
                </tr>
                <tr valign="top">
                    <th scope="row"><?php _e('Realm (Enter the name)', 'blizzard-api'); ?></th>
                    <td><input type="text" name="realm" value="<?php echo esc_attr(get_option('blizzard_api_realm_original')); ?>" required /></td>
                </tr>
                <tr valign="top">
                    <th scope="row"><?php _e('Guild', 'blizzard-api'); ?></th>
                    <td><input type="text" name="guild" value="<?php echo esc_attr(get_option('blizzard_api_guild_original')); ?>" required /></td>
                </tr>
            </table>
        </div>

        <?php submit_button(); ?>
    </form>
</div>


<script>
    jQuery(document).ready(function ($) {
        function toggleGameOptions() {
            var selectedGame = jQuery('#blizzard-game-select').val();

            jQuery('#wow-options').hide();
            jQuery('#diablo3-options').hide();
            // Ocultar más opciones de otros juegos si es necesario

            if (selectedGame === 'wow') {
                jQuery('#wow-options').show();
            } else if (selectedGame === 'diablo3') {
                jQuery('#diablo3-options').show();
            }
            // Añadir más condicionales para otros juegos si es necesario
        }

        // Inicializar al cargar la página
        toggleGameOptions();

        // Cambiar las opciones cuando se selecciona un juego
        jQuery('#blizzard-game-select').change(function () {
            toggleGameOptions();
        });
    });

    jQuery('#blizzard-api-save-button').click(function () {
        var formData = jQuery('#blizzard-api-form').serialize();

        jQuery('#blizzard-api-progress-modal').show();
        jQuery('#blizzard-api-progress-message').text('<?php _e('Saving settings...', 'blizzard-api'); ?>');
        jQuery('#blizzard-api-progress-bar').val(0);

        // Hacer la solicitud AJAX para guardar los datos y descargar imágenes.
        jQuery.ajax({
            url: ajaxurl, // URL de la API de admin-ajax.php.
            method: 'POST',
            data: formData + '&action=blizzard_api_save_settings',
            success: function (response) {
                if (response.success) {
                    jQuery('#blizzard-api-progress-message').text(response.data.message);
                    jQuery('#blizzard-api-progress-bar').val(response.data.progress);

                    if (response.data.progress >= 100) {
                        jQuery('#blizzard-api-progress-modal').hide();
                        location.reload();
                    } else {
                        // Realizar la siguiente solicitud para continuar.
                        fetchNextStep(response.data.next_step);
                    }
                } else {
                    alert(response.data.message || '<?php _e('An error occurred.', 'blizzard-api'); ?>');
                    jQuery('#blizzard-api-progress-modal').hide();
                }
            },
            error: function () {
                alert('<?php _e('An error occurred while processing the request.', 'blizzard-api'); ?>');
                jQuery('#blizzard-api-progress-modal').hide();
            }
        });
    });

    function fetchNextStep(nextStep) {
        jQuery.ajax({
            url: ajaxurl,
            method: 'POST',
            data: {
                action: 'blizzard_api_fetch_next_step',
                step: nextStep
            },
            success: function (response) {
                if (response.success) {
                    jQuery('#blizzard-api-progress-message').text(response.data.message);
                    jQuery('#blizzard-api-progress-bar').val(response.data.progress);

                    if (response.data.progress >= 100) {
                        jQuery('#blizzard-api-progress-modal').hide();
                        location.reload();
                    } else {
                        fetchNextStep(response.data.next_step);
                    }
                } else {
                    alert(response.data.message || '<?php _e('An error occurred.', 'blizzard-api'); ?>');
                    jQuery('#blizzard-api-progress-modal').hide();
                }
            }
        });
    }
</script>
