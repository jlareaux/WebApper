<?php
namespace WebApper;


abstract class IndexView extends Shortcode {

    /**
     * Define shortcode attribute class properties
     *
     */
	protected $item_id;
	protected $item_label;


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
		$this->fields = web_apper_get_fields( $include );

		// Build the shortcode output hrml string
		?>

		<div class="row">
			<div class="span<?php echo $this->template_content_span(); ?>">
				<?php
				if ( $actions_control ) :
					$adtl_actions = $this->textToArray( $adtl_actions );
					$this->build_dt_actions_control( $id, $atts, $adtl_actions ); // Echos the action buttons HTML for the dataTable
				endif;
				if ( $colvis_control ) :
					$this->build_dt_colvis_control(); // Echos the colVis button HTML for the dataTable
				endif;
				if ( $form_controls ) :
					$this->build_dt_form_controls(); // Echos the form controls HTML for the dataTable
				endif;

				$this->build_dt_modal_wrapper( $id ); // Echos the Modal wrapper HTML
				?>
			</div>
		</div>

		<div class="row">
			<div class="span<?php echo $this->template_content_span(); ?>">
				<?php $this->build_dt( $atts, $colfilter_controls, array( 'include' => $include ) ); // Echos the dataTable HTML ?>
			</div>
		</div>

		<?php
		
		if ( $rightclick_menu ) :
			$this->build_dt_rightclick_menu( $id ); // Echos the browser context menu HTML
		endif;
		
