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
class Cares_Data_Sets_CPT_Tax {

	private $post_type = 'cares_data_set';
	private $allowed_editors_tax_name = 'data_set_allowed_editors';
	private $nonce_value = '';
	private $nonce_name = '';
	/**
	 * Initialize the extension class
	 *
	 * @since     1.6.0
	 */
	public function __construct() {
		$this->nonce_value = $this->post_type . '_meta_box_nonce';
		$this->nonce_name = $this->post_type . '_meta_box';
	}

	public function add_hooks() {
		// Register Policy custom post type
		add_action( 'init', array( $this, 'register_cpt' ) );
		add_action( 'init', array( $this, 'register_allowed_editors_taxonomy' ) );

		// Handle saving policies
		add_action( 'save_post_' . $this->post_type, array( $this, 'save' ) );

		add_filter( 'manage_edit-' . $this->post_type . '_columns', array( $this, 'edit_admin_columns') );
		add_filter( 'manage_' . $this->post_type . '_posts_custom_column', array( $this, 'manage_admin_columns'), 10, 2 );
		// add_filter( 'manage_edit-sapolicies_sortable_columns', array( $this, 'register_sortable_columns' ) );
		// add_action( 'pre_get_posts', array( $this, 'sortable_columns_orderby' ) );
		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_admin_scripts' ) );
		add_action( 'admin_init', array( $this, 'add_meta_box' ) );

		add_filter( 'single_template', array( $this, 'add_cpt_template' ) );

		// Get potential "allowed editors" via AJAX request.
		add_action( 'wp_ajax_cds_search_possible_editors', array( $this, 'search_possible_editors' ) );

		// Save "allowed editor" terms via AJAX request.
		add_action( 'wp_ajax_cds_update_allowed_editors', array( $this, 'update_allowed_editors' ) );

		// Destroy "allowed editor" terms as users are added and deleted.
		add_action( 'deleted_user', array( $this, 'allowed_editor_user_deleted' ) );

		// Change the REST API response so that it includes important meta for ticker items.
		// add_action( 'rest_api_init', array( $this, 'rest_read_meta' ) );
	}

	/**
	 * Define the "cares_data_set" custom post type.
	 *
	 * @since    1.0.0
	 */
	public function register_cpt() {

		$labels = array(
			'name' => __('Data Sets', 'cares-spreadsheet-importer' ),
			'singular_name' => __('Data Sets', 'cares-spreadsheet-importer' ),
			// 'all_items' => __('All Resources', 'cares-spreadsheet-importer' ),
			'add_new' => __('Add Data Set', 'cares-spreadsheet-importer' ),
			'add_new_item' => __('Add Data Set', 'cares-spreadsheet-importer' ),
			'edit_item' => __('Edit Data Set', 'cares-spreadsheet-importer' ),
			'new_item' => __('New Data Set', 'cares-spreadsheet-importer' ),
			'view_item' => __('View Data Set', 'cares-spreadsheet-importer' ),
			'search_items' => __('Search in Data Sets', 'cares-spreadsheet-importer' ),
			'not_found' =>  __('No data sets found', 'cares-spreadsheet-importer' ),
			'not_found_in_trash' => __('No data sets found in trash', 'cares-spreadsheet-importer' ),
			'parent_item_colon' => __( 'Parent Data Set:', 'cares-spreadsheet-importer' ),
	        'menu_name' => __( 'Data Sets', 'cares-spreadsheet-importer' ),

		);
		$args = array(
			'labels' => $labels,
			'public' => true,
			'publicly_queryable' => true,
			'show_ui' => true,
			'query_var' => true,
			'rewrite' => array( 'slug' => 'data-sets' ),
			'hierarchical' => false,
			'show_in_menu' => true,//'salud_america',
			// 'menu_position' => 58,
			'menu_icon' => 'dashicons-chart-bar',
			'taxonomies' => array( $this->allowed_editors_tax_name ),
			// 'supports' => array('title','editor','excerpt','trackbacks','custom-fields','comments','revisions','thumbnail','author','page-attributes',),
			// 'supports' => array('title', 'author'),
			'show_in_rest' => true,
			'has_archive' => true,
			// 'capability_type' => $this->post_type,
			// 'map_meta_cap' => true
		);

		register_post_type( $this->post_type, $args );

	}

