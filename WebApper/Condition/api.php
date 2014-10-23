<?php

/**
 * Add Condition
 * 
 * @since WebApper (1.0)
 * @param arr $itemData The Condition details
 * @return mixed (int) insert ID on success, (bool) false on fail
 */
function web_apper_insert_condition( $itemData ) {
	$itemData = apply_filters( 'condition_pre_insert', $itemData ); // Allow filtering of the Condition data before saving;

	$left = $itemData['condition_left_type'];
	$itemData['condition_left_side'] = $_POST['condition_left_' . $left];
	$right = $itemData['condition_right_type'];
	$itemData['condition_right_side'] = $_POST['condition_right_' . $right];

	if ( in_array( $itemData['condition_operator'], array('is empty','is not empty') ) ) :
		$itemData['condition_right_type'] = '';
		$itemData['condition_right_side'] = '';
	endif;

	$item = new WebApper\Condition\Condition;

	foreach ( $itemData as $key => $val ) :
		$item->$key = $val;
	endforeach;

	if ( $item->save() ) :
		do_action( 'condition_post_insert', $item ); // Allow Condition data to be hooked onto
		return true;
	else :
		return false;
	endif;
}

/**
 * Update Condition
 * 
 * @since WebApper (1.0)
 * @param int $id The Condition ID
 * @param arr $itemData The Condition details
 * @return bool true on success, false on failure
 */
function web_apper_update_condition( $id, $itemData ) {	

	$itemData = apply_filters( 'condition_pre_update', $itemData ); // Allow filtering of the Condition data before saving;

	$left = $itemData['condition_left_type'];
	$itemData['condition_left_side'] = $_POST['condition_left_' . $left];
	$right = $itemData['condition_right_type'];
	$itemData['condition_right_side'] = $_POST['condition_right_' . $right];

	if ( in_array( $itemData['condition_operator'], array('is empty','is not empty') ) ) :
		$itemData['condition_right_type'] = '';
		$itemData['condition_right_side'] = '';
	endif;
	
	$item = new WebApper\Condition\Condition( $id );

	foreach ( $itemData as $key => $val ) :
		$item->$key = $val;
	endforeach;

	if ( $item->save() ) :
		do_action( 'condition_post_update', $item ); // Allow Condition data to be hooked onto
		return true;
	else :
		return false;
	endif;
}

/**
 * Delete Condition
 *
 * @since WebApper (1.0)
 * @param int $id The Condition ID
 * @return bool true on success, false on failure
 */
function web_apper_delete_condition( $id ) {
	
	do_action( 'condition_pre_delete', $id ); // Allow Condition data to be hooked onto
	
	$item = new WebApper\Condition\Condition($id);
	
	if ( $item->delete() ) :
		do_action( 'condition_post_delete', $item ); // Allow Condition data to be hooked onto
		return true;
	else :
		return false;
	endif;
}

/**
 * Get Condition
 * 
 * @since WebApper (1.0)
 * @param int $id The Condition ID
 * @return (obj) The Condition
 */
function web_apper_get_condition( $id ) {
	$item = new WebApper\Condition\Condition( $id );
	return (object) $item->get_data();
}

/**
 * Get Results
 *
 * @since WebApper (1.0)
 * @return arr Associative array of Condition objects
*/
function web_apper_condition_get_results( $query ) {
	return $items = WebApper\Condition\Condition::get_results( $query );
}

/**
 * Queries the DB to get conditions by 'condition_id'
 *
 * @param array $atts
 * @param string $orderBy
 * @since 1.0
 */
function web_apper_get_condition_by_condition_id( $condition_id ) {
	global $wpdb;
	$item = web_apper_condition_get_results(  "SELECT * FROM {$wpdb->prefix}web_apper_conditions WHERE condition_id = '{$condition_id}'" );
	return $item[0];	
}

