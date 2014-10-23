<?php
/*
WebApper Moddule Name: Equations
*/

namespace WebApper\Equation;

// Include Module files
require_once 'class.php';
require_once 'api.php';
foreach( glob ( dirname(__FILE__) . '/Shortcode/*.php' ) as $filename ) {
	require_once( $filename );
}
foreach( glob ( dirname(__FILE__) . '/Element/*.php' ) as $filename ) {
	require_once( $filename );
}


// Hook into plugin activation to install Module DB table
add_filter( 'web_apper_create_table_sql', function ( $sql_array ) {
	global $wpdb;
	$sql_array[] = "CREATE TABLE {$wpdb->prefix}web_apper_equations (
		ID SMALLINT UNSIGNED AUTO_INCREMENT NOT NULL,
		equation_id VARCHAR(55) NOT NULL,
		equation_name VARCHAR(100) NOT NULL,
		equation_type VARCHAR(30) NOT NULL,
		equation_code TEXT NOT NULL,
		core_item TINYINT(1) UNSIGNED DEFAULT '0' NOT NULL,
		UNIQUE INDEX (equation_id),
		PRIMARY KEY (ID)
	);";
	return $sql_array;
}, 2, 1 );
add_action( 'web_apper_insert_table_rows', function () {
	global $wpdb;
	$wpdb->query( "INSERT INTO `{$wpdb->prefix}web_apper_fields` (`field_name`, `field_id`, `field_index_only`, `field_form_only`, `field_field_only`, `field_type`, `field_attributes`, `field_required`, `field_placeholder`, `field_short_desc`, `field_long_desc`, `field_options`, `field_default_value`, `field_validation`, `field_regex`, `field_error_message`, `field_dt_show_col_default`, `field_dt_format_value`, `field_dt_filter_type`, `field_dt_filter_options`, `field_dt_filter_value`, `field_bulk_edit`, `field_read_only`, `field_dynamic_value_flow_id`, `field_action_flow_id`, `field_form_flow_id`, `core_item`) VALUES
		('Equation ID', 'equation_id', 0, 0, 0, 'Textbox', '', 1, '', 'A unique ID for the Equation', 'Must be alphanumeric: contain only lowercase letters, numbers or hyphens(-).', '', '', 'AlphaNumeric', '', '', 1, '', 'search', '', '', 0, 0, '', '', '', 1),
		('Equation Name', 'equation_name', 0, 0, 0, 'Textbox', '', 1, '', '', '', '', '', '', '', '', 1, '', 'search', '', '', 0, 0, '', '', '', 1),
		('Type', 'equation_type', 0, 0, 0, 'Select', '', 1, '', '', '', 'Algebraic,Algebraic|Text,Text', '', '', '', '', 1, '', 'equals', 'Algebraic,Algebraic|Text,Text', '', 0, 0, '', '', '', 1),
		('Code', 'equation_code', 0, 0, 0, 'Textarea', '', 1, '', '', '', '', '', '', '', '', 1, '', 'search', '', '', 0, 0, '', '', '', 1)
	;" );
}, 1, 0 );

// Add custom field type to PFBC
add_action( 'web_apper_add_form_field', function ( $form, $type, $label, $name, $options, $additionalParams ) {
	if ( $type == 'Equation' ) $form->addElement( new \PFBC\Element\Equation($label, $name, $additionalParams) ); // Captcha
}, 1, 6 );	