<?php

/**
 * Add Equation
 * 
 * @since WebApper (1.0)
 * @param arr $itemData The Equation details
 * @return mixed (int) insert ID on success, (bool) false on fail
 */
function web_apper_insert_equation( $itemData ) {
	$itemData = apply_filters( 'equation_pre_insert', $itemData ); // Allow filtering of the Equation data before saving;

	$item = new WebApper\Equation\Equation;

	foreach ( $itemData as $key => $val ) :
		$item->$key = $val;
	endforeach;

	if ( $item->save() ) :
		do_action( 'equation_post_insert', $item ); // Allow Equation data to be hooked onto
		return true;
	else :
		return false;
	endif;
}

/**
 * Update Equation
 * 
 * @since WebApper (1.0)
 * @param int $id The Equation ID
 * @param arr $itemData The Equation details
 * @return bool true on success, false on failure
 */
function web_apper_update_equation( $id, $itemData ) {	

	$itemData = apply_filters( 'equation_pre_update', $itemData ); // Allow filtering of the Equation data before saving;
	
	$item = new WebApper\Equation\Equation( $id );

	foreach ( $itemData as $key => $val ) :
		$item->$key = $val;
	endforeach;

	if ( $item->save() ) :
		do_action( 'equation_post_update', $item ); // Allow Equation data to be hooked onto
		return true;
	else :
		return false;
	endif;
}

/**
 * Delete Equation
 *
 * @since WebApper (1.0)
 * @param int $id The Equation ID
 * @return bool true on success, false on failure
 */
function web_apper_delete_equation( $id ) {
	
	do_action( 'equation_pre_delete', $id ); // Allow Equation data to be hooked onto
	
	$item = new WebApper\Equation\Equation($id);
	
	if ( $item->delete() ) :
		do_action( 'equation_post_delete', $item ); // Allow Equation data to be hooked onto
		return true;
	else :
		return false;
	endif;
}

/**
 * Get Equation
 * 
 * @since WebApper (1.0)
 * @param int $id The Equation ID
 * @return (obj) The Equation
 */
function web_apper_get_equation( $id ) {
	$item = new WebApper\Equation\Equation( $id );
	return (object) $item->get_data();
}

/**
 * Get Results
 *
 * @since WebApper (1.0)
 * @return arr Associative array of Equation objects
*/
function web_apper_equation_get_results( $query ) {
	return $items = WebApper\Equation\Equation::get_results( $query );
}

/**
 * Queries the DB to get equations by 'equation_id'
 *
 * @param array $atts
 * @param string $orderBy
 * @since 1.0
 */
function web_apper_get_equation_by_equation_id( $equation_id ) {
	global $wpdb;
	$item = web_apper_equation_get_results(  "SELECT * FROM {$wpdb->prefix}web_apper_equations WHERE equation_id = '{$equation_id}'" );
	return $item[0];	
}

/**
 * Evaluate equation
 *
 * @since WebApper (1.0)
 * @return mixed
*/
function web_apper_equation_evaluate( $id ) {
	$item = web_apper_get_equation( $id ); // Get the equaltion
	return eval( "return " . $item->equation_code . ";");
}