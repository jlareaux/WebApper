<?php
namespace PFBC\Element;

class Field extends Select {
	public function __construct($label, $name, array $properties = null) {
		$options = array('' => '');
		$value = $properties['value'];
		
		global $wpdb;
		$fields = \web_apper_field_get_results( "SELECT ID, field_name FROM {$wpdb->prefix}web_apper_fields" );
		if ( !empty($fields) ) :
			foreach ( $fields as $field ) : // Foreach role
				$options[$field['field_id']] = $field['field_name'];
			endforeach;
		endif;
		parent::__construct($label, $name, $options, $properties);
    }
}