<?php

/**
 * Add Flow
 * 
 * @since WebApper (1.0)
 * @param arr $itemData The Flow details
 * @return mixed (int) insert ID on success, (bool) false on fail
 */
function web_apper_insert_flow( $itemData ) {
	$itemData = apply_filters( 'flow_pre_insert', $itemData ); // Allow filtering of the Flow data before saving;

	$item = new WebApper\Flow\Flow;

	foreach ( $itemData as $key => $val ) :
		$item->$key = $val;
	endforeach;

	if ( $item->save() ) :
		do_action( 'flow_post_insert', $item ); // Allow Flow data to be hooked onto
		return true;
	else :
		return false;
	endif;
}

/**
 * Update Flow
 * 
 * @since WebApper (1.0)
 * @param int $id The Flow ID
 * @param arr $itemData The Flow details
 * @return bool true on success, false on failure
 */
function web_apper_update_flow( $id, $itemData ) {	

	$itemData = apply_filters( 'flow_pre_update', $itemData ); // Allow filtering of the Flow data before saving;
	
	$item = new WebApper\Flow\Flow( $id );

	foreach ( $itemData as $key => $val ) :
		$item->$key = $val;
	endforeach;

	if ( $item->save() ) :
		do_action( 'flow_post_update', $item ); // Allow Flow data to be hooked onto
		return true;
	else :
		return false;
	endif;
}

/**
 * Delete Flow
 *
 * @since WebApper (1.0)
 * @param int $id The Flow ID
 * @return bool true on success, false on failure
 */
function web_apper_delete_flow( $id ) {
	
	do_action( 'flow_pre_delete', $id ); // Allow Flow data to be hooked onto
	
	$item = new WebApper\Flow\Flow($id);
	
	if ( $item->delete() ) :
		do_action( 'flow_post_delete', $item ); // Allow Flow data to be hooked onto
		return true;
	else :
		return false;
	endif;
}

/**
 * Get Flow
 * 
 * @since WebApper (1.0)
 * @param int $id The Flow ID
 * @return (obj) The Flow
 */
function web_apper_get_flow( $id ) {
	$item = new WebApper\Flow\Flow( $id );
	return (object) $item->get_data();
}

/**
 * Get Results
 *
 * @since WebApper (1.0)
 * @return arr Associative array of Flow objects
*/
function web_apper_flow_get_results( $query ) {
	return $items = WebApper\Flow\Flow::get_results( $query );
}

/**
 * Queries the DB to get flow by 'flow_id'
 *
 * @param array $atts
 * @param string $orderBy
 * @since 1.0
 */
function web_apper_get_flow_by_flow_id( $flow_id ) {
	global $wpdb;
	$item = web_apper_flow_get_results(  "SELECT * FROM {$wpdb->prefix}web_apper_flows WHERE flow_id = '{$flow_id}'" );
	return $item[0];	
}

/**
 * Evaluate Flow
 *
 * @since WebApper (1.0)
 * @return mixed
*/
function web_apper_flow_evaluate( $flow_id  ) {
	$field_flow =  web_apper_get_flow_by_flow_id( $flow_id  );
	preg_match_all( "%IF\s(.+?\sTHEN\s.+?)\s%", $field_flow['flow_code'], $field_flows );
	preg_match( "%ELSE\s(.*)%", $field_flow['flow_code'], $default );
	foreach ( $flows[1] as $flow ) :
		$parts = explode( 'THEN', $flow );
		if ( web_apper_condition_evaluate(trim($parts[0])) ) :
			if ( $field_flow['flow_type'] == 'action' ) :
				$action = web_apper_get_action_by_action_id( trim($parts[1]) );
				return web_apper_action_evaluate( $action['ID'] );
			elseif ( $field_flow['flow_type'] == 'dynamic value' ) :
				$equation = web_apper_get_equation_by_equation_id( trim($parts[1]) );
				return web_apper_equation_evaluate( $equation['ID'] );
			endif;
		endif;
	endforeach;
	// ELSE default
	if ( $field_flow['flow_type'] == 'action' ) :
		$action = web_apper_get_action_by_action_id( trim($default[1]) );
		return eval( $action['action_code'] );
	elseif ( $field_flow['flow_type'] == 'dynamic value' ) :
		$equation = web_apper_get_equation_by_equation_id( trim($default[1]) );
		return web_apper_equation_evaluate( $equation['ID'] );
	endif;
}

