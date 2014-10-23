<?php
/*
 * [generator id='Generator' usercap='edit_plugins' ownercap='edit_plugins']
 *
 */

namespace WebApper\Shortcode;

class Generator extends \WebApper\Shortcode {
	
    /**
     * Define shortcode properties
     *
     */
	protected $shortcode = 'generator';

    /**
     * Handles the add post shortcode
     *
     * @param array $atts
     */
    public function shortcode( $atts ) {

		// Build the shortcode output hrml string
		global $post;
		$generate = $_GET['generate'];
		
		?>
		<ul class="nav nav-pills">
			<li<?php if ( $generate == null ) echo ' class="active"'; ?>><a href="<?php echo get_permalink($post->ID); ?>"><i class="icon-home"></i> Getting Started</a></li>
			<!--<li<?php if ( $generate == 'form' ) echo ' class="active"'; ?>><a href="<?php echo get_permalink($post->ID); ?>/?generate=recordform"><i class="icon-list"></i> Form shortcode</a></li>-->
			<!--<li<?php if ( $generate == 'indextable' ) echo ' class="active"'; ?>><a href="<?php echo get_permalink($post->ID); ?>/?generate=recordindex"><i class="icon-list-alt"></i> Indextable shortcode</a></li>-->
			<li<?php if ( $generate == 'posttypes' ) echo ' class="active"'; ?>><a href="<?php echo get_permalink($post->ID); ?>/?generate=posttypes"><i class="icon-th-list"></i> Posttypes</a></li>
			<li<?php if ( $generate == 'fieldsets' ) echo ' class="active"'; ?>><a href="<?php echo get_permalink($post->ID); ?>/?generate=fieldsets"><i class="icon-th-large"></i> Fieldsets</a></li>
			<li<?php if ( $generate == 'fields' ) echo ' class="active"'; ?>><a href="<?php echo get_permalink($post->ID); ?>/?generate=fields"><i class="icon-th"></i> Fields</a></li>
			<li<?php if ( $generate == 'actions' ) echo ' class="active"'; ?>><a href="<?php echo get_permalink($post->ID); ?>/?generate=actions"><i class="icon-th"></i> Actions</a></li>
			<li<?php if ( $generate == 'equations' ) echo ' class="active"'; ?>><a href="<?php echo get_permalink($post->ID); ?>/?generate=equations"><i class="icon-th"></i> Equations</a></li>
			<li<?php if ( $generate == 'conditions' ) echo ' class="active"'; ?>><a href="<?php echo get_permalink($post->ID); ?>/?generate=conditions"><i class="icon-th"></i> Conditions</a></li>
			<li<?php if ( $generate == 'flows' ) echo ' class="active"'; ?>><a href="<?php echo get_permalink($post->ID); ?>/?generate=flows"><i class="icon-th"></i> Flows</a></li>
			<!--<li<?php if ( $generate == 'tasks' ) echo ' class="active"'; ?>><a href="<?php echo get_permalink($post->ID); ?>/?generate=tasks"><i class="icon-th"></i> Task builder</a></li>-->
		</ul>
		<hr />
		<?php

		switch ( $generate ) :
			case null :
				?>
				<div class="alert alert-info fade in">
					<button type="button" class="close" data-dismiss="alert">&times;</button>
					<strong>How To!</strong> Use this page to help you get setup!
				</div>
				<?php
			break;
			case 'recordform' :
				do_shortcode("[generator_recordform]");
			break;
			case 'recordindex' :
				do_shortcode("[generator_recordindex]");
			break;
			case 'posttypes' :
				do_shortcode("[posttype_builder id='PosttypeBuilder']");
			break;
			case 'fieldsets' :
				do_shortcode("[fieldset_builder]");
			break;
			case 'fields' :
				do_shortcode("[field_builder']");
			break;
			case 'actions' :
				do_shortcode("[action_builder']");
			break;
			case 'equations' :
				do_shortcode("[equation_builder']");
			break;
			case 'conditions' :
				do_shortcode("[condition_builder']");
			break;
			case 'flows' :
				do_shortcode("[flow_builder']");
			break;
			case 'tasks' :
				do_shortcode("[task_builder id='TaskBuilder']");
			break;
		endswitch;
	}
	
}

$initialize = new Generator(); 

?>