	/**
	 * Define the "cares_data_set" custom post type.
	 *
	 * @since    1.0.0
	 */
	public function register_allowed_editors_taxonomy() {
		// Add new "Resource Type" taxonomy to Salud America Resources

		$labels = array(
				'name' => __( 'Allowed Editors', 'cares-spreadsheet-importer'  ),
				'singular_name' => __( 'Allowed Editor', 'cares-spreadsheet-importer' ),
				'search_items' =>  __( 'Search Allowed Editors', 'cares-spreadsheet-importer' ),
				'all_items' => __( 'All Allowed Editors', 'cares-spreadsheet-importer' ),
				'parent_item' => __( 'Parent Allowed Editors', 'cares-spreadsheet-importer' ),
				'parent_item_colon' => __( 'Parent Allowed Editor:', 'cares-spreadsheet-importer' ),
				'edit_item' => __( 'Edit Allowed Editor', 'cares-spreadsheet-importer' ),
				'update_item' => __( 'Update Allowed Editor', 'cares-spreadsheet-importer' ),
				'add_new_item' => __( 'Add New Allowed Editor', 'cares-spreadsheet-importer' ),
				'new_item_name' => __( 'New Allowed Editor Name', 'cares-spreadsheet-importer' ),
				'menu_name' => __( 'Allowed Editors', 'cares-spreadsheet-importer' )
			);


		$args = array(
			'labels' => $labels,
			'query_var' => true,
			'rewrite' => true,
			// 'hierarchical' => true,
			// 'show_ui' => true,
			'show_admin_column' => true,
			'capabilities' => array(
				'manage_terms' => 'edit_posts',
				'delete_terms' => 'edit_posts',
				'edit_terms' => 'edit_posts',
				'assign_terms' => 'edit_posts'
			)
		);

		register_taxonomy( $this->allowed_editors_tax_name, array( $this->post_type ), $args );
	}

	/**
	 * Change behavior of the SA Policies overview table by adding taxonomies and custom columns.
	 * - Add Type and Stage columns (populated from post meta).
	 *
	 * @since    1.6.0
	 *
	 * @return   array of columns to display
	 */
	public function edit_admin_columns( $columns ) {
		// Last two columns are always Comments and Date.
		// We want to insert our new columns just before those.
		$entries = count( $columns );
		$opening_set = array_slice( $columns, 0, $entries - 1 );
		$closing_set = array_slice( $columns, - 1 );

		$insert_set = array(
			'source_file' => __( 'Source File' ),
			// 'stage' => __( 'Stage' )
			);

		$columns = array_merge( $opening_set, $insert_set, $closing_set );

		return $columns;
	}

	/**
	 * Change behavior of the SA Policies overview table by adding taxonomies and custom columns.
	 * - Handle Output for Type and Stage columns (populated from post meta).
	 *
	 * @since    1.6.0
	 *
	 * @return   string content of custom columns
	 */
	public function manage_admin_columns( $column, $post_id ) {
			switch( $column ) {
				case 'source_file' :
					$data_set_source = get_post_meta( $post_id, 'cares_data_set_source', true );
					if ( $data_set_source )  {
						echo '<span class="dashicons dashicons-media-spreadsheet"></span> ' . basename( get_attached_file( $data_set_source ) );
					}
				break;
			}
	}

	/**
	 * Change behavior of the SA Policies overview table by adding taxonomies and custom columns.
	 * - Add sortability to Type and Stage columns.
	 *
	 * @since    1.6.0
	 *
	 * @return   array of columns to display
	 */
	public function register_sortable_columns( $columns ) {
					// $columns["type"] = "type";
					// $columns["stage"] = "stage";
					//Note: Advo targets can't be sortable, because the value is a string.
					return $columns;
	}
	/**
	 * Change behavior of the SA Policies overview table by adding taxonomies and custom columns.
	 * - Define sorting query for Type and Stage columns.
	 *
	 * @since    1.6.0
	 *
	 * @return   alters $query variable by reference
	 */
	function sortable_columns_orderby( $query ) {
			if ( ! is_admin() ) {
				return;
			}

			$orderby = $query->get( 'orderby');

			switch ( $orderby ) {
				case 'stage':
						// $query->set( 'meta_key','sa_policystage' );
						// $query->set( 'orderby','meta_value' );
					break;
				case 'type':
						// $query->set( 'meta_key','sa_policytype' );
						// $query->set( 'orderby','meta_value' );
					break;
			}
	}

