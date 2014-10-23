<?php
/*
WebApper Moddule Name: Flows
*/

namespace WebApper\Flow;

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
	$sql_array[] = "CREATE TABLE {$wpdb->prefix}web_apper_flows (
		ID SMALLINT UNSIGNED AUTO_INCREMENT NOT NULL,
		flow_name VARCHAR(100) NOT NULL,
		flow_id VARCHAR(55) NOT NULL,
		flow_type ENUM('form','dynamic value','action') NOT NULL,
		flow_code TEXT NOT NULL,
		flow_hook VARCHAR(100),
		core_item TINYINT(1) UNSIGNED DEFAULT '0' NOT NULL,
		UNIQUE INDEX (flow_id),
		PRIMARY KEY (ID)
	);";
	return $sql_array;
}, 2, 1 );
add_action( 'web_apper_insert_table_rows', function () {
	global $wpdb;
	// Deafult Fields
	$wpdb->query( "INSERT INTO `{$wpdb->prefix}web_apper_fields` (`field_name`, `field_id`, `field_index_only`, `field_form_only`, `field_field_only`, `field_type`, `field_attributes`, `field_required`, `field_placeholder`, `field_short_desc`, `field_long_desc`, `field_options`, `field_default_value`, `field_validation`, `field_regex`, `field_error_message`, `field_dt_show_col_default`, `field_dt_format_value`, `field_dt_filter_type`, `field_dt_filter_options`, `field_dt_filter_value`, `field_bulk_edit`, `field_read_only`, `field_dynamic_value_flow_id`, `field_action_flow_id`, `field_form_flow_id`, `core_item`) VALUES
		('Name', 'flow_name', 0, 0, 0, 'Textbox', '', 1, '', '', '', '', '', '', '', '', 1, '', 'search', '', '', 0, 0, '', '', '', 1),
		('Flow ID', 'flow_id', 0, 0, 0, 'Textbox', '', 1, '', 'A unique ID for the Flow', '', '', '', '', '', '', 1, '', 'search', '', '', 0, 0, '', '', '', 1),
		('Type', 'flow_type', 0, 0, 0, 'Select', '', 1, '', '', '', 'form,form|dynamic value,dynamic value|action,action', '', '', '', '', 1, '', 'equals', '', '', 0, 0, '', '', 'flowgen-flow-type', 1),
		('If ', 'flow_conditional', 0, 1, 0, 'Select', '', 0, '', '', '', ',|has enough income,has enough income', '', '', '', '', 1, 'bool_to_text', 'search', '', '', 0, 0, '', '', '', 1),
		('Show ', 'flow_statement_form_show', 0, 1, 0, 'Select', 'multiple,1', 0, '', '', '', 'This Field|first-name,First Name|hourly-wage, Hourly Wage', '', '', '', '', 1, '', '', '', '', 0, 0, '', '', '', 1),
		('Return ', 'flow_statement_dynamic_value', 0, 1, 0, 'Select', '', 0, '', '', '', 'equation1,equation1|equation2,equation2', '', '', '', '', 1, '', '', '', '', 0, 0, '', '', '', 1),
		('Do ', 'flow_statement_action', 0, 1, 0, 'Select', 'multiple,1', 0, '', '', '', 'action1,action1|action2,action2', '', '', '', '', 1, '', '', '', '', 0, 0, '', '', '', 1),
		('Code', 'flow_code', 0, 0, 0, 'Textarea', '', 1, '', '', '', '', '', '', '', '', 1, '', 'search', '', '', 0, 0, '', '', '', 1),
		('Hook', 'flow_hook', 0, 0, 0, 'Select', '', 0, '', '', '', ',|AAA,AAA|BBB,BBB', '', '', '', '', 1, '', 'search', '', '', 0, 0, '', '', '', 1)
	;" );
	// Deafult Conditionals
	$wpdb->query( "INSERT INTO `{$wpdb->prefix}web_apper_conditions` (`condition_name`, `condition_id`, `condition_left_type`, `condition_left_side`, `condition_operator`, `condition_right_type`, `condition_right_side`, `core_item`) VALUES
		('Flow Generator Type Field equals Form', 'flow-type-equals-form', 'field', 'flow_type', 'is equal to', 'static', 'form', 1),
		('Flow Generator Type Field equals Action', 'flow-type-equals-action', 'field', 'flow_type', 'is equal to', 'static', 'action', 1),
		('Flow Generator Type Field equals DV', 'flow-type-equals-dv', 'field', 'flow_type', 'is equal to', 'static', 'dynamic value', 1)
	;" );
	// Deafult Flows
	$wpdb->query( "INSERT INTO `{$wpdb->prefix}web_apper_flows` (`flow_name`, `flow_id`, `flow_type`, `flow_code`, `flow_hook`, `core_item`) VALUES
		('Flow Gen - Flow Type field', 'flowgen-flow-type', 'form', 'IF flow-type-equals-form\r\nSHOW flow_statement_form_show\r\nELSEIF flow-type-equals-dv\r\nSHOW flow_statement_dynamic_value\r\nELSEIF flow-type-equals-action\r\nSHOW flow_statement_action', '', 1)
	;" );
}, 1, 0 );

// Add custom field type to PFBC
add_action( 'web_apper_add_form_field', function ( $form, $type, $label, $name, $options, $additionalParams ) {
	if ( $type == 'Flow' ) $form->addElement( new \PFBC\Element\Flow($label, $name, $additionalParams) ); // Captcha
}, 1, 6 );