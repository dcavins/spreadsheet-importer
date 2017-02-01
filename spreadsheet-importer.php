<?php
/**
 * A tool to import and parse spreadsheet files.
 *
 * @package   CARES_Spreadsheet_Importer
 * @author    dcavins
 * @license   GPL-2.0+
 * @link      http://www.communitycommons.org
 * @copyright 2017 Community Commons
 *
 * @wordpress-plugin
 * Plugin Name:       CARES Spreadsheet Importer
 * Plugin URI:        @TODO
 * Description:       A tool to import and parse spreadsheet files.
 * Version:           1.0.0
 * Author:            AuthorName
 * Text Domain:       cares-spreadsheet-importer
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * GitHub Plugin URI: @TODO
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

/*----------------------------------------------------------------------------*
 * Public-Facing Functionality
 *----------------------------------------------------------------------------*/

function cares_spreadsheet_importer_class_init() {

	// Helper functions
	require_once( plugin_dir_path( __FILE__ ) . 'includes/spreadsheet-importer-functions.php' );

	// Template output functions
	require_once( plugin_dir_path( __FILE__ ) . 'public/views/template-tags.php' );
	require_once( plugin_dir_path( __FILE__ ) . 'public/views/shortcodes.php' );

	// The main class
	require_once( plugin_dir_path( __FILE__ ) . 'public/class-spreadsheet-importer.php' );
	$class_public = new CARES_Spreadsheet_Importer();
	$class_public->add_hooks();

	// The custom post type
	require_once( plugin_dir_path( __FILE__ ) . 'includes/data-sets-cpt.php' );
	$class_cpt = new Cares_Data_Sets_CPT_Tax();
	$class_cpt->add_hooks();

	// Admin and dashboard functionality
	if ( is_admin() && ( ! defined( 'DOING_AJAX' ) || ! DOING_AJAX ) ) {
		require_once( plugin_dir_path( __FILE__ ) . 'admin/class-spreadsheet-importer-admin.php' );
		$class_admin = new CARES_Spreadsheet_Importer_Admin();
		$class_admin->add_hooks();
	}

}
add_action( 'init', 'cares_spreadsheet_importer_class_init', 9 );

/*
 * Register hooks that are fired when the plugin is activated or deactivated.
 * When the plugin is deleted, the uninstall.php file is loaded.
 *
 */
// require_once plugin_dir_path( __FILE__ ) . 'includes/class-openlayers-activator.php';
// register_activation_hook( __FILE__, array( 'CARES_OpenLayers_Activator', 'activate' ) );
// register_deactivation_hook( __FILE__, array( 'CARES_OpenLayers_Activator', 'deactivate' ) );

/*
 * Helper function.
 * @return Fully-qualified URI to the root of the plugin.
 */
function cares_spreadsheet_importer_get_plugin_base_uri(){
	return plugin_dir_url( __FILE__ );
}

/*
 * Helper function.
 * @return Fully-qualified URI to the root of the plugin.
 */
function cares_spreadsheet_importer_get_plugin_base_location(){
	return trailingslashit( dirname( __FILE__ ) );
}

/*
 * Helper function.
 * @TODO: Update this when you update the plugin's version above.
 *
 * @return string Current version of plugin.
 */
function cares_spreadsheet_importer_get_plugin_version(){
	return '1.0.0';
}
