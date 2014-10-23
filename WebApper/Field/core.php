<?php
/*
WebApper Moddule Name: Fields
*/

namespace WebApper\Field;

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
	$sql_array[] = "CREATE TABLE `{$wpdb->prefix}web_apper_fields` (
		`ID` SMALLINT UNSIGNED NOT NULL AUTO_INCREMENT,
		`field_name` VARCHAR(100) NOT NULL,
		`field_id` VARCHAR(55) NOT NULL,
		`field_index_only` BOOL DEFAULT '0',
		`field_form_only` BOOL DEFAULT '0',
		`field_field_only` BOOL DEFAULT '0',
		`field_type` VARCHAR(255) NOT NULL,
		`field_attributes` VARCHAR(255),
		`field_required` BOOL DEFAULT '0',
		`field_placeholder` VARCHAR(150),
		`field_short_desc` VARCHAR(150),
		`field_long_desc` VARCHAR(255),
		`field_options` TEXT,
		`field_default_value` VARCHAR(150),
		`field_validation` ENUM('AlphaNumeric','RegExp'),
		`field_regex` VARCHAR(255),
		`field_error_message` VARCHAR(255),
		`field_dt_show_col_default` BOOL DEFAULT '1',
		`field_dt_format_value` VARCHAR(30),
		`field_dt_filter_type` VARCHAR(30),
		`field_dt_filter_options` TEXT,
		`field_dt_filter_value` VARCHAR(100),
		`field_bulk_edit` BOOL DEFAULT '0',
		`field_read_only` BOOL DEFAULT '0',
		`field_dynamic_value_flow_id` VARCHAR(100),
		`field_action_flow_id` VARCHAR(100),
		`field_form_flow_id` VARCHAR(100),
		`core_item` BOOL DEFAULT '0',
		UNIQUE INDEX (field_id),
		PRIMARY KEY (ID)
	);";
	return $sql_array;
}, 1, 1 );

