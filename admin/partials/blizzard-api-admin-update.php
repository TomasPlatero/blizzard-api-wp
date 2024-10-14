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
require_once plugin_dir_path( __DIR__ ) . '../includes/class-blizzard-api-data.php';
require_once plugin_dir_path( __DIR__ ) . '../includes/raiderio/class-blizzard-api-raiderio.php';

$transient_updated = false;
$update_message = '';
$warning_message = ''; // Mensaje de advertencia

if ( isset( $_POST['update_guild_roster_data'] ) ) {
    // Ejecutar la función del cron para actualizar datos de los miembros y avatares
    // Blizzard_Api_Cron::blizzard_update_guild_members();
    do_action('blizzard_update_guild_members_cron');

    // Asumir que la actualización se realizó correctamente, puedes añadir más validaciones si es necesario
    $transient_updated = true;
    $update_message = __( 'The roster data and avatars have been successfully updated.', 'blizzard-api' );
}

?>

<!-- Mostrar mensajes de advertencia o éxito -->
<?php if ( $transient_updated ): ?>
    <div class="updated"><p><?php echo esc_html( $update_message ); ?></p></div>
<?php endif; ?>

<?php if ( !empty($warning_message) ): ?>
    <div class="notice notice-warning"><p><?php echo esc_html( $warning_message ); ?></p></div>
<?php endif; ?>

<div class="wrap">
    <h1><?php _e( 'Update Member Data', 'blizzard-api' ); ?></h1>
    <div class="notice notice-info">
        <p><strong><?php _e( 'Note:', 'blizzard-api' ); ?></strong> <?php _e( "Clicking 'Update' will remove the cached member data stored in WordPress for your guild and refresh it.", 'blizzard-api' ); ?></p>
    </div>
    <hr>

    <h2><?php _e( 'Guild Member Data', 'blizzard-api' ); ?></h2>
    <p><?php _e( 'Use the button below to update your guild member data and avatars. This will ensure that the most recent information is reflected on your site.', 'blizzard-api' ); ?></p>
    
    <form method="post" action="" style="margin-top: 20px;">
        <input type="submit" name="update_guild_roster_data" class="button button-primary" value="<?php _e( 'Update Members & Avatars', 'blizzard-api' ); ?>" />
    </form>
</div>
