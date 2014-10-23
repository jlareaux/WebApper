<?php

/**
 * Add Action
 * 
 * @since WebApper (1.0)
 * @param arr $itemData The Action details
 * @return mixed (int) insert ID on success, (bool) false on fail
 */
function web_apper_insert_action( $itemData ) {
	$itemData = apply_filters( 'action_pre_insert', $itemData ); // Allow filtering of the Action data before saving;

	$item = new WebApper\Action\Action;

	foreach ( $itemData as $key => $val ) :
		$item->$key = $val;
	endforeach;

	if ( $item->save() ) :
		do_action( 'action_post_insert', $item ); // Allow Action data to be hooked onto
		return true;
	else :
		return false;
	endif;
}

/**
 * Update Action
 * 
 * @since WebApper (1.0)
 * @param int $id The Action ID
 * @param arr $itemData The Action details
 * @return bool true on success, false on failure
 */
function web_apper_update_action( $id, $itemData ) {	

	$itemData = apply_filters( 'action_pre_update', $itemData ); // Allow filtering of the Action data before saving;
	
	$item = new WebApper\Action\Action( $id );

	foreach ( $itemData as $key => $val ) :
		$item->$key = $val;
	endforeach;

	if ( $item->save() ) :
		do_action( 'action_post_update', $item ); // Allow Action data to be hooked onto
		return true;
	else :
		return false;
	endif;
}

/**
 * Delete Action
 *
 * @since WebApper (1.0)
 * @param int $id The Action ID
 * @return bool true on success, false on failure
 */
function web_apper_delete_action( $id ) {
	
	do_action( 'action_pre_delete', $id ); // Allow Action data to be hooked onto
	
	$item = new WebApper\Action\Action($id);
	
	if ( $item->delete() ) :
		do_action( 'action_post_delete', $item ); // Allow Action data to be hooked onto
		return true;
	else :
		return false;
	endif;
}

/**
 * Get Action
 * 
 * @since WebApper (1.0)
 * @param int $id The Action ID
 * @return (obj) The Action
 */
function web_apper_get_action( $id ) {
	$item = new WebApper\Action\Action( $id );
	return (object) $item->get_data();
}

/**
 * Get Results
 *
 * @since WebApper (1.0)
 * @return arr Associative array of Action objects
*/
function web_apper_action_get_results( $query ) {
	return $items = WebApper\Action\Action::get_results( $query );
}

/**
 * Queries the DB to get actions by 'action_id'
 *
 * @param array $atts
 * @param string $orderBy
 * @since 1.0
 */
function web_apper_get_action_by_action_id( $action_id ) {
	global $wpdb;
	$item = web_apper_action_get_results(  "SELECT * FROM {$wpdb->prefix}web_apper_actions WHERE action_id = '{$action_id}'" );
	return $item[0];	
}

/**
 * Evaluate action
 *
 * @since WebApper (1.0)
 * @return mixed
*/
function web_apper_action_evaluate( $id ) {
	$item = web_apper_get_action( $id ); // Get the equaltion
	return eval( $item->action_code );
}