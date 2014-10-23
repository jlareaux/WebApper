<?php
namespace PFBC\Element;

class Condition extends Select {
	public function __construct($label, $name, array $properties = null) {
		$options = array('' => '');
		$value = $properties['value'];
		
		global $wpdb;
		$conditions = \web_apper_condition_get_results( "SELECT ID, condition_name FROM {$wpdb->prefix}web_apper_conditions" );
		if ( !empty($conditions) ) :
			foreach ( $conditions as $condition ) : // Foreach role
				$options[$condition['ID']] = $condition['condition_name'];
			endforeach;
		endif;
		parent::__construct($label, $name, $options, $properties);
    }
}