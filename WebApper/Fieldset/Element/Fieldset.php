<?php
namespace PFBC\Element;

class Fieldset extends Select {
	public function __construct($label, $name, array $properties = null) {
		$options = array('' => '');
		$value = $properties['value'];
		
		global $wpdb;
		$fieldsets = \web_apper_fieldset_get_results( "SELECT ID, fieldset_name FROM {$wpdb->prefix}web_apper_fieldsets" );
		if ( !empty($fieldsets) ) :
			foreach ( $fieldsets as $fieldset ) : // Foreach role
				$options[$fieldset['fieldset_id']] = $fieldset['fieldset_name'];
			endforeach;
		endif;
		parent::__construct($label, $name, $options, $properties);
    }
}