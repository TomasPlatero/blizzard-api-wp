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
require_once plugin_dir_path(__DIR__) . '../includes/world-of-warcraft/class-blizzard-api-wow.php';

// Verifica los permisos del usuario
if (!current_user_can('manage_options')) {
    return;
}

// Obtener datos de la API
$roster_data = get_transient( "blizzard_guild_roster_data" );

echo '<div class="wrap">';
echo '<h1>' . __('Blizzard API JSON Viewer', 'blizzard-api') . '</h1>';

// Mostrar datos del roster de la hermandad
echo '<h2>' . __('Guild Roster Data', 'blizzard-api') . '</h2>';
if ($roster_data !== null) {
    echo '<pre><code class="json">' . json_encode($roster_data, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) . '</code></pre>';
} else {
    echo '<p>' . __('No data available for guild roster.', 'blizzard-api') . '</p>';
}

echo '</div>';

?>
