<?php
namespace WebApper;

// Start PHP user session - for PFBC
	session_start();


abstract class Shortcode extends Base {
	
    /**
     * Define shortcode attribute class properties
     *
     */
	protected $shortcode;  // The name of the shortcode
	protected $defaults = array();  // The default shortcode attributes
	protected $ajax_nopriv = false;  // If set to true, will enable ajax when user is not logged in
	protected $fields = array();  // Array of fields the shortcode is working with
	protected $formConfig = array();  // The PFBC form config if shortcode uses it


    /**
     * Initialize the Shortcode Class
     *
     */
    public function __construct() {
		add_shortcode( $this->shortcode, array($this, 'shortcode') );
		add_action( 'wp_ajax_web_apper' . $this->shortcode, array($this, 'route_ajax_request') );
		if ( $this->ajax_nopriv == true )
			add_action( 'wp_ajax_nopriv_web_apper' . $this->shortcode, array($this, 'route_ajax_request') );
	}

    /**
     * Handles the add post shortcode
     *
     * @param $atts
     */
    abstract protected function shortcode( $atts );

    /**
     * Checks if a shortcode has required attributes
     *
	 * @param string $usercap
     * @since 1.0
     */
	protected function has_req_attrs( $atts ) {
		// Check for required shortcode attributes
		if ( $atts['id'] == null )
			return 'You must give the shortcode a unique ID, i.e. [shortcode id=\'code-1\']';
		return true;
	}

    /**
     * Configure the form obj settings
     *
     * @param array $atts
     * @since 1.0
     */
	public function config_form( $id ) {
		$this->formConfig['prevent'] = array('bootstrap', 'jQuery', 'focus');  // Prevents scripts from loading twice
		$this->formConfig['view'] = new \PFBC\View\Modal;
		$this->formConfig['errorView'] = new \PFBC\ErrorView\Standard;
		$this->formConfig['action'] = admin_url('admin-ajax.php');
		$this->formConfig['ajax'] = 1;
		$this->formConfig['ajaxCallback'] = 'parseResponse' . $id;
	}
	
