<?php

/**
 * Provide a admin area view for the plugin
 *
 * This file is used to markup the admin-facing aspects of the plugin.
 *
 * @link       https://artictempest.es
 * @since      1.0.0
 *
 * @package    Blizzard_Api
 * @subpackage Blizzard_Api/admin/partials
 */

if ( isset( $_POST['submit'] ) ) {
    // Verifica los permisos del usuario
    if ( ! current_user_can( 'manage_options' ) ) {
        return;
    }

    // Sanitize and save options
    update_option( 'blizzard_api_client_id', sanitize_text_field( $_POST['client_id'] ) );
    update_option( 'blizzard_api_client_secret', sanitize_text_field( $_POST['client_secret'] ) );
    update_option( 'blizzard_api_realm', sanitize_text_field( $_POST['realm'] ) );
    update_option( 'blizzard_api_guild', sanitize_text_field( $_POST['guild'] ) );
}
 
?>
<div class="wrap">
    <form method="post" action="">
        <h1><?php _e( 'Blizzard API Settings', 'blizzard-api' ); ?></h1>

        <h3><?php _e( 'Data from Blizzard', 'blizzard-api' ); ?></h3>
        <p><?php _e( 'Add the client and secret IDs that you can get from ', 'blizzard-api' ); ?>
            <a href="https://develop.battle.net/access/clients" target="_blank">
                <?php _e( 'Blizzard API Access Client', 'blizzard-api' ); ?>
            </a>.
        </p>
        
        <table class="form-table">
            <tr valign="top">
                <th scope="row"><?php _e( 'Client ID', 'blizzard-api' ); ?></th>
                <td><input type="text" name="client_id" value="<?php echo esc_attr( get_option('blizzard_api_client_id') ); ?>" /></td>
            </tr>
            <tr valign="top">
                <th scope="row"><?php _e( 'Client Secret', 'blizzard-api' ); ?></th>
                <td><input type="text" name="client_secret" value="<?php echo esc_attr( get_option('blizzard_api_client_secret') ); ?>" /></td>
            </tr>
        </table>

        <h3><?php _e( 'World of Warcraft Data', 'blizzard-api' ); ?></h3>
        <p><?php _e( 'Add the next data of your guild:', 'blizzard-api' ); ?></p>
        
        <table class="form-table">
            <tr valign="top">
                <th scope="row"><?php _e( 'Realm', 'blizzard-api' ); ?></th>
                <td><input type="text" name="realm" value="<?php echo esc_attr( get_option('blizzard_api_realm') ); ?>" /></td>
            </tr>
            <tr valign="top">
                <th scope="row"><?php _e( 'Guild', 'blizzard-api' ); ?></th>
                <td><input type="text" name="guild" value="<?php echo esc_attr( get_option('blizzard_api_guild') ); ?>" /></td>
            </tr>
        </table>

        <?php submit_button(); ?>
    </form>
</div>

<!-- This file should primarily consist of HTML with a little bit of PHP. -->