/**
 * Evaluate Condition
 *
 * @since WebApper (1.0)
 * @return mixed
*/
function web_apper_condition_evaluate( $condition_id ) {
	$item = web_apper_get_condition_by_condition_id( $condition_id ); // Get the condition
	
	if ( $item['condition_left_type'] == 'field' ) :
	elseif ( $item['condition_left_type'] == 'equation' ) :
	elseif ( $item['condition_left_type'] == 'static' ) :
		$left_value = $item['condition_left_side'];
	endif;

	if ( $item['condition_right_type'] == 'field' ) :
	elseif ( $item['condition_right_type'] == 'equation' ) :
	elseif ( $item['condition_right_type'] == 'static' ) :
		$right_value = $item['condition_right_side'];
	endif;

	if ( $item['condition_operator'] == 'is equal to' ) :
		if ( $left_value == $right_value ) :
			return true;
		endif;
	elseif ( $item['condition_operator'] == 'is not equal to' ) :
		if ( $left_value != $right_value ) :
			return true;
		endif;
	elseif ( $item['condition_operator'] == 'is lesser than' ) :
		if ( $left_value < $right_value ) :
			return true;
		endif;
	elseif ( $item['condition_operator'] == 'is greater than' ) :
		if ( $left_value > $right_value ) :
			return true;
		endif;
	elseif ( $item['condition_operator'] == 'is lesser than or equal to' ) :
		if ( $left_value <= $right_value ) :
			return true;
		endif;
	elseif ( $item['condition_operator'] == 'is greater than or equal to' ) :
		if ( $left_value >= $right_value ) :
			return true;
		endif;
	elseif ( $item['condition_operator'] == 'is empty' ) :
		if ( empty($left_value) ) :
			return true;
		endif;
	elseif ( $item['condition_operator'] == 'is not empty' ) :
		if ( !empty($left_value) ) :
			return true;
		endif;
	elseif ( $item['condition_operator'] == 'is in string' ) :
		if ( stristr($right_value, $left_value) ) :
			return true;
		endif;
	elseif ( $item['condition_operator'] == 'is not in string' ) :
		if ( !stristr($right_value, $left_value) ) :
			return true;
		endif;
	endif;
}

/**
 * Build Condition javascript
 *
 * @since WebApper (1.0)
 * @return mixed
*/
function web_apper_build_condition_js_vars( $condition_id ) {
	$item = web_apper_get_condition_by_condition_id( $condition_id ); // Get the condition

	if ( $item['condition_left_type'] == 'field' ) :
		$vars_js = "
			// Get the Node
			if ( jQuery('form #" . $item['condition_left_side'] . "').length === 1 ) {
				var sToggleField = 'form #" . $item['condition_left_side'] . "';
			} else {
				var sToggleField = 'form [name=\"" . $item['condition_left_side'] . "\"]';
			}
			// Get the value depending on input type
			if ( jQuery( sToggleField ).attr('type') == 'radio' || jQuery( sToggleField ).attr('type') == 'checkbox' ) {
				var left_value = jQuery( sToggleField + ':checked' ).val();
			} else {
				var left_value = jQuery( sToggleField ).val();
			}
		";
	elseif ( $item['condition_left_type'] == 'equation' ) :
	elseif ( $item['condition_left_type'] == 'static' ) :
		$vars_js = "var left_value = '" . $item['condition_left_side'] . "';
		";
	endif;

	if ( $item['condition_right_type'] == 'field' ) :
		$vars_js .= "
			// Get the Node
			if ( jQuery('form #" . $item['condition_right_side'] . "').length === 1 ) {
				var sToggleField = 'form #" . $item['condition_right_side'] . "';
			} else {
				var sToggleField = 'form [name=\"" . $item['condition_right_side'] . "\"]';
			}
			// Get the value depending on input type
			if ( jQuery( sToggleField ).attr('type') == 'radio' || jQuery( sToggleField ).attr('type') == 'checkbox' ) {
				var left_value = jQuery( sToggleField + ':checked' ).val();
			} else {
				var left_value = jQuery( sToggleField ).val();
			}
		";
	elseif ( $item['condition_right_type'] == 'equation' ) :
	elseif ( $item['condition_right_type'] == 'static' ) :
		$vars_js .= "var right_value = '" . $item['condition_right_side'] . "';
		";
	endif;

	return $vars_js;
}

/**
 * Build Condition javascript
 *
 * @since WebApper (1.0)
 * @return mixed
*/
function web_apper_build_condition_js( $condition_id ) {
	$item = web_apper_get_condition_by_condition_id( $condition_id ); // Get the condition

	if ( $item['condition_operator'] == 'is equal to' ) :
		$condition_js = "left_value == right_value";
	elseif ( $item['condition_operator'] == 'is not equal to' ) :
		$condition_js = "left_value != right_value";
	elseif ( $item['condition_operator'] == 'is lesser than' ) :
		$condition_js = "left_value < right_value";
	elseif ( $item['condition_operator'] == 'is greater than' ) :
		$condition_js = "left_value > right_value";
	elseif ( $item['condition_operator'] == 'is lesser than or equal to' ) :
		$condition_js = "left_value <= right_value";
	elseif ( $item['condition_operator'] == 'is greater than or equal to' ) :
		$condition_js = "left_value >= right_value";
	elseif ( $item['condition_operator'] == 'is empty' ) :
		$condition_js = "left_value == ''";
	elseif ( $item['condition_operator'] == 'is not empty' ) :
		$condition_js = "left_value != ''";
	elseif ( $item['condition_operator'] == 'is in string' ) :
		$condition_js = "0 <= right_value.search(left_value)";
	elseif ( $item['condition_operator'] == 'is not in string' ) :
		$condition_js = "right_value.search(left_value) < 0";
	endif;
	return $condition_js;
}