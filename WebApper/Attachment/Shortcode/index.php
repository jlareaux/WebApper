<?php

namespace WebApper\Attachment;

/*
 * [attachments]
 *
 */
class AttachmentIndex extends \WebApper\IndexView {
	
    /**
     * Define shortcode properties
     *
     */
	protected $shortcode = 'attachments';
	protected $defaults = array(
		'id' => 'attachments',
		'viewcap' => 'edit_posts',	// The Required capability to view
		'deletecap' => 'edit_posts', // The Required capability to delete
		'include' => 'attachment_item_id,attachment_type,attachment_file_path,attachment_file_name,attachment_created_on',
	);

    /**
     * Handles the shortcode
     *
     * @param array $atts
     */
    public function shortcode( $atts ) {

		// Allow filtering of shortcode attributes before rendering
		$atts = apply_filters( $this->shortcode . '_atts', $atts, $id );

		// Extract shortcode attributes into individual vars and also store as an array 
		extract( $atts = shortcode_atts( $this->defaults, $atts ) );

		// Check for required shortcode attributes
		$msg = $this->has_req_attrs( $atts );
		if ( $msg !== true )
			return $msg;

        // Check if current user has proper privileges to view
		if ( !$this->current_user_has_cap($viewcap) ) :
			echo 'You do not have sufficient permissions to access this content.';
			return;
		endif;

		// Get the fields
		$this->fields = web_apper_get_fields( $atts['include'] );

		// Build the shortcode output hrml string
		?>

		<div class="row">
			<div class="span<?php echo $this->template_content_span(); ?>">
				<?php
					$adtl_actions = array( 'div' => 'div', 'SelectAll' => 'Select All', 'ResetFilters' => 'Reset Filters' ); // Specify addition action buttons for the dataTable
					$this->build_dt_actions_control( $id, $atts, $adtl_actions ); // Echos the action buttons HTML for the dataTable
					$this->build_dt_colvis_control(); // Echos the colVis button HTML for the dataTable
					$this->build_dt_form_controls(); // Echos the form controls HTML for the dataTable
				?>
			</div>
		</div>

		<div class="row">
			<div class="span<?php echo $this->template_content_span(); ?>">
				<?php
					$ajax_data = array( 'include' => $atts['include'] );
					$this->build_dt( $atts, true, $ajax_data ); // Echos the dataTable HTML
				?>
			</div>
		</div>

		<?php
			$this->build_dt_modal_wrapper( $id ); // Echos the Modal wrapper HTML
			$this->build_dt_rightclick_menu( $id ); // Echos the browser context menu HTML
		 ?>

		<script type="text/javascript">

			jQuery(document).ready(function($) {
				<?php $this->build_dt_row_selection( $id, true ); // Echos dataTable Row selection JS, specify true for multi-select ?>
			});

			// Handle response from ajax post
			function parseResponse<?php echo $id; ?>(response) {
				var result = jQuery.parseJSON(response);  // Parse response
				if ( result.success ) {  // If ajax returns a successful save
					table.api().ajax.reload(); // Reload the dataTable
				}
				jQuery('#<?php echo $id; ?>Modal').modal('hide'); // Hide the Modal
				jQuery('.page-content').prepend(result.htmlalert);  // Show and alert
				jQuery('#<?php echo $id; ?>SelectAll').text('Select All'); // Reset the 'Select All' button
			}

		</script>
		<?php
	}

    /**
     * Get Attachments from the database
     *
     * @since 1.0
     */
	protected function get_records() {
		$columns[] = array(
			'db' => 'ID', 'dt' => 'DT_RowData',
			'formatter' => function( $d, $row ) {
				return array(
					'item-id' => $d,
				);
			}
		);
		// Get the fields
		$this->fields = web_apper_get_fields( $_POST['web_apper_include'] );
		foreach ( $this->fields as $field ) :
			if ( $field['field_form_only'] != true ) :
				$column['db'] = $field['field_id'];
				$column['dt'] = $field['field_id'];
				if ( !empty( $field['field_dt_format_value'] ) ) :
					$column['formatter'] = $this->dt_format_value( $field['field_dt_format_value'] );
				else :
					unset( $column['formatter'] );
				endif;
				$columns[] = $column;
			endif;
		endforeach;
		require_once( dirname( dirname( dirname( __FILE__ ) ) ) . '/SSP.php' );
		global $wpdb;		
		echo json_encode(
			\SSP::simple( $_POST, $wpdb->prefix . 'web_apper_attachments', 'ID', $columns ) // $_GET, $sql_details, $table, $primaryKey, $columns
		);
	}

