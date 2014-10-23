<?php

namespace WebApper\Equation;

/*
 * [equation_builder]
 *
 */
class EquationIndex extends \WebApper\IndexView {
	
    /**
     * Define shortcode properties
     *
     */
	protected $item_id = 'equation';
	protected $item_label = 'Equation';
	protected $shortcode = 'equation_builder';
	protected $defaults = array(
		'id' => 'equation_builder',
		'include' => 'equation_name,equation_id,equation_type,equation_code',
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

$initialize = new EquationIndex(); 

?>