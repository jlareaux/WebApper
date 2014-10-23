<?php
namespace PFBC\Element;

class Flow extends Select {
	public function __construct($label, $name, array $properties = null) {
		$options = array('' => '');
		$value = $properties['value'];
		
		global $wpdb;
		$flows = \web_apper_flow_get_results( "SELECT ID, flow_name FROM {$wpdb->prefix}web_apper_flows" );
		if ( !empty($flows) ) :
			foreach ( $flows as $flow ) : // Foreach role
				$options[$flow['ID']] = $flow['flow_name'];
			endforeach;
		endif;
		parent::__construct($label, $name, $options, $properties);
    }
}