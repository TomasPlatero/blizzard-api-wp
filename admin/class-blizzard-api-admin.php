<?php
require_once plugin_dir_path( __DIR__ ) . 'includes/class-blizzard-api-data.php';

/**
 * The admin-specific functionality of the plugin.
 *
 * @link       https://artictempest.es
 * @since      1.0.0
 *
 * @package    Blizzard_Api
 * @subpackage Blizzard_Api/admin
 */

class Blizzard_Api_Admin {

    private $plugin_name;
    private $version;

    public function __construct( $plugin_name, $version ) {
        $this->plugin_name = $plugin_name;
        $this->version = $version;

        add_action( 'admin_menu', array( $this, 'add_admin_menu' ) );
        add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_styles' ) );
        add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_scripts' ) );
    }

	public function enqueue_styles() {
		wp_enqueue_style( 
			$this->plugin_name, 
			plugin_dir_url( __FILE__ ) . 'css/blizzard-api-admin.css', 
			array(), 
			$this->version, 
			'all' 
		);
	
		// Enqueue Highlight.js stylesheet
		wp_enqueue_style( 
			'highlight-js', 
			'https://cdnjs.cloudflare.com/ajax/libs/highlight.js/11.7.0/styles/stackoverflow-light.min.css', // Cambia 'default.min.css' por el tema que prefieras
			array(), 
			'11.7.0', 
			'all' 
		);
	}
	
	public function enqueue_scripts() {
		wp_enqueue_script( 
			$this->plugin_name, 
			plugin_dir_url( __FILE__ ) . 'js/blizzard-api-admin.js', 
			array( 'jquery' ), 
			$this->version, 
			false 
		);
	
		// Enqueue Highlight.js script
		wp_enqueue_script( 
			'highlight-js', 
			'https://cdnjs.cloudflare.com/ajax/libs/highlight.js/11.7.0/highlight.min.js', 
			array(), 
			'11.7.0', 
			true // Carga en el footer
		);
	
		// Inicializa Highlight.js después de que se carga
		wp_add_inline_script( 
			'highlight-js', 
			'hljs.highlightAll();' 
		);
	}
	

    public function add_admin_menu() {
        add_menu_page(
            __( 'Blizzard API', 'blizzard-api' ),
            __( 'Blizzard API', 'blizzard-api' ),
            '',
            $this->plugin_name,
            array( $this, 'display_settings_page' ),
            'dashicons-admin-generic',
            80
        );

        add_submenu_page(
            $this->plugin_name,
            __( 'Settings', 'blizzard-api' ),
            __( 'Settings', 'blizzard-api' ),
            'manage_options',
            'blizzard-api-settings',
            array( $this, 'display_settings_page' )
        );

        add_submenu_page(
            $this->plugin_name,
            __( 'Refresh Data', 'blizzard-api' ),
            __( 'Refresh Data', 'blizzard-api' ),
            'manage_options',
            'blizzard-api-update',
            array( $this, 'display_update_page' )
        );

        add_submenu_page(
            $this->plugin_name,
            __( 'Data Visualizer', 'blizzard-api' ),
            __( 'Data Visualizer', 'blizzard-api' ),
            'manage_options',
            'blizzard-api-data-visualizer',
            array( $this, 'display_data_visualizer_page' )
        );
    }

    public function display_settings_page() {
        include_once plugin_dir_path( dirname( __FILE__ ) ) . 'admin/partials/blizzard-api-admin-display.php';
    }

    public function display_update_page() {
        include_once plugin_dir_path( dirname( __FILE__ ) ) . 'admin/partials/blizzard-api-admin-update.php';
    }

    public function display_data_visualizer_page() {
        include_once plugin_dir_path( dirname( __FILE__ ) ) . 'admin/partials/blizzard-api-admin-data-visualizer.php';
    }
}
