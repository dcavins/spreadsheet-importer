<?php
/**
 * @package   CARES_Spreadsheet_Importer
 * @author    AuthorName
 * @license   GPL-2.0+
 * @link      http://www.communitycommons.org
 * @copyright 2017 Community Commons
 */

/**
 * Plugin class. This class should ideally be used to work with the
 * public-facing side of the WordPress site.
 *
 * If you're interested in introducing administrative or dashboard
 * functionality, then refer to `admin/class-cares_ol-admin.php`
 *
 *
 * @package CARES_Spreadsheet_Importer
 * @author  AuthorName
 */
class CARES_Spreadsheet_Importer {

	/**
	 *
	 * The current version of the plugin.
	 *
	 * Plugin version, used for cache-busting of style and script file references.
	 *
	 * @since    1.0.0
	 *
	 * @var      string
	 */
	protected $version = '1.0.0';

	/**
	 *
	 * Unique identifier for your plugin.
	 *
	 *
	 * The variable name is used as the text domain when internationalizing strings
	 * of text. Its value should match the Text Domain file header in the main
	 * plugin file.
	 *
	 * @since    1.0.0
	 *
	 * @var      string
	 */
	protected $plugin_slug = 'cares-spreadsheet-importer';

	/**
	 * Initialize the plugin by setting localization and loading public scripts
	 * and styles.
	 *
	 * @since     1.0.0
	 */
	public function __construct() {

		$this->version = cares_spreadsheet_importer_get_plugin_version();
	}

	public function add_hooks() {
		// Load plugin text domain
		add_action( 'init', array( $this, 'load_plugin_textdomain' ) );

		// Load public-facing style sheet and JavaScript.
		add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_styles_scripts' ) );

		// Ensure that all logged-in users have the 'upload_files' cap
		add_filter( 'map_meta_cap', array( $this, 'map_meta_cap' ), 10, 4 );

	}

	/**
	 * Return the plugin slug.
	 *
	 * @since    1.0.0
	 *
	 * @return   string Plugin slug.
	 */
	public function get_plugin_slug() {
		return $this->plugin_slug;
	}

	/**
	 * Load the plugin text domain for translation.
	 *
	 * @since    1.0.0
	 */
	public function load_plugin_textdomain() {
		$domain = $this->plugin_slug;
		$locale = apply_filters( 'plugin_locale', get_locale(), $domain );

		load_textdomain( $domain, trailingslashit( WP_LANG_DIR ) . $domain . '/' . $domain . '-' . $locale . '.mo' );
	}

	/**
	 * Register and enqueue public-facing style sheet.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_styles_scripts() {
		// Styles
		wp_enqueue_style( $this->plugin_slug . '-plugin-styles', plugins_url( 'css/public.css', __FILE__ ), array(), $this->version );
		wp_enqueue_script( $this->plugin_slug . '-plugin-scripts', plugins_url( 'js/public.min.js', __FILE__ ), array( 'jquery' ), $this->version, true );
		//localize data for script
		wp_localize_script( $this->plugin_slug . '-plugin-scripts', 'CARES_Spreadsheets_Edit', array(
				'root' => esc_url_raw( rest_url() ),
				'nonce' => wp_create_nonce( 'wp_rest' ),
				'current_user_id' => get_current_user_id(),
				'ajax_url' => admin_url( 'admin-ajax.php' )
			)
		);

		// IE specific
		// global $wp_styles;
		// wp_enqueue_style( $this->plugin_slug . '-ie-plugin-styles', plugins_url( 'css/public-ie.css', __FILE__ ), array(), $this->version );
		// $wp_styles->add_data( $this->plugin_slug . '-ie-plugin-styles', 'conditional', 'lte IE 9' );

		// Scripts
		if ( is_archive( 'spreadsheets' ) ) {
			// wp_enqueue_script( 'd3-js-v3', 'https://d3js.org/d3.v3.min.js', array(), '3.5.17' );
			// wp_enqueue_script( 'd3-js-v4', 'https://d3js.org/d3.v4.min.js', array(), '4.4.0' );
		}

		// Enqueue Chosen.js library.
		// wp_enqueue_style( 'openlayers-styles', 'https://openlayers.org/en/v3.19.1/css/ol.css', array(), '3.19.1' );
		// wp_enqueue_script( 'openlayers-script', 'https://openlayers.org/en/v3.19.1/build/ol.js', array(), '3.19.1' );
	}

	/**
	 * Give users the 'edit_post' and 'upload_files' cap, when appropriate
	 *
	 * @since 1.4
	 *
	 * @param array $caps The mapped caps
	 * @param string $cap The cap being mapped
	 * @param int $user_id The user id in question
	 * @param $args
	 * @return array $caps
	 */
	public function map_meta_cap( $caps, $cap, $user_id, $args ) {
		if ( 'upload_files' !== $cap && 'edit_post' !== $cap ) {
			return $caps;
		}

		$maybe_user = new WP_User( $user_id );
		if ( ! ( $maybe_user instanceof WP_User ) || empty( $maybe_user->ID ) ) {
			return $caps;
		}

		$is_data_set = false;

		// DOING_AJAX is not set yet, so we cheat
		$is_ajax = isset( $_SERVER['REQUEST_METHOD'] ) && 'POST' === $_SERVER['REQUEST_METHOD'] && 'async-upload.php' === substr( $_SERVER['REQUEST_URI'], strrpos( $_SERVER['REQUEST_URI'], '/' ) + 1 );

		if ( $is_ajax ) {
			// WordPress sends the 'media-form' nonce, which we use
			// as an initial screen
			$nonce   = isset( $_REQUEST['_wpnonce'] ) ? stripslashes( $_REQUEST['_wpnonce'] ) : '';
			$post_id = isset( $_REQUEST['post_id'] ) ? intval( $_REQUEST['post_id'] ) : '';

			if ( wp_verify_nonce( $nonce, 'media-form' ) && $post_id ) {
				$post   = get_post( $post_id );

				// The dummy Doc created during the Create
				// process should pass this test, in addition to
				// existing Docs
				$is_data_set = isset( $post->post_type ) && 'cares_data_set' === $post->post_type;
			}
		} else {
			$is_data_set = cares_data_set_is_data_set() || cares_data_set_is_create();
		}

		if ( $is_data_set ) {
			// If this user can edit this doc, we're good.
			// @TODO: post id might not exist here.
			if ( empty( $post_id ) ) {
				if ( isset( $args[0] ) ) {
					$post_id = $args[0];
				} else {
					$post_id = get_the_ID();
				}
			}
			if ( in_array( $user_id, cares_data_set_get_allowed_editors( $post_id ) ) ) {
				$caps = array( 'exist' );
			}
		}

		return $caps;
	}

}