/**
 * Build Flow javascript
 *
 * @since WebApper (1.0)
 * @return mixed
*/
function web_apper_build_flow_js( $field ) {
	$field_flow =  web_apper_get_flow_by_flow_id( $field['field_form_flow_id']  );
	preg_match_all( "%(IF|ELSEIF)\s{0,}([^\s]+?)\s{0,}(SHOW|HIDE)\s{0,}([^\s]*)%", $field_flow['flow_code'], $flows );
	preg_match( "%ELSE\s{0,}(SHOW|HIDE)\s{0,}([^\s]*)%", $field_flow['flow_code'], $default );
	$default_toggle_fields = array();
	if ( !empty($default) ) :
		$default_toggle_fields = explode(',', $default[1]);
	endif;

	echo '<script type="text/javascript">
		jQuery(document).ready(function($) {	
	';
	
	$flow_js = "
		function condition_" . str_replace('-', '_', $field['field_form_flow_id']) . "() {

			var show = [];
			var hide = [];
	";
	foreach ( $flows[0] as $flow ) :
		preg_match( "%(SHOW|HIDE)%", $flow, $toggle );
		preg_match( "%(IF|ELSEIF)\s{0,}([^\s]+?)\s{0,}(SHOW|HIDE)%", $flow, $condition );
		$condition = $condition[2];
		preg_match( "%(SHOW|HIDE)\s{0,}([^\s]*)%", $flow, $toggle_fields );
		$toggle_fields = explode(',', $toggle_fields[2]);
		$flow_js_show = '';
		$flow_js_hide = '';
		$default_flow_js_show = '';
		$default_flow_js_hide = '';

		foreach ( $toggle_fields as $toggle_field ) :
			$flow_js_show .= "show.push('" . trim($toggle_field) . "');
			";
			$flow_js_hide .= "hide.push('" . trim($toggle_field) . "');
			";
		endforeach;
		
		foreach ( $default_toggle_fields as $default_toggle_field ) :
			$default_flow_js_show .= "show.push('" . trim($default_toggle_field) . "');
			";
			$default_flow_js_hide .= "hide.push('" . trim($default_toggle_field) . "');
			";
		endforeach;
		
		if ( $toggle[0] == 'SHOW' ) :
			$toggle_js_true = $flow_js_show . $default_flow_js_hide;
			$toggle_js_false = $default_flow_js_show . $flow_js_hide;
		else :
			$toggle_js_true = $flow_js_hide . $default_flow_js_show;
			$toggle_js_false = $default_flow_js_hide . $flow_js_show;
		endif;
		


		$flow_js .= web_apper_build_condition_js_vars( trim($condition) );
		$flow_js .= "
			if ( " . web_apper_build_condition_js( trim($condition) ) . " ) {
		" . $toggle_js_true . "
			} else {
		" . $toggle_js_false . "
			}
		";
		endforeach;
		$flow_js .= "
		//	console.log(show);
		//	console.log(hide);
		//	console.log('===');
				showHideFields( show, hide, '" . $toggle[0] . "' );
			}
		";
		
		echo $flow_js . "
			condition_" . str_replace('-', '_', $field['field_form_flow_id']) . "();
			jQuery('form [name=\"" . $field['field_id'] . "\"]').live('change keyup', function() {
				condition_" . str_replace('-', '_', $field['field_form_flow_id']) . "();
			});
		});
		";
	echo '</script>';
}