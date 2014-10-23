<?php
/*
WebApper Moddule Name: Conditions
*/

namespace WebApper\Condition;

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
	$sql_array[] = "CREATE TABLE {$wpdb->prefix}web_apper_conditions (
		ID SMALLINT UNSIGNED AUTO_INCREMENT NOT NULL,
		condition_name VARCHAR(100) NOT NULL,
		condition_id VARCHAR(55) NOT NULL,
		condition_left_type VARCHAR(30) NOT NULL,
		condition_left_side VARCHAR(100) NOT NULL,
		condition_operator VARCHAR(30) NOT NULL,
		condition_right_type VARCHAR(30),
		condition_right_side VARCHAR(100),
		core_item TINYINT(1) UNSIGNED DEFAULT '0' NOT NULL,
		UNIQUE INDEX (condition_id),
		PRIMARY KEY (ID)
	);";
	return $sql_array;
}, 2, 1 );
add_action( 'web_apper_insert_table_rows', function () {
	global $wpdb;
	// Deafult Fields
	$wpdb->query( "INSERT INTO `{$wpdb->prefix}web_apper_fields` (`field_name`, `field_id`, `field_index_only`, `field_form_only`, `field_field_only`, `field_type`, `field_attributes`, `field_required`, `field_placeholder`, `field_short_desc`, `field_long_desc`, `field_options`, `field_default_value`, `field_validation`, `field_regex`, `field_error_message`, `field_dt_show_col_default`, `field_dt_format_value`, `field_dt_filter_type`, `field_dt_filter_options`, `field_dt_filter_value`, `field_bulk_edit`, `field_read_only`, `field_dynamic_value_flow_id`, `field_action_flow_id`, `field_form_flow_id`, `core_item`) VALUES
		('Condition Name', 'condition_name', 0, 0, 0, 'Textbox', '', 1, '', '', '', '', '', '', '', '', 1, '', 'search', '', '', 0, 0, '', '', '', 1),
		('Condition ID', 'condition_id', 0, 0, 0, 'Textbox', '', 1, 'condition-id', 'A unique ID for the Condition', 'Must be alphanumeric: contain only lowercase letters, numbers, underscores(_) and/or hyphens(-).', '', '', 'AlphaNumeric', '', '', 1, '', 'search', '', '', 0, 0, '', '', '', 1),
		('Left Type', 'condition_left_type', 0, 0, 0, 'Select', 'class,left', 0, '', '', '', 'field,Field|equation,Equation|static,Static', '', '', '', '', 1, '', 'equals', 'field,Field|equation,Equation|static,Static', '', 0, 0, '', '', 'cond-left-type', 1),
		('Left Field', 'condition_left_field', 0, 1, 1, 'Textbox', 'class,left builder_field', 0, '', '', '', 'This Field,This Field|first-name,First Name|hourly-wage, Hourly Wage', '', '', '', '', 1, '', 'search', '', '', 0, 0, '', '', '', 1),
		('Left Equation', 'condition_left_equation', 0, 1, 1, 'Textbox', 'class,left builder_field', 0, '', '', '', 'total_income,total income|total_debt,total debt', '', '', '', '', 1, '', 'search', '', '', 0, 0, '', '', '', 1),
		('Left Static', 'condition_left_static', 0, 1, 1, 'Textbox', 'class,left builder_field', 0, '', '', '', '', '', '', '', '', 1, '', 'search', '', '', 0, 0, '', '', '', 1),
		('Left Value', 'condition_left_side', 0, 0, 0, 'Hidden', '', 0, '', '', '', '', '', '', '', '', 1, '', 'search', '', '', 0, 0, '', '', '', 1),
		('Conditional', 'condition_operator', 0, 0, 0, 'Select', 'class,builder_field', 0, '', '', '', 'is equal to,is equal to|is not equal to,is not equal to|is lesser than,is lesser than|is greater than,is greater than|is lesser than or equal to,is lesser than or equal to|is greater than or equal to,is greater than or equal to|is empty,is empty|is not empty,is not empty|is in string,is in string|is not in string,is not in string', '', '', '', '', 1, '', 'equals', 'is equal to,is equal to|is not equal to,is not equal to|is lesser than,is lesser than|is greater than,is greater than|is lesser than or equal to,is lesser than or equal to|is greater than or equal to,is greater than or equal to|is empty,is empty|is not empty,is not empty|is in string,is in string|is not in string,is not in string', '', 0, 0, '', '', 'cond-condition', 1),
		('Right type', 'condition_right_type', 0, 0, 0, 'Select', 'class,right', 0, '', '', '', 'field,Field|equation,Equation|static,Static', '', '', '', '', 1, '', 'equals', 'field,Field|equation,Equation|static,Static', '', 0, 0, '', '', 'cond-right-type', 1),
		('Right field', 'condition_right_field', 0, 1, 1, 'Textbox', 'class,right builder_field', 0, '', '', '', 'This Field,This Field|first-name,First Name|hourly-wage, Hourly Wage', '', '', '', '', 1, '', 'search', '', '', 0, 0, '', '', '', 1),
		('Right equation', 'condition_right_equation', 0, 1, 1, 'Textbox', 'class,right builder_field', 0, '', '', '', 'total_income,total income|total_debt,total debt', '', '', '', '', 1, '', 'search', '', '', 0, 0, '', '', '', 1),
		('Right static', 'condition_right_static', 0, 1, 1, 'Textbox', 'class,right builder_field', 0, '', '', '', '', '', '', '', '', 1, '', 'search', '', '', 0, 0, '', '', '', 1),
		('Right Value', 'condition_right_side', 0, 0, 0, 'Hidden', '', 0, '', '', '', '', '', '', '', '', 1, '', 'search', '', '', 0, 0, '', '', '', 1)
	;" );
	// Deafult Conditionals
	$wpdb->query( "INSERT INTO `{$wpdb->prefix}web_apper_conditions` (`condition_name`, `condition_id`, `condition_left_type`, `condition_left_side`, `condition_operator`, `condition_right_type`, `condition_right_side`, `core_item`) VALUES
		('Cond Gen - Left Type equals Field', 'cond-left-type-equals-field', 'field', 'condition_left_type', 'is equal to', 'static', 'field', 1),
		('Cond Gen - Left Type equals Static', 'cond-left-type-equals-static', 'field', 'condition_left_type', 'is equal to', 'static', 'static', 1),
		('Cond Gen - Left Type equals Equation', 'cond-left-type-equals-equation', 'field', 'condition_left_type', 'is equal to', 'static', 'equation', 1),
		('Cond Gen - Right Type equals Field', 'cond-right-type-equals-field', 'field', 'condition_right_type', 'is equal to', 'static', 'field', 1),
		('Cond Gen - Right Type equals Static', 'cond-right-type-equals-static', 'field', 'condition_right_type', 'is equal to', 'static', 'static', 1),
		('Cond Gen - Right Type equals Equation', 'cond-right-type-equals-equation', 'field', 'condition_right_type', 'is equal to', 'static', 'equation', 1),
		('Cond Gen - Condition equals Empty', 'cond-condition-equals-empty', 'field', 'condition_operator', 'is equal to', 'static', 'is empty', 1),
		('Cond Gen - Condition equals Not Empty', 'cond-condition-equals-not-empty', 'field', 'condition_operator', 'is equal to', 'static', 'is not empty', 1)
	;" );
	// Deafult Flows
	$wpdb->query( "INSERT INTO `{$wpdb->prefix}web_apper_flows` (`flow_name`, `flow_id`, `flow_type`, `flow_code`, `flow_hook`, `core_item`) VALUES
		('Cond Gen - Left Type field', 'cond-left-type', 'form', 'IF cond-left-type-equals-field\nSHOW condition_left_field\nELSEIF cond-left-type-equals-static\nSHOW condition_left_static\nELSEIF cond-left-type-equals-equation\nSHOW condition_left_equation', '', 1),
		('Cond Gen - Right Type field', 'cond-right-type', 'form', 'IF cond-right-type-equals-field\r\nSHOW condition_right_field\r\nELSEIF cond-right-type-equals-static\r\nSHOW condition_right_static\r\nELSEIF cond-right-type-equals-equation\r\nSHOW condition_right_equation', '', 1),
		('Cond Gen - Condition field', 'cond-condition', 'form', 'IF cond-condition-equals-not-empty\r\nHIDE condition_right_type,condition_right_field,condition_right_equation,condition_right_static\r\nELSEIF cond-condition-equals-empty\r\nHIDE condition_right_type,condition_right_field,condition_right_equation,condition_right_static', '', 1)
	;" );
}, 1, 0 );

// Add custom field type to PFBC
add_action( 'web_apper_add_form_field', function ( $form, $type, $label, $name, $options, $additionalParams ) {
	if ( $type == 'Condition' ) $form->addElement( new \PFBC\Element\Condition($label, $name, $additionalParams) ); // Captcha
}, 1, 6 );	