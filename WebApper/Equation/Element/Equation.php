<?php
namespace PFBC\Element;

class Equation extends Select {
	public function __construct($label, $name, array $properties = null) {
		$options = array('' => '');
		$value = $properties['value'];
		
		global $wpdb;
		$equations = \web_apper_equation_get_results( "SELECT ID, equation_name FROM {$wpdb->prefix}web_apper_equations" );
		if ( !empty($equations) ) :
			foreach ( $equations as $equation ) : // Foreach role
				$options[$equation['equation_id']] = $equation['equation_name'];
			endforeach;
		endif;
		parent::__construct($label, $name, $options, $properties);
    }
}