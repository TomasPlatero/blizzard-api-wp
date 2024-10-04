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

if (isset($_POST['submit'])) {
    // Verifica los permisos del usuario
    if (!current_user_can('manage_options')) {
        return;
    }

    // Sanitize and save options
    update_option('blizzard_api_client_id', sanitize_text_field($_POST['client_id']));
    update_option('blizzard_api_client_secret', sanitize_text_field($_POST['client_secret']));

    // Obtén el valor del reino y la región
    $region = sanitize_text_field($_POST['region']);
    $realm_slug = sanitize_title($_POST['realm']);

    // Llama a la función de validación
    $validation_result = Blizzard_Api_Data::validate_realm($region, $realm_slug);

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
?>

<div class="wrap">
    <form id="blizzard-api-form" method="post" action="">
        <h1><?php _e('Blizzard API Settings', 'blizzard-api'); ?></h1>

        <?php
        // Display settings errors, including our custom message
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
        <?php submit_button(); ?>
    </form>
</div>