	public function enqueue_admin_scripts() {
		// $screen = get_current_screen();
		// if ( $this->post_type == $screen->id ) {
		// 	wp_enqueue_script( 'wp-color-picker' );
		// 	wp_enqueue_style( 'wp-color-picker' );

		// 	// Enqueue fancy coloring; includes quick-edit
		// 	wp_enqueue_script( 'salud-admin', plugins_url( '../admin/assets/js/admin.js', __FILE__ ), array( 'wp-color-picker', 'jquery', 'wp-util' ), $this::VERSION, true );

		// 	// Enqueue fancy coloring; includes quick-edit
		//     wp_enqueue_style( $this->plugin_slug . '-ticker-styles', plugins_url( '../public/css/ticker.css', __FILE__ ), array(), $this::VERSION );
		// }
	}

	/**
	 * Modify the Data Sets edit screen to show the source data set.
	 * - Add meta box for item hyperlink.
	 *
	 * @since    1.0.0
	 *
	 * @return   void
	 */
	//Building the input form in the WordPress admin area
	public function add_meta_box() {
		add_meta_box(
			$this->post_type . '_meta_box', // id
			'Data Set Information', // title
			array( $this, 'meta_box_html' ), // callable function
			$this->post_type, // screen to appear on
			'normal', // context
			'high' // priority
		);
	}
		public function meta_box_html( $post ) {
			$meta_key = 'cares_data_set_source';
			$data_set_source = get_post_meta( $post->ID, $meta_key, $single = true );

			// Add a nonce field so we can check for it later.
			wp_nonce_field( $this->nonce_name, $this->nonce_value );
			?>
			<label for='cares_data_set_source'><strong>Original Source</strong></label><br />
			<span id="data_set_source_filename" class="dashicons-before dashicons-media-spreadsheet"><?php
			if ( $data_set_source ) {
				echo basename( get_attached_file( $data_set_source ) );
			} else {
				echo "<em>No file selected.</em>";
			}
			?></span>
			<input id='cares_data_set_source' type='hidden' name='cares_data_set_source' value='<?php
				if ( $data_set_source ) {
					echo $data_set_source;
				}
				?>'/>
			<button id="cares_data_set_source_button" class="sa_term_intro_file_upload_button" data-filetype="">Select File</button>
			<?php
			if ( ! $data_set_source ) {
				$show_style = 'style="display:none"';
			} else {
				$show_style = '';
			}
			?>
			<button id="cares_data_set_source_button_remove" class="sa_term_intro_file_remove_button" <?php echo $show_style; ?>>Remove File</button><br />
			<script type="text/javascript">
				/*
				 * Attaches the image uploader to the input fields
				 */
				jQuery(document).ready(function($){
					var uploader_frame,
						filetype,
						title_text,
						button_text,
						library_type;

				    // Runs when the image button is clicked.
				    $('#cares_data_set_source_button').on( 'click', function(e){
				    	filetype = $(this).data( "filetype" );

				        // Prevents the default action from occuring.
				        e.preventDefault();

				        // If the frame already exists, re-open it.
				        if ( uploader_frame ) {
				            uploader_frame.open();
				            return;
				        }

				        // Sets up the media library frame if needed
				        // We need two versions: one restricted to images, the other to PDFs
				        uploader_frame = new wp.media.view.MediaFrame.Select({
				            title: 'Choose or Upload a Source File',
				            button: { text:  'Use this file' },
				            library: { type: ['application/vnd.ms-excel', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet', 'application/vnd.oasis.opendocument.spreadsheet', 'text/csv', 'text/tab-separated-values'] },
				            multiple: false
				        });

				        // Runs when an image is selected.
				        uploader_frame.on('select', function(){
				            // Grabs the attachment selection and creates a JSON representation of the model.
				            var media_attachment = uploader_frame.state().get('selection').first().toJSON();
				            // Sends the attachment URL to our custom image input field.
				            $('#data_set_source_filename').empty().html(media_attachment.filename);
				            $('#cares_data_set_source').val(media_attachment.id);
				            $('#cares_data_set_source_button_remove').show();
				        });

				        // Opens the media library frame.
				        uploader_frame.open();
				    });
				    // Runs when the remove file button is clicked.
				    $('.sa_term_intro_file_remove_button').on( 'click', function(e){
				        // Prevents the default action from occuring.
				        e.preventDefault();

				        $('#data_set_source_filename').empty().html("<em>No file selected.</em>")
				        $('#cares_data_set_source').val('');
				        $('#cares_data_set_source_button_remove').hide();
				    });
				});
			</script>
			<?php
			}

	/**
	 * Save extra information.
	 *
	 * @since    1.0.0
	 *
	 * @return   void
	 */
	public function save( $post_id, $post, $update ) {

		if ( ! $this->user_can_save( $post_id, $this->nonce_value, $this->nonce_name  ) ) {
			return false;
		}

		// Set the user access term.
		$author_id = (int) $post->post_author;
		$access_term_slug = cares_data_set_get_user_access_term_slug( $author_id );
		$term_taxonomy_ids = wp_set_object_terms( $post_id, $access_term_slug, $this->allowed_editors_tax_name, 'true' );

		// Save meta
		$meta_fields = array( 'cares_data_set_source' );
		$meta_success = $this->save_meta_fields( $post_id, $meta_fields );

	}

	/**
	 * Determines whether or not the current user has the ability to save meta data associated with this post.
	 *
	 * @param		int		$post_id	The ID of the post being save
	 * @param		bool				Whether or not the user has the ability to save this post.
	 */
	public function user_can_save( $post_id, $nonce_value, $nonce_name ) {

	    // Don't save if the user hasn't submitted the changes
		if( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
			return false;
		} // end if

		// Verify that the input is coming from the proper form
		if ( ! isset( $_POST[ $nonce_value ] ) || ! wp_verify_nonce( $_POST[ $nonce_value ], $nonce_name ) ) {
			return false;
		} // end if

		return true;

	} // end user_can_save

	/**
	 * General handler for saving post meta.
	 *
	 * @since   1.0.0
	 *
	 * @param 	int $post_id
	 * @param 	array meta_key names to save
	 * @return  bool
	 */
	function save_meta_fields( $post_id, $fields = array() ) {
	    $successes = 0;

	    foreach( $fields as $field ) {
	      //groups_update_groupmeta returns false if the old value matches the new value, so we'll need to check for that case
	      $old_setting = get_post_meta( $post_id, $field, true );
	      $new_setting = ( isset( $_POST[$field] ) ) ? $_POST[$field] : '' ;
	      $success = false;

	      if ( empty( $new_setting ) && ! empty( $old_setting ) ) {
			$success = delete_post_meta( $post_id, $field );
	      } elseif ( $new_setting == $old_setting ) {
			// No need to resave settings if they're the same
			$success = true;
	      } else {
			$success = update_post_meta( $post_id, $field, $new_setting );
	      }

	      if ( $success ) {
	        $successes++;
	      }

	    }

	    if ( $successes == count( $fields ) ) {
	      return true;
	    } else {
	      return false;
	    }
	}

	public function add_cpt_template( $single_template ) {
		global $post;

		if ( $post->post_type == $this->post_type ) {
			$single_template = cares_spreadsheet_importer_get_plugin_base_location() . 'public/views/templates/single-data-set.php';
		}
		return $single_template;
	}

    /**
     * Update allowed editors terms via AJAX.
     *
     * @since 1.0.0
     *
     * @param int      $id       ID of the deleted user.
     * @param int|null $reassign ID of the user to reassign posts and links to.
     *                           Default null, for no reassignment.
     */
	public function search_possible_editors() {
		check_ajax_referer( 'wp_rest' );

		if ( empty( $_POST['search'] ) || empty( $_POST['post_id'] ) ) {
			wp_send_json_error( __( 'Incomplete request.', 'cares-spreadsheet-importer' ) );
		}

		$search = $_POST['search'];
		$post_id = (int) $_POST['post_id'];

		$args = array(
			'search'         => '*' . $_POST['search'] . '*',
			'search_columns' => array( 'user_login', 'user_url', 'user_email', 'user_nicename', 'display_name' ),
			'fields'         => array( 'id', 'user_login', 'display_name' ),
		);

		$matches = new WP_User_Query( $args );

		if ( is_wp_error( $matches ) ) {
			$return = array( 'message' => __( 'Failed', 'cares-spreadsheet-importer' ) );
			wp_send_json_error( $return );
		} else {
			$return = array(
				'message' => __( 'Found Matches', 'cares-spreadsheet-importer' ),
				'users'   => $matches->results,
			);
			if ( empty( $matches->results ) ) {
				$return['message'] = __( 'No Matches', 'cares-spreadsheet-importer' );
			}
			wp_send_json_success( $return );
		}

	}

    /**
     * Update allowed editors terms via AJAX.
     *
     * @since 1.0.0
     *
     * @param int      $id       ID of the deleted user.
     * @param int|null $reassign ID of the user to reassign posts and links to.
     *                           Default null, for no reassignment.
     */
	public function update_allowed_editors() {
		check_ajax_referer( 'wp_rest' );

		if ( empty( $_POST['editor_id'] ) || empty( $_POST['post_id'] ) ) {
			wp_send_json_error( __( 'Incomplete request.', 'cares-spreadsheet-importer' ) );
		}

		$editor_id = (int) $_POST['editor_id'];
		$post_id = (int) $_POST['post_id'];
		$operation = 'add';
		if ( ! empty( $_POST['operation'] ) && $_POST['operation'] == 'remove' ) {
			$operation = 'remove';
		}

		$editor = get_user_by( 'id', $editor_id );
		if ( ! ( $editor instanceof WP_User ) ) {
			wp_send_json_error( 'User is unknown.' );
		}

		$access_term_slug = cares_data_set_get_user_access_term_slug( $editor_id );
		$term_taxonomy_ids = wp_set_object_terms( $post_id, $access_term_slug, $this->allowed_editors_tax_name, 'true' );

		// Set up return values.
		$return = array(
		    'operation'   => $operation,
		    'editor_id'   => $editor->ID,
		    'editor_name' => $editor->display_name,
		);

		if ( is_wp_error( $term_taxonomy_ids ) ) {
			$return['message'] = __( 'Failed', 'cares-spreadsheet-importer' );
			wp_send_json_error( $return );
			// There was an error somewhere and the terms couldn't be set.
		} else {
			$return['message'] = __( 'Saved', 'cares-spreadsheet-importer' );
			wp_send_json_success( $return );
		}

	}

    /**
     * Removes a user's "allowed_editor" term when the account is deleted.
     *
     * @since 2.9.0
     *
     * @param int      $id       ID of the deleted user.
     * @param int|null $reassign ID of the user to reassign posts and links to.
     *                           Default null, for no reassignment.
     */
	public function allowed_editor_user_deleted( $user_id ) {
		$term = cares_data_set_get_user_access_term( $user_id );
		wp_delete_term( (int) $term->term_id, $this->allowed_editors_tax_name );
	}

	/**
	 * Change the REST API response so that it includes important meta for ticker items.
	 *
	 * @since    1.6.0
	 *
	 * @return   void
	 */
	public function rest_read_meta() {
	    register_rest_field( $this->post_type,
	        'sa_ticker_item_leader_text',
	        array(
	            'get_callback'    => array( $this, 'get_ticker_meta' ),
	            'update_callback' => null,
	            'schema'          => null,
	        )
	    );
	    register_rest_field( $this->post_type,
	        'sa_ticker_item_leader_color',
	        array(
	            'get_callback'    => array( $this, 'get_ticker_meta' ),
	            'update_callback' => null,
	            'schema'          => null,
	        )
	    );
	    register_rest_field( $this->post_type,
	        'sa_ticker_item_link',
	        array(
	            'get_callback'    => array( $this, 'get_ticker_meta' ),
	            'update_callback' => null,
	            'schema'          => null,
	        )
	    );
	    register_rest_field( $this->post_type,
	        'nice_date',
	        array(
	            'get_callback'    => array( $this, 'get_short_human_date' ),
	            'update_callback' => null,
	            'schema'          => null,
	        )
	    );
	}

	/**
	 * Get the value of the requested meta field.
	 *
	 * @param array $object Details of current post.
	 * @param string $field_name Name of field.
	 * @param WP_REST_Request $request Current request
	 *
	 * @return mixed
	 */
	public function get_ticker_meta( $object, $field_name, $request ) {
	    return get_post_meta( $object[ 'id' ], $field_name, true );
	}

	/**
	 * Get the value of the requested meta field for a post's term.
	 *
	 * @param array $object Details of current post.
	 * @param string $field_name Name of field.
	 * @param WP_REST_Request $request Current request
	 *
	 * @return mixed
	 */
	public function get_ticker_term_meta( $object, $field_name, $request ) {
		// Set a default value.
		$value = '';
		$terms = get_the_terms( $object[ 'id' ], $this->tax_name );
		if ( is_array( $terms ) ) {
			$term_id = current( $terms )->term_id;
			$value = get_term_meta( $term_id, $field_name, true );
		}
	    return $value;
	}

	/**
	 * Get the value of the requested meta field for a post's term.
	 *
	 * @param array $object Details of current post.
	 * @param string $field_name Name of field.
	 * @param WP_REST_Request $request Current request
	 *
	 * @return mixed
	 */
	public function get_short_human_date( $object, $field_name, $request ) {
	    return get_the_date( 'M j', $object[ 'id' ] );
	}

} //End class CC_Spreadsheets_CPT_Tax
