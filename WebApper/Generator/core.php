<?php
/*
WebApper Moddule Name: Generator
*/

namespace WebApper\Generator;

// Include Module files
foreach( glob ( dirname(__FILE__) . '/Shortcode/*.php' ) as $filename ) {
	require_once( $filename );
}