    /**
     * Echos a form for the dataTable
     *
     * @since 1.0
     */
	public function get_form() {
		// Set form values
		$id = $_POST['id']; // The shortcode ID
		if ( isset($_POST['web_apper_item_ids']) ) : // If rows are being edited
			if ( 1 < count($_POST['web_apper_item_ids']) ) : // If 1 rows is being edited
				$itemID = implode( ',' , $_POST['web_apper_item_ids'] );
				$modal_heading = 'Edit Attachments';
				$bulk_edit = true;
			else : // Else multiple rows are being edited
				$itemID = $_POST['web_apper_item_ids'][0];
				$item = web_apper_get_attachment( $itemID ); // Get the Record were calculating open seats for
				$modal_heading = 'Edit Attachment';
				$bulk_edit = false;
			endif;
			$web_apper_action = 'update_record';
			$submit_label = 'Update';
			$submit_label_loading = 'Updating...';
		else : // Else add a new row
			$web_apper_action = 'add_record';
			$modal_heading = 'Add Attachment';
			$submit_label = 'Save';
			$submit_label_loading = 'Saving...';
		endif;
		// Form Modal header
		$this->config_form( $id ); // Set form settings
		$form = new \PFBC\Form( $id );
		$form->configure( $this->formConfig ); // Configure form settings
		$form->addElement( new \PFBC\Element\Hidden( 'web_apper_form', $id ) );
		$form->addElement( new \PFBC\Element\Hidden( 'action', 'web_apper' . $this->shortcode ) );
		$form->addElement( new \PFBC\Element\Hidden( 'web_apper_nonce', wp_create_nonce( 'AwesomeSauce!87' ) ) );
		$form->addElement( new \PFBC\Element\Hidden( 'web_apper_action', $web_apper_action ) );
		$form->addElement( new \PFBC\Element\Hidden( 'web_apper_include', $_POST['data']['include'] ) );
		if ( isset($itemID) ) :
			$form->addElement( new \PFBC\Element\Hidden( 'web_apper_item_ids', $itemID ) );
		endif;
		$form->addElement(new \PFBC\Element\ModalHeading($modal_heading));
		// Form Modal body
		$this->fields = web_apper_get_fields( $_POST['data']['include'] ); // Get the fields
		foreach ( $this->fields as $field ) :
			if ( $bulk_edit ) :
				if ( $field['field_bulk_edit'] == 1 ):
					$field['field_required'] = 0;
					$this->add_form_field( $field, $form );
				endif;
			else:
				$field['field_value'] = $item->$field['field_id'];
				$this->add_form_field( $field, $form );
			endif;
		endforeach;
		// Form Modal footer
		$form->addElement(new \PFBC\Element\Button($submit_label, 'submit', array(
			'id' => 'submit',
			'data-loading-text' => $submit_label_loading
		)));
		$form->render(); // Output the form
	}

    /**
     * Add Attachment to the database
     *
     * @since 1.0
     */
	public function add_record() {
		// Get the fields
		$this->fields = web_apper_get_fields( $_POST['web_apper_include'] );
		// Get the post data
		$itemData = $this->get_field_data_from_post();
		// Save the Attachment
		$result = web_apper_insert_attachment( $itemData );
		// Send ajax response
		if ( $result ) :
			return json_encode( $this->send_response( 'Attachment saved.', 'Hurray!', 'alert-success' ) );  // Send Response
		else :
			return json_encode( $this->send_response( 'There was a problem saving the Attachment. Please Try again.', 'Oh snap!', 'alert-error', false ) );  // Send Response
		endif;
	}

    /**
     * Update Attachment in the database
     *
     * @since 1.0
     */
	public function update_record() {
		// Get the fields
		$this->fields = web_apper_get_fields( $_POST['web_apper_include'] );
		// Get the post data
		$itemData = $this->get_field_data_from_post();
		// Save Attachments
		$errors = array();
		foreach( explode( ',', $_POST['web_apper_item_ids'] ) as $itemID ) : // Fields ids are a comma-delimited string here since they are coming from a PFBC form 
			$result = web_apper_update_attachment( $itemID, $itemData );
			if ( !$result ) : 
				$errors[] = $itemID;
			endif;
		endforeach;
		// Send ajax response
		if ( !empty($errors) ) :
			return json_encode( $this->send_response( 'The following Attachments were not updated: ' . implode(', ', $errors), 'Oh snap!', 'alert-error', false ) );  // Send Response
		else :
			return json_encode( $this->send_response( 'Update  successful.', 'Hurray!', 'alert-success', true ) );  // Send Response
		endif;
	}

    /**
     * Delete Attachment from the database
     *
     * @since 1.0
     */
	public function delete_record() {
		// Delete Attachments
		foreach( $_POST['web_apper_item_ids'] as $itemID ) :
			$result = web_apper_delete_attachment( $itemID );
			if ( !$result ) : 
				$errors[] = $itemID;
			endif;
		endforeach;
		// Send ajax response
		if ( !empty($errors) ) :
			return json_encode( $this->send_response( 'The following Attachments were not deleted: ' . implode(', ', $errors), 'Oh snap!', 'alert-error', false ) );  // Send Response
		else :
			return json_encode( $this->send_response( 'Delete successful.', 'Hurray!', 'alert-success', true ) );  // Send Response
		endif;
	}

}

$initialize = new AttachmentIndex(); 

?>