		$this->shortcode_js( $atts );
	}

    /**
     * Handles the shortcode
     *
     * @param array $atts
     */
    public function shortcode_js( $atts ) {
		 ?>
		<script type="text/javascript">

			jQuery(document).ready(function($) {
				<?php 
				if ( $atts['row_selection'] ) :
					$this->build_dt_row_selection( $atts['id'], true ); // Echos dataTable Row selection JS, specify true for multi-select
				endif;
				?>
			});

			// Handle response from ajax post
			function parseResponse<?php echo $atts['id']; ?>(response) {
				var result = jQuery.parseJSON(response);  // Parse response
				if ( result.success ) {  // If ajax returns a successful save
					table.api().ajax.reload(); // Reload the dataTable
				}
				if ( result.action != 'update_record' ) {
					jQuery('#<?php echo $atts['id']; ?>Modal').modal('hide'); // Hide the Modal
				}
				jQuery('.page-content').prepend(result.htmlalert);  // Show and alert
				jQuery('#<?php echo $atts['id']; ?>SelectAll').text('Select All'); // Reset the 'Select All' button
			}

		</script>
		<?php
	}

    /**
     * Get Items from the database
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
					$column['formatter'] = function( $d, $row ){
						if ( $this->isSerialized( $d ) ) :
							$d = unserialize( $d );
						endif;
						return $d;
					};
				endif;
				$columns[] = $column;
			endif;
		endforeach;
		require_once( dirname( dirname( __FILE__ ) ) . '/WebApper/SSP.php' );
		global $wpdb;		
		echo json_encode(
			\SSP::simple( $_POST, $wpdb->prefix . 'web_apper_' . $this->item_id . 's', 'ID', $columns ) // $_GET, $sql_details, $table, $primaryKey, $columns
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
				$modal_heading = 'Edit ' . $this->item_label . 's';
				$bulk_edit = true;
			else : // Else multiple rows are being edited
				$itemID = $_POST['web_apper_item_ids'][0];
				$web_apper_get_item = 'web_apper_get_' . $this->item_id;
				$item = $web_apper_get_item( $itemID );
				$modal_heading = 'Edit ' . $this->item_label;
				$bulk_edit = false;
			endif;
			$web_apper_action = 'update_record';
			$submit_label = 'Update';
			$submit_label_loading = 'Updating...';
		else : // Else add a new row
			$web_apper_action = 'add_record';
			$modal_heading = 'Add ' . $this->item_label;
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
					$field['field_default_value'] = NULL;
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
     * Echos a form for the dataTable
     *
     * @since 1.0
     */
	public function get_form_import() {
		$id = $_POST['id']; // The shortcode ID
		// Form Modal header
		$this->config_form( $id ); // Set form settings
		$form = new \PFBC\Form( $id );
		$form->configure( $this->formConfig ); // Configure form settings
		$form->addElement( new \PFBC\Element\Hidden( 'web_apper_form', $id ) );
		$form->addElement( new \PFBC\Element\Hidden( 'action', 'web_apper' . $this->shortcode ) );
		$form->addElement( new \PFBC\Element\Hidden( 'web_apper_nonce', wp_create_nonce( 'AwesomeSauce!87' ) ) );
		$form->addElement( new \PFBC\Element\Hidden( 'web_apper_action', 'import' ) );
		$form->addElement( new \PFBC\Element\Hidden( 'web_apper_include', $_POST['data']['include'] ) );
		$form->addElement(new \PFBC\Element\ModalHeading('Import'));
		// Form Modal body
		$this->fields = web_apper_get_fields( $_POST['data']['include'] ); // Get the fields
		foreach ( $this->fields as $field ) :
			$this->add_form_field( $field, $form );
		endforeach;
		// Form Modal footer
		$form->addElement(new \PFBC\Element\Button('Import', 'submit', array(
			'id' => 'submit',
			'data-loading-text' => 'Importing...'
		)));
		$form->render(); // Output the form
	}

    /**
     * Converts timestamps to custom formatted string for DataTables.js
     *
	 * @param string $time
     * @since 1.0
     */
	function build_dt( $atts, $col_filters = false, $ajax_data = array(), $tableclasses = 'table-bordered table-striped table-hover table-condensed' ) { 
		?>
		<table id="<?php echo $atts['id']; ?>DataTable" class="table <?php echo str_replace(",", " ", $tableclasses); ?>">
			<thead>
				<tr>
					<?php $this->build_dt_headers(); ?>
			   </tr>
			</thead>
			<tbody></tbody>
		</table>

		<script type="text/javascript">
			jQuery(document).ready(function($) {

				table = $('#<?php echo $atts['id']; ?>DataTable').dataTable( {
					'dom': "t <'#dt_info' i > <'#dt_pagination' p >",  // Set HTML DOM options
					'stateSave': true,
					'serverSide': true,
					'ajax': {
						'url': '<?php echo admin_url('admin-ajax.php'); ?>',
						'type': 'POST',
						'data': {
							'id': '<?php echo $atts['id']; ?>',
							'action': 'web_apper<?php echo $this->shortcode; ?>',
							'web_apper_action': 'get_records',
							'web_apper_nonce': '<?php echo wp_create_nonce('AwesomeSauce!87'); ?>',
							'web_apper_include': '<?php echo $atts['include']; ?>',
							'web_apper_posttype': '<?php echo $atts['posttype']; ?>',
							'columns': [
								<?php if ( $col_filters ) $this->build_dt_col_filters(); ?>
								{
									'data': '<?php echo $field['field_id']; ?>',
									'searchable': true,
									'search': {
										'value': function () {
											var undefinedVal;
											if ( undefinedVal != $('[name="<?php echo $field['field_id']; ?>_search"]').val() )
												return $('[name="<?php echo $field['field_id']; ?>_search"]').val();
											else
												return "";
										},
									},
								},
							],
						}
					},
					'columns': [<?php
						foreach ( $this->fields as $field ) :
							if ( $field['field_form_only'] != true ) :
								echo "{ 'data': '" . $field['field_id'] . "', 'name': '" . $field['field_id'] . "' },";
							endif;
						endforeach;
					?>],
					'columnDefs': [
						{ 'defaultContent': '&nbsp;', 'targets': '_all' },
					],
				} );
				
				<?php do_action( $this->shortcode . '_dt_javascript', $atts['id'], $ajax_data ); // Allow for insertion of js ?>

			});
		</script>
		<?php 
	}

    /**
     * Builds a table header for dataTable
     *
     * @since 1.0
     */
	function build_dt_headers() {
		foreach ( $this->fields as $field ) :
			if ( $field['field_form_only'] != true ) :
				?>
				<th id="<?php echo $field['field_id']; ?>" class="dropdown">
					<?php if ( !empty($field['field_dt_filter_type']) ) : ?>
						<a href="#" class="dropdown-toggle" data-toggle="dropdown"><?php echo $field['field_name']; ?></a>
						<a href="#" class="pull-right"><i class=""></i></a>
						<ul class="dropdown-menu">
							<form class="form">
								<?php if ( $field['field_dt_filter_type'] == 'search' ) : ?>
									<input type="text" name="<?php echo $field['field_id']; ?>_search" placeholder="search" value="<?php echo $field['field_dt_filter_value']; ?>" />					
								<?php elseif ( $field['field_dt_filter_type'] == 'range' ) : ?>
									<input type="number" name="<?php echo $field['field_id']; ?>_min" placeholder="min" value="<?php echo $field['field_dt_filter_value']; ?>" />
									<input type="number" name="<?php echo $field['field_id']; ?>_max" placeholder="max" value="<?php echo $field['field_dt_filter_value']; ?>" />
								<?php elseif ( $field['field_dt_filter_type'] == 'date' ) : ?>
									<input type="date" name="<?php echo $field['field_id']; ?>_min" placeholder="min" value="<?php echo $field['field_dt_filter_value']; ?>" />
									<input type="date" name="<?php echo $field['field_id']; ?>_max" placeholder="max" value="<?php echo $field['field_dt_filter_value']; ?>" />
								<?php elseif ( $field['field_dt_filter_type'] == 'equals' ) : ?>
									<?php
										if ( !isset( $field['field_dt_filter_options'] ) ) :
											$field['field_dt_filter_options'] = $field['field_options'];
										endif;
									?>
									<?php foreach ( explode('|', $field['field_dt_filter_options']) as $option_pair ) : $optionPair = explode(',', $option_pair); $checked = ''; ?>
										<?php if ( isset($field['field_dt_filter_value']) && in_array($field['field_dt_filter_value'], $optionPair) ) $checked = ' checked="checked"'; ?>
										<label><input type="checkbox" name="<?php echo $field['field_id']; ?>_equals[]" value="<?php echo $optionPair[0]; ?>"<?php echo $checked; ?> /><?php echo $optionPair[1]; ?></label>
									<?php endforeach; ?>
								<?php endif; ?>
							</form>
						</ul>
					<?php else : ?>
						<?php echo $field['field_name']; ?>
					<?php endif; ?>
				</th>
				<?php
			endif;
		endforeach;
	}

    /**
     * Builds a table header for dataTable
     *
     * @since 1.0
     */
	function build_dt_col_filters() {
		foreach ( $this->fields as $field ) :
			if ( isset($field['field_dt_filter_type']) && !empty($field['field_dt_filter_type']) && $field['field_form_only'] != true ) :
				$include_filter_js = true;
				?>
				{
					'data': '<?php echo $field['field_id']; ?>',
					'searchable': true,
					'search': {
					<?php if ( $field['field_dt_filter_type'] == 'search' ) : ?>
						'value': function () {
							var undefinedVal;
							if ( undefinedVal != $('[name="<?php echo $field['field_id']; ?>_search"]').val() )
								return $('[name="<?php echo $field['field_id']; ?>_search"]').val();
							else
								return "";
						},
					<?php elseif ( $field['field_dt_filter_type'] == 'range' ||  $field['field_dt_filter_type'] == 'date' ) : ?>
						'min': function () {
							var undefinedVal;
							if ( undefinedVal != $('[name="<?php echo $field['field_id']; ?>_min"]').val() )
								return $('[name="<?php echo $field['field_id']; ?>_min"]').val().replace('/', '-');
							else
								return "";
						},
						'max': function () {
							var undefinedVal;
							if ( undefinedVal != $('[name="<?php echo $field['field_id']; ?>_max"]').val() )
								return $('[name="<?php echo $field['field_id']; ?>_max"]').val().replace('/', '-');
							else
								return "";
						},
					<?php elseif ( $field['field_dt_filter_type'] == 'equals' ) : ?>
						'equals': function () {
							var values = [];
							$('[name="<?php echo $field['field_id']; ?>_equals[]"]:checked').each( function() {
								values.push( $(this).val() );
							});
							return values;
						},
					<?php endif; ?>
					},
				},
				<?php
			endif;
		endforeach;
		if ( $include_filter_js ) :
			add_action( $this->shortcode . '_dt_javascript', function() { ?> 
				// Column Filter Controls
					// Get the DataTable thead node
					var tableHead = $('thead', table);
					// Add click handler to open TH dropdowns
					$('a.dropdown-toggle', tableHead).dropdown();
					$('a.dropdown-toggle', tableHead).live( 'click', function(e) {
						e.stopPropagation();
						$(this).dropdown('toggle');
					});
					// Fix TH dropdown input focus problem
					$('.dropdown-menu', tableHead).on( 'click', function(e) {
						e.stopPropagation();
					});
					// Column filter handlers
					$('input[type="checkbox"], input[type="date"]', tableHead).on('change', function(e) {
						table.api().ajax.reload();
					});
					var typingTimeoutCS = null;
					$('input:not(:checkbox)', tableHead).on( 'keyup', function(e) {
						if (typingTimeoutCS != null) {
							clearTimeout(typingTimeoutCS);
						}
						typingTimeoutCS = setTimeout(function() {
							typingTimeoutCS = null;  
							table.api().ajax.reload();
						}, 500);  
					});
			<?php } );
		endif;
	}

    /**
     * Builds action menu for a dataTable
     *
     * @since 1.0
     */
	function build_dt_actions_control( $id, $atts, $adtl_actions = array() ) {
		$actions = array();
		if ( $this->current_user_has_cap( $atts['addcap'] ) ) :
			$actions['Add'] = 'Add';
			add_action( $this->shortcode . '_dt_javascript', function( $id, $ajax_data ) { ?> 
				// 'Add' button click handler
				$('.<?php echo $id; ?>Add').live("click", function() {
					$('.alert').remove(); // Close any alerts that may be open
					<?php $this->build_dt_action_ajax_data( $id, 'get_form', false, $ajax_data ); ?>
					$.post("<?php echo admin_url('admin-ajax.php'); ?>", data, function(form) {
						$('#<?php echo $id; ?>Modal').html(form);  // Insert form into modal
						$('#<?php echo $id; ?>Modal').removeClass('minimize').addClass('dock').modal({ backdrop: false });  // Show the modal
					});
				});				
			<?php }, 1, 2 );
		endif;
		if ( $this->current_user_has_cap( $atts['editcap'] ) ) :
			$actions['Edit'] = 'Edit';
			add_action( $this->shortcode . '_dt_javascript', function( $id, $ajax_data ) { ?> 
				// 'Edit' button click handler
				$('.<?php echo $id; ?>Edit').live("click", function() {
					$('.alert').remove(); // Close any alerts that may be open
					<?php $this->build_dt_action_ajax_data( $id, 'get_form', true, $ajax_data ); ?>
					$.post("<?php echo admin_url('admin-ajax.php'); ?>", data, function(form) {
						$('#<?php echo $id; ?>Modal').html(form); // Insert form into modal
						$('#<?php echo $id; ?>Modal').removeClass('minimize').addClass('dock').modal({ backdrop: false }); // Show the modal
					});
				});
			<?php }, 1, 2 );
		endif;
		if ( $this->current_user_has_cap( $atts['deletecap'] ) ) :
			$actions['Delete'] = 'Delete';
			add_action( $this->shortcode . '_dt_javascript', function( $id, $ajax_data ) { ?> 
				// 'Delete' button click handler
				$('.<?php echo $id; ?>Delete').live("click", function() {
					$('.alert').remove(); // Close any alerts that may be open
					<?php $this->build_dt_action_ajax_data( $id, 'delete_record', true, $ajax_data ); ?>
					$.post("<?php echo admin_url('admin-ajax.php'); ?>", data, function(response) {
						parseResponse<?php echo $id; ?>(response)
					});
				});
			<?php }, 1, 2 );
		endif;
		if ( $this->current_user_has_cap( $atts['importcap'] ) ) :
			$actions['div'] = 'div';
			$actions['Import'] = 'Import';
			add_action( $this->shortcode . '_dt_javascript', function( $id, $ajax_data ) { ?> 
				// 'Import' button click handler
				$('.<?php echo $id; ?>Import').live("click", function() {
					$('.alert').remove(); // Close any alerts that may be open
					<?php $this->build_dt_action_ajax_data( $id, 'get_form_import', false, $ajax_data = array( 'include' => 'datatable_import' ) ); ?>
					$.post("<?php echo admin_url('admin-ajax.php'); ?>", data, function(form) {
						$('#<?php echo $id; ?>Modal').html(form); // Insert form into modal
						$('#<?php echo $id; ?>Modal').removeClass('minimize').addClass('dock').modal({ backdrop: false }); // Show the modal
					});
				});
			<?php }, 1, 2 );
		endif;
		if ( array_key_exists( 'SelectAll', $adtl_actions ) ) :
			add_action( $this->shortcode . '_dt_javascript', function( $id, $ajax_data ) { ?> 
				// 'Select All' button click handler
				$('.<?php echo $id; ?>SelectAll').live("click", function() {
					$('tr', '#<?php echo $id; ?>DataTable tbody').each(function() {
						$(this).addClass('row_selected');
					});
				});
			<?php }, 1, 2 );
		endif;
		if ( array_key_exists( 'ResetFilters', $adtl_actions ) ) :
			add_action( $this->shortcode . '_dt_javascript', function( $id, $ajax_data ) { ?> 
				// 'Reset Filters' button click handler
				$('.<?php echo $id; ?>ResetFilters').live("click", function() {
					$('form', '#<?php echo $id; ?>DataTable thead').each(function() {
						$(this).resetForm(); // Clear form values
					});
					$('#dt_search').val('');
					table.api().search( '', 0, 1 );
					table.api().ajax.reload();
				});
			<?php }, 1, 2 );
		endif;
		$actions = array_merge( $actions, $adtl_actions );
		if ( !empty( $actions ) ) :
			?>
			<div id="dt_actions" class="btn-group">
				<button class="btn btn-primary dropdown-toggle" data-toggle="dropdown">Actions <span class="caret"></span></button>
				<ul id="<?php echo $id; ?>_actions" class="dropdown-menu">
					<?php
					foreach ( $actions as $actionID => $actionLabel ) :
						if ( $actionLabel == 'div' ) :
							echo '<li class="divider"></li>';
						else :
							echo '<li><a class="' . $id . $actionID . '" href="#">' . $actionLabel . '</a></li>';
						endif;
					endforeach;
					?>
				</ul>
			</div>
			<?php
		endif;
	}

    /**
     * Builds colvis for a dataTable
     *
     * @since 1.0
     */
	function build_dt_colvis_control( $reset = true ) {
		?>
		<div id="dt_colvis" class="btn-group">
			<button class="btn dropdown-toggle" data-toggle="dropdown">Columns <span class="caret"></span></button>
			<ul class="dropdown-menu">
				<?php
				$counter = 0;
				foreach ( $this->fields as $field ) :
					if ( $field['field_form_only'] != true ) :
						echo '<li><label><input type="checkbox" class="toggle-vis" value="' . $counter . '" checked="checked" />' . $field['field_name'] . '</label></li>';
						$counter++;
					endif;
				endforeach;
				?>
				<?php if ( $reset == true ) : ?>
				<li class="divider"></li>
				<li><label id="reset_colunms">Reset columns</label></li>
				<?php endif; ?>
			</ul>
		</div>
		<?php add_action( $this->shortcode . '_dt_javascript', function() { ?> 
			// Column Visability Control
				// Stop dropdown from fading when clicked
				$('#dt_colvis .dropdown-menu').on( 'click', function(e) {
					e.stopPropagation();
				});
				// Set initial state of checkboxes
				$('#dt_colvis .dropdown-menu input').each( function( index ) {
					if ( table.api().column( index ).visible() === false ) {
						$(this).prop('checked', false);
					}

				});
				// Column checkboxes
				$('.toggle-vis').on( 'change', function (e) {
					var column = table.api().column( $(this).val() ); // Get the column API object
					column.visible( ! column.visible() ); // Toggle the visibility
				} );
				// Reset Columns Button
				$('#reset_colunms').on( 'click', function(e) {
					$('#dt_colvis .dropdown-menu input').each( function( index ) {
						if ( $(this)[0].defaultChecked == true ) {
							$(this).prop('checked', true);
							table.api().column( index ).visible( true )
						} else {
							$(this).prop('checked', false);
							table.api().column( index ).visible( false )
						}
					});
				});
		<?php } );
	}

    /**
     * Build paging / global search for a dataTable
     *
     * @since 1.0
     */
	function build_dt_form_controls( $paging = true, $search = true, $search_placeholder = 'search the table' ) {
		?>
		<form class="form-inline pull-right">
			<?php if ( $paging == true ) : ?>
			<label for="dt_paging">show</label>
			<select id="dt_paging" name="dt_paging" class="input-mini">
				<option value="10">10</option>
				<option value="25">25</option>
				<option value="50">50</option>
				<option value="-1">All</option>
			</select>	
			<?php endif; ?>
			<?php if ( $search == true ) : ?>
			<input id="dt_search" type="search" class="input-medium" placeholder="<?php echo $search_placeholder; ?>">
			<?php endif; ?>
		</form>
		<?php add_action( $this->shortcode . '_dt_javascript', function() { ?> 
			// Paging Control
				$('#dt_paging').val( table.api().page.len() );
				$('#dt_paging').on( 'change', function(e) {
					table.api().page.len( $(':selected', this).val() ).draw();
				});
			// Search Control
				$('#dt_search').val( table.api().search() );
				var typingTimeoutGS = null;
				$('#dt_search').on( 'keyup', function(e) {
					if (typingTimeoutGS != null) {
						clearTimeout(typingTimeoutGS);
					}
					typingTimeoutGS = setTimeout(function() {
						typingTimeoutGS = null;  
						table.api().search( $('#dt_search').val(), 0, 1 ).draw();
					}, 500);  
				});
		<?php } );
	}

    /**
     * Build row selection script for a dataTable
     *
     * @since 1.0
     */
	function build_dt_row_selection( $id, $multi = false, $dbl_click_edit = true ) {
		?> 
		// Add a click/dblclick handler to the table rows
		function trSingleClick(that, e) {
			if ( jQuery('td', this).hasClass('dataTables_empty') ) { // If the dataTable is empty
				return; // Do nothing
			} else { // Else the dataTable has rows
				<?php if ( $multi == true ) : ?>
					if ( $('#<?php echo $id; ?>DataTable').data('shifted') == true ) {
						if ( jQuery(this).hasClass('row_selected') ) { // If the row is already selected
							jQuery(this).removeClass('row_selected'); // Unselect the row
						} else { // Else the row is not selected // Else
							jQuery(this).addClass('row_selected'); // Select the row
						}
					} else {
						jQuery('tr.row_selected', '#<?php echo $id; ?>DataTable tbody').removeClass('row_selected'); // Unselect any other rows
						jQuery(this).addClass('row_selected'); // Select the row
					}
				<?php else : ?>
					jQuery('tr.row_selected', '#<?php echo $id; ?>DataTable tbody').removeClass('row_selected'); // Unselect any other rows
					jQuery(this).addClass('row_selected'); // Select the row
				<?php endif; ?>
			}
		}
		<?php if ( $dbl_click_edit ) : ?>
			function trDoubleClick(e) {
				if ( jQuery('td', this).hasClass('dataTables_empty') ) { // If the dataTable is empty
					return; // Do nothing
				}
				jQuery('tr.row_selected', table).removeClass('row_selected'); // Unselect any other rows
				jQuery(this).addClass('row_selected'); // Select the row
				jQuery('.<?php echo $id; ?>Edit').click(); // Click the Edit button
			}
			jQuery('tr', '#<?php echo $id; ?>DataTable tbody').live('click', function(e) {
				var that = this;
				setTimeout(function() {
					var dblclick = jQuery(that).data('double');
					if (dblclick > 0) {
						jQuery(that).data('double', dblclick-1);
					} else {
						<?php if ( $multi == true ) : ?>
						$('#<?php echo $id; ?>DataTable').data('shifted', e.ctrlKey );
						<?php endif; ?>
						trSingleClick.call(that, e);
					}
				}, 100);
			}).live('dblclick', function(e) {
				jQuery(this).data('double', 2);
				trDoubleClick.call(this, e);
			});
		<?php else : ?>
			jQuery('tr', '#<?php echo $id; ?>DataTable tbody').live('click', function(e) {
				var that = this;
				trSingleClick.call(that, e);
			});
		<?php endif;
	}

    /**
     * Build a custom RightClick menu for a dataTable
     *
     * @since 1.0
     */
	function build_dt_rightclick_menu( $id, $menu_items = array() ) {
		?>
		<ul id="<?php echo $id; ?>ContextMenu" class="dropdown-menu context-menu hide fade" role="menu">
			<?php //echo apply_filters( $this->shortcode . '_right_click_menu', $menu_items, $id ); // Create hook to add HTML & JS ?>
		</ul>
		<script type="text/javascript">
			jQuery(document).ready(function($) {
				// Context Menu
				$('#<?php echo $id; ?>ContextMenu').html( $('#<?php echo $id; ?>_actions.dropdown-menu').html() )
				$('tbody tr', table).live('mouseover', function() {
					this.oncontextmenu = function(e) {
						$('.clicked').removeClass('clicked');
						$(this).addClass('clicked');
						if ( $('tr.row_selected').length > 1 ) {
							$('#<?php echo $id; ?>ContextMenu #editSelected').parent().show();
						} else {
							$('#<?php echo $id; ?>ContextMenu #editSelected').parent().hide();
						}
						//$('#<?php echo $id; ?>ContextMenu #view').attr('href', $('tr.clicked', table).data('singleview') );
						$('#<?php echo $id; ?>ContextMenu').css('top', e.pageY ).css('left', e.pageX ).addClass('in');
						e.preventDefault();
					}
				});
				$('#site-main').on("click", function() {
					$('#<?php echo $id; ?>ContextMenu').removeClass('in');
				});
				$('tbody tr', table).mouseover(function(){
					$('#site-main').each(function() {
						this.oncontextmenu = function(e) {
							// Do nothing...
						}
					});
				});
				$('tbody tr', table).mouseout(function(){
					$('#site-main').each(function() {
						this.oncontextmenu = function(e) {
							$('#<?php echo $id; ?>ContextMenu').removeClass('in');
						}
					});
				});
				$('#<?php echo $id; ?>ContextMenu').mouseover(function(){
					$('#site-main').on("click", function() {
						// Do nothing...
					});
				});
				$('#<?php echo $id; ?>ContextMenu').mouseout(function(){
					$('#site-main').on("click", function() {
						$('#<?php echo $id; ?>ContextMenu').removeClass('in');
					});
				});
			});
		</script>
		<?php
	}

    /**
     * Build data for an ajax request
     *
     * @since 1.0
     */
	function build_dt_action_ajax_data( $id, $action, $include_item_ids = true, $ajax_data = array() ) {
		if ( $include_item_ids ) : ?>
			if ( $('tr.row_selected').length > 0 ) {
				item_ids = []; 
				$('tr.row_selected').each(function() {
					item_ids.push( $(this).data('item-id') );
				});
			} else {
				alert("Please select a row first!");
				return false;
			}
		<?php endif; ?>
		var data = {
			id: '<?php echo $id; ?>',
			action: 'web_apper<?php echo $this->shortcode; ?>',
			web_apper_action: '<?php echo $action; ?>',
			web_apper_nonce: '<?php echo wp_create_nonce("AwesomeSauce!87"); ?>',
			<?php
			if ( $include_item_ids ) :
				echo 'web_apper_item_ids: item_ids,';
			endif;
			if ( !empty($ajax_data) ) :
				echo 'data: {';
				foreach ( $ajax_data as $key => $val ) :
					echo $key . ": '" . $val . "',";
				endforeach;
				echo '},';
			endif;
			?>
		};
		<?php
	}

    /**
     * Format a dataTable Column
     *
     * @since 1.0
     */
	function dt_format_value( $type ) {
		if ( $type == 'bool_to_text' ) :
			return function( $d, $row ){
				return $this->boolToText( $d );
			};
		elseif ( $type == 'date' ) :
			return function( $d, $row ){
				if ( empty($d) || $d == '0000-00-00 00:00:00' ) :
					return ' ';
				else :
					$datetime_format = get_option( 'date_format' ) . ' ' . get_option( 'time_format' );
					return date( $datetime_format, strtotime($d) );
				endif;
			};
		elseif ( $type == 'user_id' ) :
			return function( $d, $row ){
				$user = get_user_by( 'id', $d );
				return $user->display_name;
			};
		elseif ( $type == 'post_id' ) :
			return function( $d, $row ){
				$user_name = get_post_meta( $d, 'first-name', true ) . ' '  . get_post_meta( $d, 'last-name', true );
				return $user_name;
			};
		endif;
		return $d;
	}

    /**
     * Build modal wrapper for a dataTable
     *
     * @since 1.0
     */
	function build_dt_modal_wrapper( $id ) {
		?><div id="<?php echo $id; ?>Modal" class="modal hide fade" tabindex="-1" role="dialog" aria-labelledby="<?php echo $id; ?>ModalLabel" aria-hidden="true"></div><?php
	}

    /**
     * Add Item to the database
     *
     * @since 1.0
     */
	public function add_record() {
		// Get the fields
		$this->fields = web_apper_get_fields( $_POST['web_apper_include'] );
		// Get the post data
		$itemData = $this->get_field_data_from_post();
		// Save the Item
		$web_apper_insert_item = 'web_apper_insert_' . $this->item_id;
		$result = $web_apper_insert_item( $itemData );
		// Send ajax response
		if ( $result ) :
			return json_encode( $this->send_response( $this->item_label . ' saved.', 'Hurray!', 'alert-success' ) );  // Send Response
		else :
			return json_encode( $this->send_response( 'There was a problem saving the ' . $this->item_label . '. Please Try again.', 'Oh snap!', 'alert-error', false ) );  // Send Response
		endif;
	}

    /**
     * Update Item in the database
     *
     * @since 1.0
     */
	public function update_record() {
		$itemIDs = explode( ',', $_POST['web_apper_item_ids'] );
		// Get the fields
		$this->fields = web_apper_get_fields( $_POST['web_apper_include'] );
		// Get the post data
		$itemData = $this->get_field_data_from_post();
		$bulk_edit = count( $itemIDs ) == 1 ? false : true ;
		if ( $bulk_edit ) :
			foreach ( $itemData as $id => $data ) :
				if ( empty($data) ) :
					unset( $itemData[$id] );
				endif;
			endforeach;
		endif;
		// Save Items
		$errors = array();
		foreach( $itemIDs as $itemID ) : // Fields ids are a comma-delimited string here since they are coming from a PFBC form 
			$web_apper_update_item = 'web_apper_update_' . $this->item_id;
			$result = $web_apper_update_item( $itemID, $itemData );
			if ( !$result ) :
				$errors[] = $itemID;
			endif;
		endforeach;
		// Send ajax response
		if ( !empty($errors) ) :
			return json_encode( $this->send_response( 'The following ' . $this->item_label . 's were not updated: ' . implode(', ', $errors), 'Oh snap!', 'alert-error', false ) );  // Send Response
		else :
			return json_encode( $this->send_response( 'Update  successful.', 'Hurray!', 'alert-success', true ) );  // Send Response
		endif;
	}

    /**
     * Delete Item from the database
     *
     * @since 1.0
     */
	public function delete_record() {
		// Delete Items
		foreach( $_POST['web_apper_item_ids'] as $itemID ) :
			$web_apper_delete_item = 'web_apper_delete_' . $this->item_id;
			$result = $web_apper_delete_item( $itemID );
			if ( !$result ) : 
				$errors[] = $itemID;
			endif;
		endforeach;
		// Send ajax response
		if ( !empty($errors) ) :
			return json_encode( $this->send_response( 'The following ' . $this->item_label . 's were not deleted: ' . implode(', ', $errors), 'Oh snap!', 'alert-error', false ) );  // Send Response
		else :
			return json_encode( $this->send_response( 'Delete successful.', 'Hurray!', 'alert-success', true ) );  // Send Response
		endif;
	}

    /**
     * Import CSV to the database
     *
     * @since 1.0
     */
	public function import() {
		// Import Files
		foreach( explode(',', $_POST['web_apper_attachment_ids']) as $attachmentID ) :
			$attachment = web_apper_get_attachment( $attachmentID );
			$file_path = dirname( dirname( dirname( __FILE__ ) ) ) . '/uploads/WebApper/plupload' . '/' . $attachment->attachment_file_name;

			$rows = array_map( 'str_getcsv', file($file_path) );
			$cols = $rows[0];
			unset( $rows[0] );
			
			foreach ( $rows as $row ) :
				$i = 0;
				foreach ( $row as $value ) :
					$item[$cols[$i]] = $value;
					$i++;
				endforeach;
				$items[] = $item;
			endforeach;

			foreach ( $items as $itemData ) :
				// Save the Item
				$web_apper_insert_item = 'web_apper_insert_' . $this->item_id;
				$result = $web_apper_insert_item( $itemData );
				if ( $result === false ) : 
					$errors = true;
				endif;
			endforeach;			
		endforeach;
		// Send ajax response
		if ( $errors ) :
			return json_encode( $this->send_response( 'There were some errors importing', 'Oh snap!', 'alert-error', false ) );  // Send Response
		else :
			return json_encode( $this->send_response( 'Import successful.', 'Hurray!', 'alert-success', true ) );  // Send Response
		endif;
	}

}

?>