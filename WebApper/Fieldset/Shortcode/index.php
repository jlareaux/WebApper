<?php

namespace WebApper\Fieldset;

/*
 * [fieldset_builder]
 *
 */
class FieldsetIndex extends \WebApper\IndexView {
	
    /**
     * Define shortcode properties
     *
     */
	protected $item_id = 'fieldset';
	protected $item_label = 'Fieldset';
	protected $shortcode = 'fieldset_builder';
	protected $defaults = array(
		'id' => 'fieldset_builder',
		'include' => 'fieldset_name,fieldset_id,fieldset_field_ids,fieldset_max_clones',
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

$initialize = new FieldsetIndex(); 

?>