	/**
	 *  Adds a field to an instance of PFBC
	 *
	 * @param array $field
	 * @param object $form
	 * @since 1.0
	 */
	protected function add_form_field( $field, $form ) {
			// Add Field flow javascript
			if ( $field['field_index_only'] ) :
				return;
			endif;
			$additionalParams = array();
			// Set Field name & id
			$name = $field['field_id'];
			$additionalParams['id'] = $name;
			// Set Field value
			if ( isset($field['field_value']) ) :
				$additionalParams['value'] = $field['field_value'];
			elseif ( isset($field['field_default_value']) ) :
				$additionalParams['value'] = stripslashes($field['field_default_value']);
			endif;
			if ( $this->isSerialized( $additionalParams['value'] ) ) :
				$additionalParams['value'] = unserialize( $additionalParams['value'] );
			endif;
			// Set Field label
			$label = $field['field_name'];
			// Set Field options
			if ( !empty($field['field_options']) ) :
				$optionPairs = explode( '|', $field['field_options'] );
				if ( !empty($optionPairs) ) :
					$options = array();
					foreach ( $optionPairs as $optionPair ) :
						$optionPair = str_replace("'", "", str_replace('"', "", stripslashes( $optionPair )));
						$optionVals = explode( ',', $optionPair );
						$options[trim( $optionVals[0] )] = trim( $optionVals[1] );
					endforeach;
				endif;
			endif;
			// Set Field additional parameters
			$additionalParams['placeholder'] = $field['field_placeholder'];
			$additionalParams['shortDesc'] = $field['field_short_desc'];
			$additionalParams['longDesc'] = $field['field_long_desc'];
			if ( $field['field_required'] == 1 ) :
				$additionalParams['required'] = $field['field_required'];
				$additionalParams['data-required'] = $field['field_required'];
			endif;
			if ( $field['field_validation'] == 'RegExp' ) :
				$additionalParams['validation'] = new \PFBC\Validation\RegExp( $field['field_regex'], $field['field_error_message'] );
			elseif ( $field['field_validation'] == 'AlphaNumeric' ) :
				$additionalParams['validation'] = new \PFBC\Validation\AlphaNumeric;
			endif;
			if ( !empty($field['field_attributes']) ) :
				$attributePairs = explode( '|', $field['field_attributes'] );
				if ( !empty($attributePairs) ) :
					$attributes = array();
					foreach ( $attributePairs as $attributePair ) :
						$attributeVals = explode( ',', $attributePair );
						$attributes[trim(str_replace("'", "", str_replace('"', "", stripslashes($attributeVals[0]))))] = trim(str_replace("'", "", str_replace('"', "", stripslashes($attributeVals[1]))));
					endforeach;
					$additionalParams = array_merge( $additionalParams, $attributes );
				endif;
			endif;
			// Check for a Fieldset
			if ( isset($field['field_fieldset']) ) :
				if ( isset($field['field_primaryset']) != true ) :
					$additionalParams['data-fieldset'] = $field['field_fieldset'];
				endif;
			endif;
			// Add Field flow javascript
			if ( !empty($field['field_form_flow_id']) ) :
				web_apper_build_flow_js( $field );
			endif;
			// Add Field object to the form
			switch ( $field['field_type'] ) :
				case 'Hidden':
					$form->addElement( new \PFBC\Element\Hidden($name, $additionalParams['value']) ); break; // Hidden
				case 'HTML':
					$form->addElement( new \PFBC\Element\HTML($field['field_default_value']) ); break; // HTML
				case 'Legend':
					$form->addElement( new \PFBC\Element\Legend($additionalParams['value']) ); break; // Legend
				case 'Textbox':
					$form->addElement( new \PFBC\Element\Textbox($label, $name, $additionalParams) ); break; // Textbox
				case 'Textarea':
					$form->addElement( new \PFBC\Element\Textarea($label, $name, $additionalParams) ); break; // Textarea
				case 'Password':
					$form->addElement( new \PFBC\Element\Password($label, $name, $additionalParams) ); break; // Password
				case 'Phone':
					$form->addElement( new \PFBC\Element\Phone($label, $name, $additionalParams) ); break; // Phone
				case 'Search':
					$form->addElement( new \PFBC\Element\Search($label, $name, $additionalParams) ); break; // Search
				case 'File':
					$form->addElement( new \PFBC\Element\File($label, $name, $additionalParams) ); break; // File
				case 'Select':
					$form->addElement( new \PFBC\Element\Select($label, $name, $options, $additionalParams) ); break; // Select
				case 'Radio':
					$form->addElement( new \PFBC\Element\Radio($label, $name, $options, $additionalParams) ); break; // Radio
				case 'Checkbox':
					$form->addElement( new \PFBC\Element\Checkbox($label, $name, $options, $additionalParams) ); break; // Checkbox
				case 'State':
					$form->addElement( new \PFBC\Element\State($label, $name, $additionalParams) ); break; // State
				case 'Country':
					$form->addElement( new \PFBC\Element\Country($label, $name, $additionalParams) ); break; // Country
				case 'YesNo':
					$form->addElement( new \PFBC\Element\YesNo($label, $name, $additionalParams) ); break; // YesNo
				case 'Sort':
					$form->addElement( new \PFBC\Element\Sort($label, $name, $options, $additionalParams) ); break; // Sort
				case 'Checksort':
					$form->addElement( new \PFBC\Element\Checksort($label, $name, $options, $additionalParams) ); break; // Checksort
				case 'Range':
					$form->addElement( new \PFBC\Element\Range($label, $name, $options, $additionalParams) ); break; // Range
				case 'Url':
					$form->addElement( new \PFBC\Element\Url($label, $name, $additionalParams) ); break; // Url
				case 'Email':
					$form->addElement( new \PFBC\Element\Email($label, $name, $additionalParams) ); break; // Email
				case 'Date':
					$form->addElement( new \PFBC\Element\Date($label, $name, $additionalParams) ); break; // Date
				case 'DateTime':
					$form->addElement( new \PFBC\Element\DateTime($label, $name, $additionalParams) ); break; // DateTime
				case 'DateTimeLocal':
					$form->addElement( new \PFBC\Element\DateTimeLocal($label, $name, $additionalParams) ); break; // DateTimeLocal
				case 'Month':
					$form->addElement( new \PFBC\Element\Month($label, $name, $additionalParams) ); break; // Month
				case 'Week':
					$form->addElement( new \PFBC\Element\Week($label, $name, $additionalParams) ); break; // Week
				case 'Time':
					$form->addElement( new \PFBC\Element\Time($label, $name, $additionalParams) ); break; // Time
				case 'Number':
					$form->addElement( new \PFBC\Element\Number($label, $name, $additionalParams) ); break; // Number
				case 'Color':
					$form->addElement( new \PFBC\Element\Color($label, $name, $additionalParams) ); break; // Color
				case 'jQueryUIDate':
					$form->addElement( new \PFBC\Element\jQueryUIDate($label, $name, $additionalParams) ); break; // jQueryUIDate
				case 'TinyMCE':
					$form->addElement( new \PFBC\Element\TinyMCE($label, $name, $additionalParams) ); break; // TinyMCE
				case 'CKEditor':
					$form->addElement( new \PFBC\Element\CKEditor($label, $name, $additionalParams) ); break; // CKEditor
				case 'Button':
					$form->addElement( new \PFBC\Element\Button($label, $name, $additionalParams) ); break; // Button
				case 'Captcha':
					$form->addElement( new \PFBC\Element\Captcha($label) ); break; // Captcha
				case 'UploadFile':
					$form->addElement( new \PFBC\Element\UploadFile($label, $name, $additionalParams) ); break; // Captcha
				case 'UploadImage':
					$form->addElement( new \PFBC\Element\UploadImage($label, $name, $additionalParams) ); break; // Captcha
				case 'UploadCSV':
					$form->addElement( new \PFBC\Element\UploadCSV($label, $name, $additionalParams) ); break; // Captcha
				case 'CollaspeHead':
					$form->addElement( new \PFBC\Element\CollaspeHead($label, $name) ); break; // Captcha
				case 'CollaspeFoot':
					$form->addElement( new \PFBC\Element\CollaspeFoot($label, $name) ); break; // Captcha
				case 'Fieldtype':
					$form->addElement( new \PFBC\Element\Fieldtype($label, $name, $additionalParams) ); break; // Captcha
				case 'Field':
					$form->addElement( new \PFBC\Element\Field($label, $name, $additionalParams) ); break; // Captcha
				case 'Fieldset':
					$form->addElement( new \PFBC\Element\Fieldset($label, $name, $additionalParams) ); break; // Captcha
				case 'Action':
					$form->addElement( new \PFBC\Element\Action($label, $name, $additionalParams) ); break; // Captcha
				case 'Equation':
					$form->addElement( new \PFBC\Element\Equation($label, $name, $additionalParams) ); break; // Captcha
				case 'Condition':
					$form->addElement( new \PFBC\Element\Condition($label, $name, $additionalParams) ); break; // Captcha
				case 'Flow':
					$form->addElement( new \PFBC\Element\Flow($label, $name, $additionalParams) ); break; // Captcha
				case 'User':
					$form->addElement( new \PFBC\Element\User($label, $name, $additionalParams) ); break; // Captcha
				default:
					do_action( 'web_apper_add_form_field', $form, $field['field_type'], $label, $name, $options, $additionalParams ); // Allow form to be hooked onto
			endswitch;
	}

