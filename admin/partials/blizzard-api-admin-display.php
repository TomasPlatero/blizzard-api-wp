<?php

/**
 * Provide an admin area view for the plugin
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

if (isset($_POST['submit'])) {
    // Verifica los permisos del usuario
    if (!current_user_can('manage_options')) {
        return;
    }

    // Sanitize and save options
    update_option('blizzard_api_client_id', sanitize_text_field($_POST['client_id']));
    update_option('blizzard_api_client_secret', sanitize_text_field($_POST['client_secret']));
    update_option('blizzard_api_guild', sanitize_title($_POST['guild']));

    // Obtén el valor del reino y la región
    $region = sanitize_text_field($_POST['region']);
    $realm_slug = sanitize_title($_POST['realm']);

    // Llama a la función de validación
    $validation_result = Blizzard_Api_Wow::validate_realm($region, $realm_slug);

    if (is_wp_error($validation_result)) {
        add_settings_error(
            'blizzard_api_messages',
            'blizzard_api_message',
            $validation_result->get_error_message(),
            'error'
        );
    } else {
        // Si el reino es válido, guarda los datos
        if (update_option('blizzard_api_realm_original', $_POST['realm'])) {
            error_log('Realm original guardado: ' . $_POST['realm']);
        } else {
            error_log('Error guardando el reino.');
        }
        
        update_option('blizzard_api_realm', $realm_slug); // Slug del reino

        // Agrega mensaje de éxito
        add_settings_error(
            'blizzard_api_messages',
            'blizzard_api_message',
            __('Settings saved. You may need to refresh the data.', 'blizzard-api'),
            'updated'
        );
    }
}

// Comprobar si el formulario ha sido enviado y si existe la clave 'blizzard_game'
if (isset($_POST['blizzard_game'])) {
    update_option('blizzard_api_game', sanitize_text_field($_POST['blizzard_game']));

    // Guardar otras opciones dependiendo del juego seleccionado
    if ($_POST['blizzard_game'] === 'wow' && isset($_POST['region'])) {
        update_option('blizzard_api_region', sanitize_text_field($_POST['region']));
    } elseif ($_POST['blizzard_game'] === 'diablo3' && isset($_POST['character_name'])) {
        update_option('blizzard_api_character_name', sanitize_text_field($_POST['character_name']));
    }
}

?>
<div class="wrap">
    <form id="blizzard-api-form" method="post" action="">
        <h1><?php _e('Blizzard API Settings', 'blizzard-api'); ?></h1>

        <?php
        // Mostrar los errores de configuración, incluidos los mensajes personalizados
        settings_errors('blizzard_api_messages');
        ?>

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

        <!-- Nueva sección para seleccionar el tipo de juego -->
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
                    <td><input type="text" name="realm" id="realm-input" value="<?php echo esc_attr(get_option('blizzard_api_realm_original')); ?>" required /></td>
                </tr>
                <tr valign="top">
                    <th scope="row"><?php _e('Guild', 'blizzard-api'); ?></th>
                    <td><input type="text" name="guild" value="<?php echo esc_attr(get_option('blizzard_api_guild_original')); ?>" required /></td>
                </tr>
            </table>
        </div>

        <div id="diablo3-options" style="display:none;">
            <h3><?php _e('Diablo 3 Data', 'blizzard-api'); ?></h3>
            <table class="form-table">
                <tr valign="top">
                    <th scope="row"><?php _e('Character Name', 'blizzard-api'); ?></th>
                    <td><input type="text" name="character_name" value="<?php echo esc_attr(get_option('blizzard_api_character_name')); ?>" /></td>
                </tr>
            </table>
        </div>

        <!-- Añadir más bloques de opciones según el juego si es necesario -->

        <?php submit_button(); ?>
    </form>
</div>

<script>
    jQuery(document).ready(function ($) {
        function toggleGameOptions() {
            var selectedGame = $('#blizzard-game-select').val();

            $('#wow-options').hide();
            $('#diablo3-options').hide();
            // Ocultar más opciones de otros juegos si es necesario

            if (selectedGame === 'wow') {
                $('#wow-options').show();
            } else if (selectedGame === 'diablo3') {
                $('#diablo3-options').show();
            }
            // Añadir más condicionales para otros juegos si es necesario
        }

        // Inicializar al cargar la página
        toggleGameOptions();

        // Cambiar las opciones cuando se selecciona un juego
        $('#blizzard-game-select').change(function () {
            toggleGameOptions();
        });
    });
</script>
