<?php
/*
WebApper Moddule Name: Attachments
*/

namespace WebApper\Attachment;
// Include Module files
require_once 'class.php';
require_once 'api.php';
/*
foreach( glob ( dirname(__FILE__) . '/Shortcode/*.php' ) as $filename ) {
	require_once( $filename );
}
*/

// Hook into plugin activation to install Module DB table
add_filter( 'web_apper_create_table_sql', function ( $sql_array ) {
	global $wpdb;
	$sql_array[] = "CREATE TABLE {$wpdb->prefix}web_apper_attachments (
		ID SMALLINT UNSIGNED AUTO_INCREMENT NOT NULL,
		attachment_item_id SMALLINT UNSIGNED,
		attachment_type VARCHAR(30) NOT NULL,
		attachment_file_path VARCHAR(255) NOT NULL,
		attachment_file_name VARCHAR(255) NOT NULL,
		attachment_created_on DATETIME NOT NULL,
		PRIMARY KEY (ID)
	);";
	return $sql_array;
}, 2, 1 );
add_action( 'web_apper_insert_table_rows', function () {
	global $wpdb;
	$wpdb->query( "INSERT INTO `{$wpdb->prefix}web_apper_fields` (`field_name`, `field_id`, `field_index_only`, `field_form_only`, `field_field_only`, `field_type`, `field_attributes`, `field_required`, `field_placeholder`, `field_short_desc`, `field_long_desc`, `field_options`, `field_default_value`, `field_validation`, `field_regex`, `field_error_message`, `field_dt_show_col_default`, `field_dt_format_value`, `field_dt_filter_type`, `field_dt_filter_options`, `field_dt_filter_value`, `field_bulk_edit`, `field_read_only`, `field_dynamic_value_flow_id`, `field_action_flow_id`, `field_form_flow_id`, `core_item`) VALUES
		('Record', 'attachment_item_id', 0, 0, 0, 'Hidden', '', 0, '', '', '', '', '', '', '', '', 1, 'post_id', 'search', '', '', 0, 0, '', '', '', 1),
		('Type', 'attachment_type', 0, 0, 0, 'Select', '', 1, '', '', '', 'file,file|image,image', '', '', '', '', 1, '', 'equals', 'file,file|image,image', '', 0, 0, '', '', '', 1),
		(' ', 'attachment_file_path', 0, 1, 0, 'Hidden', '', 0, '', '', '', '', '', '', '', '', 1, '', 'search', '', '', 0, 0, '', '', '', 1),
		('File', 'attachment_file_name', 0, 0, 0, 'Hidden', '', 0, '', '', '', '', '', '', '', '', 1, '', 'search', '', '', 0, 0, '', '', '', 1),
		('Uploaded on', 'attachment_created_on', 1, 0, 0, 'Hidden', '', 0, '', '', '', '', '', '', '', '', 1, 'date', 'date', '', '', 0, 1, '', '', '', 1)
	;" );
}, 1, 0 );

add_action( 'wp_ajax_web_apper_attachment', function () {
	check_ajax_referer( 'AwesomeSauce!87', 'web_apper_nonce' );  // Verify the security nonce
	// Delete the attachment
	$result = web_apper_delete_attachment( $_POST['web_apper_item_ids'] );
	// Send ajax response
	if ( $result ) :
		$response['success'] = true;
	else :
		$response['success'] = false;
	endif;
	echo json_encode( $response );  // Send Response
	die();  // Prevents wp ajax from returning a '0'
});
