<?php

/**
 * Add a Field
 * 
 * @since WebApper (1.0)
 * @param arr $itemData The field details
 * @return mixed (int) insert ID on success, (bool) false on fail
 */
function web_apper_insert_field( $itemData ) {
	$itemData = apply_filters( 'field_pre_insert', $itemData ); // Allow filtering of the field data before saving the field;

	$item = new WebApper\Field\Field;

	foreach ( $itemData as $key => $val ) :
		$item->$key = $val;
	endforeach;

	if ( $item->save() ) :
		do_action( 'field_post_insert', $item ); // Allow field data to be hooked onto
		return true;
	else :
		return false;
	endif;
}

/**
 * Update an field
 * 
 * @since WebApper (1.0)
 * @param int $id The field ID
 * @param arr $itemData The field details
 * @return bool true on success, false on failure
 */
function web_apper_update_field( $id, $itemData ) {	

	$itemData = apply_filters( 'field_pre_update', $itemData ); // Allow filtering of the Field data before saving the field;
	
	$item = new WebApper\Field\Field( $id );

	foreach ( $itemData as $key => $val ) :
		$item->$key = $val;
	endforeach;

	if ( $item->save() ) :
		do_action( 'field_post_update', $item ); // Allow Field data to be hooked onto
		return true;
	else :
		return false;
	endif;
}

/**
 * Delete an field
 *
 * @since WebApper (1.0)
 * @param int $id The field ID
 * @return bool true on success, false on failure
 */
function web_apper_delete_field( $id ) {
	
	do_action( 'field_pre_delete', $id ); // Allow Field data to be hooked onto
	
	$item = new WebApper\Field\Field($id);
	
	if ( $item->field_core_item != 1 ) :
		if ( $item->delete() ) :
			do_action( 'field_post_delete', $item ); // Allow Field data to be hooked onto
			return true;
		else :
			return false;
		endif;
	else :
		return 'core_item';
	endif;
}

/**
 * Get an field
 * 
 * @since WebApper (1.0)
 * @param int $id The field ID
 * @return (obj) The field
 */
function web_apper_get_field( $id ) {
	$item = new WebApper\Field\Field( $id );
	return (object) $item->get_data();
}

/**
 * Get Results
 *
 * @since WebApper (1.0)
 * @return arr Associative array of Field objects
*/
function web_apper_field_get_results( $query ) {
	return $items = WebApper\Field\Field::get_results( $query );
}

/**
 * Queries the DB to get custom fields by 'field_id'
 *
 * @param array $atts
 * @param string $orderBy
 * @since 1.0
 */
function web_apper_get_field_by_field_id( $field_id ) {
	global $wpdb;
	$item = web_apper_field_get_results(  "SELECT * FROM {$wpdb->prefix}web_apper_fields WHERE field_id = '{$field_id}'" );
	return $item[0];	
}

/**
 * Queries the DB to get custom fields
 *
 * @param array $atts
 * @param string $orderBy
 * @since 1.0
 */
function web_apper_get_fields( $include, $only_fields = false ) {
	foreach ( explode(',', $include) as $include ) :
		if ( preg_match('%\{.+?\}%', $include) ) :
			global $wpdb;
			$fieldset_id = trim($include, '{}');
			$fieldset = web_apper_get_fieldset_by_fieldset_id( $fieldset_id );
			for ( $i = 1; $i <= $fieldset['fieldset_max_clones']; $i++ ) :
				foreach ( explode(',', $fieldset['fieldset_field_ids'] ) as $field_id ) :
					$field = web_apper_get_field_by_field_id( $field_id );
					$field['field_fieldset'] = $fieldset_id . $i;
					if ( $i == 1 ) :
						$field['field_primaryset'] = true;
					else :
						$field['field_id'] = $field['field_id'] . '_' . $i;
						$field['field_name'] = $field['field_name'] . ' ' . $i;
						$field['field_required'] = 0;
					endif;
					$fields[] = $field;
				endforeach;
			endfor;
			$fields[] = array( 'field_default_value' => '
				<div class="control-group"><div class="controls"><button id="' . $fieldset_id . '" type="button" class="btn" data-showfieldset="' . $fieldset_id . '" data-clone-id="2">Add ' . $fieldset['fieldset_name'] . '</button></div></div>
				<script type="text/javascript">
					jQuery("[data-fieldset]").each(function(index, element) {
						if ( jQuery(this).parent().parent().hasClass("control-group") ) {
							jQuery(this).parent().parent().fadeOut( 0 );
						} else {
							jQuery(this).fadeOut( 0 );
							jQuery(this).prev().fadeOut( 0 );
						}
					});

					jQuery("button#' . $fieldset_id . '").on("click", function() {
						var i = jQuery(this).data("clone-id");
						jQuery("[data-fieldset=\'" + jQuery(this).data("showfieldset") + i +"\']").each(function(index, element) {
							if ( jQuery(this).parent().parent().hasClass("control-group") ) {
								jQuery(this).parent().parent().fadeIn( 400 );
							} else {
								jQuery(this).fadeIn( 400 );
								jQuery(this).prev().fadeIn( 400 );
							}
						});
						if ( i == ' . $fieldset['fieldset_max_clones'] . ' ) {
							jQuery(this).delay( 400 ).fadeOut( 400 );
						} else {
							i++;
							jQuery(this).data("clone-id", i);
						}
					});
				</script>
			', 'field_type' => 'HTML', 'field_form_only' => 1, );
		else :
			$fields[] = web_apper_get_field_by_field_id( $include );
		endif;
	endforeach;
	return $fields;
}

/**
 * Save custom fields from array of ids
 *
 * @since 1.0
 */
function web_apper_update_fields( $postID, $include ) {
	foreach ( explode(',', $include) as $include ) :
		if ( preg_match('%\{.+?\}%', $include) ) :
			global $wpdb;
			$fieldset_id = trim($include, '{}');
			$fieldset = web_apper_get_fieldset_by_fieldset_id( $fieldset_id );
			for ( $i = 1; $i <= $fieldset['fieldset_max_clones']; $i++ ) :
				foreach ( explode(',', $fieldset['fieldset_field_ids'] ) as $field_id ) :
					if ( 1 < $i ) :
						$field_ids[] = $field_id . '_' . $i;
					else :
						$field_ids[] = $field_id;
					endif;
				endforeach;
			endfor;
		else :
			$field_ids[] = $include;
		endif;
	endforeach;

	foreach ( $field_ids as $fieldID ) :
		if ( isset($_POST[$fieldID]) ) :
			$value = $_POST[$fieldID];
			if ( is_array( $value ) ) :
				$value = serialize( $value );
			endif;
			update_post_meta( $postID, $fieldID, $value );
		endif;
	endforeach;
}