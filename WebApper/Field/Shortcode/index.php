<?php

namespace WebApper\Field;

/*
 * [field_builder]
 *
 */
class FieldIndex extends \WebApper\IndexView {
	
    /**
     * Define shortcode properties
     *
     */
	protected $item_id = 'field';
	protected $item_label = 'Field';
	protected $shortcode = 'field_builder';
	protected $defaults = array(
		'id' => 'field_builder',
		'include' => 'field_name,field_id,field_index_only,field_form_only,field_field_only,field_type,field_required,field_placeholder,field_short_desc,field_long_desc,field_options,field_attributes,field_default_value,field_validation,field_regex,field_error_message,field_read_only,field_dt_show_col_default,field_dt_format_value,field_dt_filter_type,field_dt_filter_options,field_dt_filter_value,field_bulk_edit,field_form_flow_id,field_dynamic_value_flow_id,field_action_flow_id',
		'viewcap' => 'edit_posts',	// The Required capability to view
		'addcap' => 'publish_posts',	 // The Required capability to add
		'editcap' => 'edit_posts',	// The Required capability to edit
		'deletecap' => 'delete_plugins', // The Required capability to delete
		'colvis_control' => true, // Enable the colVis button for the dataTable, true or false
		'form_controls' => true, // Enable the form controls for the dataTable, true or false
		'colfilter_controls' => true, // Enable the column filters for the dataTable, true or false
		'actions_control' => true, // 
		'adtl_actions' => 'div,div|SelectAll,Select All|ResetFilters,Reset Filters', // Specify addition action buttons for the dataTable
		'row_selection' => true, // Enable dataTable row selection, true or false.
		'rightclick_menu' => true, // Enable rightclick menu on dataTable rows, true or false.
	);

}

$initialize = new FieldIndex(); 

?>