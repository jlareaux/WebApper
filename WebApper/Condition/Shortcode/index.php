<?php

namespace WebApper\Condition;

/*
 * [condition_builder]
 *
 */
class ConditionIndex extends \WebApper\IndexView {
	
    /**
     * Define shortcode properties
     *
     */
	protected $item_id = 'condition';
	protected $item_label = 'Condition';
	protected $shortcode = 'condition_builder';
	protected $defaults = array(
		'id' => 'condition_builder',
		'include' => 'condition_name,condition_id,condition_left_type,condition_left_field,condition_left_equation,condition_left_static,condition_left_side,condition_operator,condition_right_type,condition_right_field,condition_right_equation,condition_right_static,condition_right_side',
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

$initialize = new ConditionIndex(); 

?>