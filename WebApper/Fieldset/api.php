<?php

/**
 * Add Fieldset
 * 
 * @since WebApper (1.0)
 * @param arr $itemData The Fieldset details
 * @return mixed (int) insert ID on success, (bool) false on fail
 */
function web_apper_insert_fieldset( $itemData ) {
	$itemData = apply_filters( 'fieldset_pre_insert', $itemData ); // Allow filtering of the Fieldset data before saving;

	$item = new WebApper\Fieldset\Fieldset;

	foreach ( $itemData as $key => $val ) :
		$item->$key = $val;
	endforeach;

	if ( $item->save() ) :
		do_action( 'fieldset_post_insert', $item ); // Allow Fieldset data to be hooked onto
		return true;
	else :
		return false;
	endif;
}

/**
 * Update Fieldset
 * 
 * @since WebApper (1.0)
 * @param int $id The Fieldset ID
 * @param arr $itemData The Fieldset details
 * @return bool true on success, false on failure
 */
function web_apper_update_fieldset( $id, $itemData ) {	

	$itemData = apply_filters( 'fieldset_pre_update', $itemData ); // Allow filtering of the Fieldset data before saving;
	
	$item = new WebApper\Fieldset\Fieldset( $id );

	foreach ( $itemData as $key => $val ) :
		$item->$key = $val;
	endforeach;

	if ( $item->save() ) :
		do_action( 'fieldset_post_update', $item ); // Allow Fieldset data to be hooked onto
		return true;
	else :
		return false;
	endif;
}

/**
 * Delete Fieldset
 *
 * @since WebApper (1.0)
 * @param int $id The Fieldset ID
 * @return bool true on success, false on failure
 */
function web_apper_delete_fieldset( $id ) {
	
	do_action( 'fieldset_pre_delete', $id ); // Allow Fieldset data to be hooked onto
	
	$item = new WebApper\Fieldset\Fieldset($id);
	
	if ( $item->delete() ) :
		do_action( 'fieldset_post_delete', $item ); // Allow Fieldset data to be hooked onto
		return true;
	else :
		return false;
	endif;
}

/**
 * Get Fieldset
 * 
 * @since WebApper (1.0)
 * @param int $id The Fieldset ID
 * @return (obj) The Fieldset
 */
function web_apper_get_fieldset( $id ) {
	$item = new WebApper\Fieldset\Fieldset( $id );
	return (object) $item->get_data();
}

/**
 * Get Results
 *
 * @since WebApper (1.0)
 * @return arr Associative array of Fieldset objects
*/
function web_apper_fieldset_get_results( $query ) {
	return $items = WebApper\Fieldset\Fieldset::get_results( $query );
}

/**
 * Queries the DB to get fieldsets by 'fieldset_id'
 *
 * @param array $atts
 * @param string $orderBy
 * @since 1.0
 */
function web_apper_get_fieldset_by_fieldset_id( $fieldset_id ) {
	global $wpdb;
	$item = web_apper_fieldset_get_results(  "SELECT * FROM {$wpdb->prefix}web_apper_fieldsets WHERE fieldset_id = '{$fieldset_id}'" );
	return $item[0];	
}