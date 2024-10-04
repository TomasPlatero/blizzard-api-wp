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

$transient_updated = false; // Variable para verificar si se ha actualizado el transient
$update_message = ''; // Mensaje de actualización

if ( isset( $_POST['update_guild_data'] ) ) {
    // Lógica para actualizar el transient de la hermandad
    Blizzard_Api_Data::get_blizzard_guild_data(); // Llama a la función que actualiza los datos
    $transient_updated = true; // Establecer la variable a true si se actualiza
    $update_message = __( 'The guild data has been successfully updated.', 'blizzard-api' ); // Mensaje para hermandad
}

if ( isset( $_POST['update_guild_roster_data'] ) ) {
    // Lógica para actualizar el transient del roster
    Blizzard_Api_Data::get_blizzard_guild_roster_data(); // Llama a la función que actualiza los datos
    $transient_updated = true; // Establecer la variable a true si se actualiza
    $update_message = __( 'The roster data has been successfully updated.', 'blizzard-api' ); // Mensaje para roster
}

?>

<?php if ( $transient_updated ): ?>
    <div class="updated"><p><?php echo esc_html( $update_message ); ?></p></div>
<?php endif; ?>

<div class="wrap">
    <h1><?php _e( 'Update Data', 'blizzard-api' ); ?></h1>
    <div class="notice notice-info">
        <p><strong><?php _e( 'Note:', 'blizzard-api' ); ?></strong> <?php _e( 'Clicking "Update" will remove the cached data stored in WordPress for your clan across all options.', 'blizzard-api' ); ?></p>
    </div>
    <hr>

    <h2><?php _e( 'General Guild Data', 'blizzard-api' ); ?></h2>
    <p><?php _e( 'Use the button below to update your guild data. This will ensure that the most recent information is reflected on your site.', 'blizzard-api' ); ?></p>
    
    <form method="post" action="" style="margin-top: 20px;">
        <input type="submit" name="update_guild_data" class="button button-primary" value="<?php _e( 'Update', 'blizzard-api' ); ?>" />
    </form>

    <h2><?php _e( 'Guild Member Data', 'blizzard-api' ); ?></h2>
    <p><?php _e( 'Use the button below to update your guild member data. This will ensure that the most recent information is reflected on your site.', 'blizzard-api' ); ?></p>
    
    <form method="post" action="" style="margin-top: 20px;º">
        <input type="submit" name="update_guild_roster_data" class="button button-primary" value="<?php _e( 'Update', 'blizzard-api' ); ?>" />
    </form>
</div>
