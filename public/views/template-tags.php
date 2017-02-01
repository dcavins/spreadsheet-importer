<?php
/**
 * Generate the public-facing pieces of the plugin.
 *
 * Community Commons MoCWP
 *
 * @package   CARES_Spreadsheet_Importer
 * @author    AuthorName
 * @license   GPL-2.0+
 * @link      http://www.communitycommons.org
 * @copyright 2017 Community Commons
 */

// using dashicons is OK here, because you'd have to be logged in to do this work.
function cares_build_table_from_json( $data = '' ) {
	$post_id = get_the_ID();
	$post = get_post( $post_id );
	$current_user_id = get_current_user_id();
	// Is this the "edit" view?
	$edit = false;
	if ( ! empty( $_GET['edit'] ) ) {
		// Can this user edit this data?
		$allowed_editor = false;
		// if ( get_the_author_meta( 'ID' ) == $current_user_id )  {
		// 	// Is the current user a specifically identified editor for this data set?
		// 	$allowed_editor = true;
		// }

		if ( current_user_can( 'edit_post', $post_id ) ) {
			$allowed_editor = true;
		}
		// Is anyone currently editing this data set?

	}
echo '<pre>';
// echo PHP_EOL . "is a data set? ";
// var_dump( cares_data_set_is_data_set() );
echo PHP_EOL . "can edit post?? ";
var_dump( current_user_can( 'edit_post', $post_id ) );
// echo PHP_EOL . "is create? ";
// var_dump( cares_data_set_is_create() );
// echo PHP_EOL . "editors? ";
// var_dump( cares_data_set_get_allowed_editors() );
echo '</pre>';

	// First, decode the data.
	$data = json_decode( $data );
	if ( ! $data ) {
		return;
	}

	$num_rows = count( $data );
	// Get the object keys
	$keys = array();
	foreach ( current( $data ) as $key => $value ) {
		$keys[] = $key;
	}
	$num_columns = count( $keys );

	// Create the table.
	?>
	<div class="data-set-edit-container">
		<table class="data-set-edit">
			<tr>
		<?php
		// Create the header row.
		foreach ( $keys as $column_header ) {
		?>
				<th contenteditable="true"><?php echo $column_header; ?></th>
		<?php
		}
		?>
			<th class="row-actions"></th>
			</tr>
		<?php
		foreach ( $data as $row ) {
			?>
			<tr>
			<?php
			foreach ( $row as $key => $cell_value) {
				?>
				<td contenteditable="true"><?php echo $cell_value; ?></td>
				<?php
			}
			?>
			<td class="row-actions"><span class="dashicons dashicons-move"></span><button class="minimal remove-row"><span class="dashicons dashicons-no"></span></button></td>
			</tr>
			<?php
		}
		?>

			<!-- This is our clonable table line -->
			<tr hidden class="hidden">
			<?php
				for ( $i=0; $i < $num_columns; $i++ ) {
					?>
					<td contenteditable="true"></td>
					<?php
				}
			?>
				<td class="row-actions"><span class="dashicons dashicons-move"></span><button class="minimal remove-row"><span class="dashicons dashicons-no"></span></button></td>
			</tr>
		</table>
		<button class="add-row table-action"><span class="dashicons dashicons-plus"></span> Add a row</button>
		<button class="export-data table-action"><span class="dashicons dashicons-download"></span> Save data</button>

		<input type="hidden" name="post-id" id="post-id" value="<?php the_ID(); ?>">

		<div id="allowed-editors">
			<h3>Editors</h3>
			<ul id="editors-list">
				<?php
				$editors = cares_data_set_get_allowed_editors_info( $post_id );
				foreach ( $editors as $editor ) {
					if ( $post->post_author == $editor->id ) {
						continue;
					}
					?>
					<li id="editor-<?php echo $editor->id; ?>"><?php echo $editor->display_name; ?></li>
					<?php
				}
				?>
			</ul>
			<h4>Add more editors</h4>
			<input id="add-editor" type="text" value="" placeholder="Search by username.">

		</div>
		<div id="exported-data"></div>
	</div>
	<?php
}