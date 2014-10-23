<?php
/*
Plugin Name: WebApper
Description: This plugin is a customizable CMS
Author: Jesse LaReaux
Version: 1.0
Author URI: http://www.facebook.com/jesse.lareaux
*/

namespace WebApper;


// User Role Editor WordPress plugin - http://wordpress.org/plugins/user-role-editor/
define('URE_SHOW_ADMIN_ROLE', 1);

// Include plugin files
require_once 'Base.php';
require_once 'Module.php';
require_once 'Shortcode.php';
require_once 'IndexView.php';
require_once 'PFBC/Form.php';
require_once 'SSP.php';
require_once 'Generator/core.php';
require_once 'Field/core.php';
require_once 'Fieldset/core.php';
require_once 'Condition/core.php';
require_once 'Equation/core.php';
require_once 'Action/core.php';
require_once 'Flow/core.php';
require_once 'Attachment/core.php';


class WebApper extends Base {
	
	// Set plugin version
	private $db_version = 9;

    public function __construct() {
        register_activation_hook( __FILE__, array($this, 'install') );
		add_action( 'plugins_loaded', array($this, 'update') );
        add_action( 'wp_enqueue_scripts', array($this, 'enqueue_scripts') );
    }

    /**
     * Create tables on plugin activation
     *
     * @global object $wpdb
	 * @param string $installed_version
     * @since 1.0
     */
    public function install() {
		global $wpdb;

		$installed_ver = get_option( 'web_apper_installed_version' );

		#if( $installed_ver !== $this->installed_version ) {
		if( $installed_ver !== $this->db_version ) { // Make it refresh the DB table structure every time the plugin is activated

			// Allow Modules to hook in sql create table statements
			$sql_array = apply_filters( 'web_apper_create_table_sql', $sql_array );

			require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
			
			foreach( $sql_array as $sql ) :
				dbDelta( $sql );
			endforeach;
			
			do_action( 'web_apper_insert_table_rows' );

			update_option( 'web_apper_installed_version', $this->installed_version );
		}
	}

    /**
     * Check db tables version on plugin upgrade
     *
     * @param string $installed_version
     * @since 1.0
     */
	public function update() {
		if (get_site_option( 'web_apper_installed_version' ) != $this->installed_version) {
			$this->install();
		}
	}
	
    /**
     * Enqueues Styles and Scripts
     *
     * @uses has_shortcode()
     * @since 1.0
     */
    public function enqueue_scripts() {
		// JS
		wp_enqueue_script( 'Form', plugins_url( 'assets/js/Form.js' , __FILE__ ), 'jQuery', '1.9.4', true );
		wp_enqueue_script( 'DataTables', plugins_url( 'assets/js/dataTables.js' , __FILE__ ), 'jQuery', '1.9.4', true );
		wp_enqueue_script( 'DataTables-Custom', plugins_url( 'assets/js/dataTables.custom.js' , __FILE__ ), 'jQuery', '1.9.4', true );
		#wp_enqueue_script( 'DataTables-ColReorder', plugins_url( 'assets/js/dataTables.colReorder.js' , __FILE__ ), 'jQuery', '1.9.4', true );
		wp_enqueue_script( 'plupload-all' );

		// CSS
		wp_enqueue_style( 'Web-Apper', plugins_url( 'assets/css/webapper.css' , __FILE__ ), false, '1.0', 'all' );
		wp_enqueue_style( 'DataTables-Custom', plugins_url( 'assets/css/dataTables.custom.css' , __FILE__ ), false, '1.0', 'all' );
		#wp_enqueue_style( 'DataTables-ColReorder', plugins_url( 'assets/css/dataTables.colReorder.css' , __FILE__ ), false, '1.0', 'all' );
    }

}

// Initialise plugin class & declare as global
$initialize = new WebApper();

?>