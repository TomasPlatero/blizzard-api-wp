<?php

/**
 * The admin-specific functionality of the plugin.
 *
 * @link       https://artictempest.es
 * @since      1.0.0
 *
 * @package    Blizzard_Api
 * @subpackage Blizzard_Api/admin
 */

/**
 * The admin-specific functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the admin-specific stylesheet and JavaScript.
 *
 * @package    Blizzard_Api
 * @subpackage Blizzard_Api/admin
 * @author     Zatoshi <admin@artictempest.es>
 */
class Blizzard_Api_Admin {

    /**
     * The ID of this plugin.
     *
     * @since    1.0.0
     * @access   private
     * @var      string    $plugin_name    The ID of this plugin.
     */
    private $plugin_name;

    /**
     * The version of this plugin.
     *
     * @since    1.0.0
     * @access   private
     * @var      string    $version    The current version of this plugin.
     */
    private $version;

    /**
     * Initialize the class and set its properties.
     *
     * @since    1.0.0
     * @param      string    $plugin_name       The name of this plugin.
     * @param      string    $version    The version of this plugin.
     */
    public function __construct( $plugin_name, $version ) {

        $this->plugin_name = $plugin_name;
        $this->version = $version;
        add_action( 'admin_menu', array( $this, 'add_admin_menu' ) );
    }

    /**
     * Register the stylesheets for the admin area.
     *
     * @since    1.0.0
     */
    public function enqueue_styles() {

        wp_enqueue_style( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'css/blizzard-api-admin.css', array(), $this->version, 'all' );
    }

    /**
     * Register the JavaScript for the admin area.
     *
     * @since    1.0.0
     */
    public function enqueue_scripts() {

        wp_enqueue_script( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'js/blizzard-api-admin.js', array( 'jquery' ), $this->version, false );
    }

    public function add_admin_menu() {
        // Menú principal
        add_menu_page(
            __( 'Blizzard API', 'blizzard-api' ),                   // Título de la página
            __( 'Blizzard API', 'blizzard-api' ),                   // Título del menú
            '',                  // Capacidad
            $this->plugin_name,               // Slug del menú
            '', // Función de devolución de llamada
            'dashicons-admin-generic',        // Icono
            80                                 // Posición
        );

        // Submenú para Settings
        add_submenu_page(
            $this->plugin_name,                // Slug del menú padre
            __( 'Settings', 'blizzard-api' ),  // Título de la página
            __( 'Settings', 'blizzard-api' ),  // Título del submenú
            'manage_options',                  // Capacidad
            'blizzard-api-settings',           // Slug del submenú
            array( $this, 'display_settings_page' ) // Función de devolución de llamada
        );

        // Submenú para actualizar el transient
        add_submenu_page(
            $this->plugin_name,                // Slug del menú padre
            __( 'Refresh Data', 'blizzard-api' ), // Título de la página
            __( 'Refresh Data', 'blizzard-api' ), // Título del submenú
            'manage_options',                  // Capacidad
            'blizzard-api-update',             // Slug del submenú
            array( $this, 'display_update_page' ) // Función de devolución de llamada
        );
    }

    public function display_settings_page() {
        include_once plugin_dir_path( dirname( __FILE__ ) ) . 'admin/partials/blizzard-api-admin-display.php';
    }

    public function display_update_page() {
        include_once plugin_dir_path( dirname( __FILE__ ) ) . 'admin/partials/blizzard-api-admin-update.php';
    }
}