// Add default rows to DB
add_action( 'web_apper_insert_table_rows', function () {
	global $wpdb;

	// Deafult Fields
	$wpdb->query( "INSERT INTO `{$wpdb->prefix}web_apper_fields` (`field_name`, `field_id`, `field_index_only`, `field_form_only`, `field_field_only`, `field_type`, `field_attributes`, `field_required`, `field_placeholder`, `field_short_desc`, `field_long_desc`, `field_options`, `field_default_value`, `field_validation`, `field_regex`, `field_error_message`, `field_dt_show_col_default`, `field_dt_format_value`, `field_dt_filter_type`, `field_dt_filter_options`, `field_dt_filter_value`, `field_bulk_edit`, `field_read_only`, `field_dynamic_value_flow_id`, `field_action_flow_id`, `field_form_flow_id`, `core_item`) VALUES
		('Field Name', 'field_name', 0, 0, 0, 'Textbox', '', 1, '', '', 'A readable label for the field', '', '', '', '', '', 1, '', 'search', '', '', 0, 0, '', '', '', 1),
		('Field ID', 'field_id', 0, 0, 0, 'Textbox', '', 1, '', 'A unique ID for the field', 'Must be alphanumeric: contain only lowercase letters, numbers and hyphens(-).', '', '', 'RegExp', '/[a-z,0-9,-]/', '', 1, '', 'search', '', '', 0, 0, '', '', '', 1),
		('Index only', 'field_index_only', 0, 0, 0, 'YesNo', '', 0, '', '', '', '', '', '', '', '', 1, 'bool_to_text', 'equals', '1,Yes|0,No', '', 1, 0, '', '', 'field-gen-index-only', 1),
		('Form only', 'field_form_only', 0, 0, 0, 'YesNo', '', 1, '', '', '', '', '', '', '', '', 1, 'bool_to_text', 'equals', '1,Yes|0,No', '', 1, 0, '', '', 'field-gen-form-only', 1),
		('Field only', 'field_field_only', 0, 0, 0, 'YesNo', '', 0, '', '', '', '', '', '', '', '', 1, 'bool_to_text', 'equals', '1,Yes|0,No', '', 1, 0, '', '', '', 0),
		('Required', 'field_required', 0, 0, 0, 'YesNo', '', 0, '', '', 'Make this a required field', '', '', '', '', '', 1, 'bool_to_text', 'equals', '1,Yes|0,No', '', 1, 0, '', '', '', 1),
		('Type', 'field_type', 0, 0, 0, 'Fieldtype', '', 1, '', '', '', '', '', '', '', '', 1, '', 'search', '', '', 0, 0, '', '', '', 1),
		('Placeholder', 'field_placeholder', 0, 0, 0, 'Textbox', '', 0, 'my placeholder', 'Provides an example or hint', '', '', '', '', '', '', 1, '', 'search', '', '', 0, 0, '', '', '', 1),
		('Short Description', 'field_short_desc', 0, 0, 0, 'Textbox', '', 0, '', 'This text is a short description', '', '', '', '', '', '', 1, '', 'search', '', '', 0, 0, '', '', '', 1),
		('Long Description', 'field_long_desc', 0, 0, 0, 'Textarea', '', 0, '', '', 'This text is a Long description', '', '', '', '', '', 1, '', 'search', '', '', 0, 0, '', '', '', 1),
		('Options', 'field_options', 0, 0, 0, 'Textarea', '', 0, 'val-1,Value One | val-2,Value Two', '', 'Enter option values for fields like checkboxes and radios. Enter options in ''option-value,Option label'' pairs. Use a pipe(|) to seperate pairs.', '', '', '', '', '', 1, '', 'search', '', '', 1, 0, '', '', '', 1),
		('Default Value', 'field_default_value', 0, 0, 0, 'Textbox', '', 0, '', '', '', '', '', '', '', '', 1, '', 'search', '', '', 0, 0, '', '', '', 1),
		('HTML attributes', 'field_attributes', 0, 0, 0, 'Textarea', '', 0, '', '', '', '', '', '', '', '', 1, '', 'search', '', '', 0, 0, '', '', '', 1),
		('Validation', 'field_validation', 0, 0, 0, 'Select', '', 0, '', '', '', ',|AlphaNumeric,AlphaNumeric|RegExp,RegExp', '', '', '', '', 1, '', 'equals', 'AlphaNumeric,AlphaNumeric|RegExp,RegExp', '', 0, 0, '', '', 'fieldgen-show-validation-fileds', 1),
		('Regex', 'field_regex', 0, 0, 0, 'Textbox', '', 0, '', 'A RegExp to validate against', '', '', '', '', '', '', 1, '', 'search', '', '', 0, 0, '', '', '', 1),
		('Error Message', 'field_error_message', 0, 0, 0, 'Textbox', '', 0, 'The %element% field is missing', '', 'A custom message to display if the field fails validation.', '', '', '', '', '', 1, '', 'search', '', '', 0, 0, '', '', '', 1),
		('Read Only', 'field_read_only', 0, 0, 0, 'YesNo', '', 0, '', '', 'A read only field can only be set when a record is added and may not be edited there after.', '', '0', '', '', '', 1, 'bool_to_text', 'equals', '1,Yes|0,No', '', 1, 0, '', '', '', 1),
		('Index Show Colunm Default', 'field_dt_show_col_default', 0, 0, 0, 'YesNo', '', 0, '', '', '', '', '1', '', '', '', 1, 'bool_to_text', 'equals', '1,Yes|0,No', '', 1, 0, '', '', '', 1),
		('Index Format Value', 'field_dt_format_value', 0, 0, 0, 'Select', '', 0, '', '', '', ',|bool_to_text,Boolean to text|date,Date|days_since,Days since|hours_since,Hours since|user_id,User Name|post_id,Record Name', '', '', '', '', 1, '', 'equals', 'bool_to_text,Boolean to text|date,Date|days_since,Days since|hours_since,Hours since', '', 1, 0, '', '', '', 1),
		('Index filter type', 'field_dt_filter_type', 0, 0, 0, 'Select', '', 0, '', '', '', ',|search,search|equals,equals|range,range|date,date', 'search', '', '', '', 1, '', 'equals', 'search,search|equals,equals|range,range|date,date', '', 1, 0, '', '', '', 1),
		('Index filter options', 'field_dt_filter_options', 0, 0, 0, 'Textarea', '', 0, 'val-1,Value One | val-2,Value Two', '', 'Enter option values for fields like checkboxes and radios. Enter options in ''option-value,Option label'' pairs. Use a pipe(|) to seperate pairs.', '', '', '', '', '', 1, '', 'search', '', '', 1, 0, '', '', '', 1),
		('Index filter default value', 'field_dt_filter_value', 0, 0, 0, 'Textbox', '', 0, '', '', '', '', '', '', '', '', 1, '', 'search', '', '', 1, 0, '', '', '', 1),
		('Bulk Editable', 'field_bulk_edit', 0, 0, 0, 'YesNo', '', 0, '', '', 'Whether this field may be updated in a bulk edit', '', '', '', '', '', 1, 'bool_to_text', 'equals', '1,Yes|0,No', '', 1, 0, '', '', '', 1),
		('Form Flow ID', 'field_form_flow_id', 0, 0, 0, 'Textbox', '', 0, '', '', '', '', '', '', '', '', 1, '', 'search', '', '', 0, 0, '', '', '', 1),
		('Dynamic Value Flow ID', 'field_dynamic_value_flow_id', 0, 0, 0, 'Textbox', '', 0, '', '', '', '', '', '', '', '', 1, '', 'search', '', '', 0, 0, '', '', '', 1),
		('Action Flow ID', 'field_action_flow_id', 0, 0, 0, 'Textbox', '', 0, '', '', '', '', '', '', '', '', 1, '', 'search', '', '', 0, 0, '', '', '', 1),
		('Collaspe Footer', 'collaspe-footer', 0, 1, 1, 'CollaspeFoot', '', 0, '', '', '', '', '', '', '', '', 1, '', 'search', '', '', 0, 0, '', '', '', 1),
		('Collaspe Column Head', 'collaspe-col-head', 0, 1, 1, 'HTML', '', 0, '', '', '', '', '<div class=accordion2col>', '', '', '', 1, '', 'search', '', '', 0, 0, '', '', '', 1),
		('Collaspe Column Foot', 'collaspe-col-foot', 0, 1, 1, 'HTML', '', 0, '', '', '', '', '</div>', '', '', '', 1, '', 'search', '', '', 0, 0, '', '', '', 1),
		('Import', 'datatable_import', 0, 1, 1, 'UploadCSV', '', 0, '', '', '', '', '', '', '', '', 1, '', 'search', '', '', 0, 0, '', '', '', 0)
	;" );
	
	// Deafult Conditionals
	$wpdb->query( "INSERT INTO `{$wpdb->prefix}web_apper_conditions` (`condition_name`, `condition_id`, `condition_left_type`, `condition_left_side`, `condition_operator`, `condition_right_type`, `condition_right_side`, `core_item`) VALUES
		('Field Gen - Index only', 'field-gen-index-only', 'field', 'field_index_only', 'is equal to', 'static', '1', 1),
		('Field Gen - Form only', 'field-gen-form-only', 'field', 'field_form_only', 'is equal to', 'static', '1', 1),
		('Field Gen - Field only', 'field-gen-field-only', 'field', 'field_field_only', 'is equal to', 'static', '0', 1),
		('Field Gen - Field Type', 'field-gen-field-type', 'field', 'field_type', 'is in string', 'static', 'Select Checkbox Radio', 1)
	;" );
	
	// Deafult Flows
	$wpdb->query( "INSERT INTO `{$wpdb->prefix}web_apper_flows` (`flow_name`, `flow_id`, `flow_type`, `flow_code`, `flow_hook`, `core_item`) VALUES
		('Field Gen - Index only', 'field-gen-index-only', 'form', 'IF field-gen-index-only HIDE field_type,field_required,field_placeholder,field_short_desc,field_long_desc,field_options,field_attributes,field_default_value,field_validation,field_regex,field_error_message,field_form_only', '', 1),
		('Field Gen - Form only', 'field-gen-form-only', 'form', 'IF field-gen-form-only HIDE field_dt_show_col_default,field_dt_format_value,field_dt_filter_type,field_dt_filter_options,field_dt_filter_value,field_bulk_edit,field_dynamic_value_flow_id,field_action_flow_id,field_index_only\r\nIF field-gen-form-only SHOW field_field_only', '', 1),
		('Field Gen - Field only', 'field-gen-field-only', 'form', 'IF field-gen-form-only SHOW field_field_only', '', 0);
		('Field Gen - Field Type', 'field-gen-field-type', 'form', 'IF field-gen-field-type SHOW field_options HIDE field_placeholder', '', 1)
	;" );
}, 1, 0 );

// Add custom field type to PFBC
add_action( 'web_apper_add_form_field', function ( $form, $type, $label, $name, $options, $additionalParams ) {
	if ( $type == 'Field' ) $form->addElement( new \PFBC\Element\Field($label, $name, $additionalParams) ); // Captcha
}, 1, 6 );