    /**
     * Routes ajax requests
     *
     * @since 1.0
     */
	public function route_ajax_request() {
		check_ajax_referer( 'AwesomeSauce!87', 'web_apper_nonce' );  // Verify the security nonce
		do_action( 'route_ajax_request', $this->shortcode, $_POST['id'] );
		if ( isset($_POST['web_apper_form']) ) : // If the form's submitted data validates
			if ( \PFBC\Form::isValid( $_POST['web_apper_form'] ) ) : // If the form's submitted data validates
				echo $this->$_POST['web_apper_action']();
			else :
				\PFBC\Form::renderAjaxErrorResponse($_POST['web_apper_form']); // Else return error response
			endif;
		elseif ( isset($_POST['web_apper_action']) ) :
			echo $this->$_POST['web_apper_action']();
		endif;
		die();  // Prevents wp ajax from returning a '0'
	}

    /**
     * Echos or JSON encodes a response
     *
	 * @param str $message
	 * @param str $class
	 * @param str $title
	 * @param arr $row
	 * @param arr $data
     * @since 1.0
     */
	function send_response( $message, $title = 'Heads Up!', $class = '', $success = true, $data = null ) {
		$htmlalert =  '<div class="alert ' . $class . ' fade in">'
					. 	'<button type="button" class="close" data-dismiss="alert">&times;</button>'
					. 	'<strong>' . $title . '</strong> ' . $message
					. '</div>';
		if ( isset($_POST['action']) ) :  // If ajax $_POST
			$response['htmlalert'] = $htmlalert;
			$response['success'] = $success;
			if ( isset($_POST['web_apper_action']) ) :
				$response['action'] = $_POST['web_apper_action'];
			endif;
			if ( !empty($data) ) :
				$response['data'] = $data;
			endif;
			return $response;
		else :
			echo $htmlalert;
		endif;
	}

    /**
     * Configure the form obj settings
     *
     * @param array $atts
     * @since 1.0
     */
	protected function template_content_span() {
		global $template;
		if ( stristr($template, 'fullwidth') ) :
			return 12;
		else :
			return 9;
		endif;
	}
	
    /**
     * Get post data for fields
     *
     * @since 1.0
     */
	function get_field_data_from_post() {
		foreach ( $this->fields as $field ) :
			if ( isset( $_POST[$field['field_id']] ) && $field['field_field_only'] != true ) :
				$item[$field['field_id']] = $_POST[$field['field_id']];
			endif;
		endforeach;
		return $item;
	}

}

?>