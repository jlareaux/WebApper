<?php

/**
 * Add Attachment
 * 
 * @since WebApper (1.0)
 * @param arr $itemData The Attachment details
 * @return mixed (int) insert ID on success, (bool) false on fail
 */
function web_apper_insert_attachment( $itemData ) {
	$itemData = apply_filters( 'attachment_pre_insert', $itemData ); // Allow filtering of the Attachment data before saving;

	$item = new WebApper\Attachment\Attachment;

	foreach ( $itemData as $key => $val ) :
		$item->$key = $val;
	endforeach;

	if ( $item->save() ) :
		do_action( 'attachment_post_insert', $item ); // Allow Attachment data to be hooked onto
		return $item->ID;
	else :
		return false;
	endif;
}

/**
 * Update Attachment
 * 
 * @since WebApper (1.0)
 * @param int $id The Attachment ID
 * @param arr $itemData The Attachment details
 * @return bool true on success, false on failure
 */
function web_apper_update_attachment( $id, $itemData ) {	

	$itemData = apply_filters( 'attachment_pre_update', $itemData ); // Allow filtering of the Attachment data before saving;
	
	$item = new WebApper\Attachment\Attachment( $id );

	foreach ( $itemData as $key => $val ) :
		$item->$key = $val;
	endforeach;

	if ( $item->save() ) :
		do_action( 'attachment_post_update', $item ); // Allow Attachment data to be hooked onto
		return true;
	else :
		return false;
	endif;
}

/**
 * Delete Attachment
 *
 * @since WebApper (1.0)
 * @param int $id The Attachment ID
 * @return bool true on success, false on failure
 */
function web_apper_delete_attachment( $id ) {
	
	do_action( 'attachment_pre_delete', $id ); // Allow Attachment data to be hooked onto
	
	$item = new WebApper\Attachment\Attachment($id);
	
	if ( $item->delete() ) :
		do_action( 'attachment_post_delete', $item ); // Allow Attachment data to be hooked onto
		return true;
	else :
		return false;
	endif;
}

/**
 * Get Attachment
 * 
 * @since WebApper (1.0)
 * @param int $id The Attachment ID
 * @return (obj) The Attachment
 */
function web_apper_get_attachment( $id ) {
	$item = new WebApper\Attachment\Attachment( $id );
	return (object) $item->get_data();
}

/**
 * Get Results
 *
 * @since WebApper (1.0)
 * @return arr Associative array of Attachment objects
*/
function web_apper_attachment_get_results( $query ) {
	return $items = WebApper\Attachment\Attachment::get_results( $query );
}