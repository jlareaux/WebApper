<?php
namespace PFBC\Element;

class Action extends Select {
	public function __construct($label, $name, array $properties = null) {
		$options = array('' => '');
		$value = $properties['value'];
		
		global $wpdb;
		$actions = \web_apper_action_get_results( "SELECT ID, action_name FROM {$wpdb->prefix}web_apper_actions" );
		if ( !empty($actions) ) :
			foreach ( $actions as $action ) : // Foreach role
				$options[$action['ID']] = $action['action_name'];
			endforeach;
		endif;
		parent::__construct($label, $name, $options, $properties);
    }
}