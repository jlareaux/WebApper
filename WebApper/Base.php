<?php

namespace WebApper;

abstract class Base {

	/** Public Methods ********************************************************/

    /**
     * Checks if current user has proper privileges
     *
	 * @param string $usercap
     * @since 1.0
     */
	protected  function current_user_has_cap( $cap, $args = null ) {
		if ( empty($cap) ) :
			return false;
		elseif ( $cap == 'logged_out' ) :
			return true;
		elseif ( is_user_logged_in() ) :
			if ( current_user_can( $cap, $args ) ) :
				return true;
			else :
				return false;
			endif;
		else :
			return false;
		endif;
	}
	
    /**
     * Checks if current user has proper privileges
     *
     * @since 1.0
	 * @param string $cap
	 * @return arr $users
     */
	protected  function get_users_by_cap( $cap ) {
		global $wp_roles;
		$all_roles = $wp_roles->roles;
		$users = array();
		foreach ( $all_roles as $roleID => $role ) : // Foreach role
			if ( $role['capabilities'][$cap] ) : // If the role has the capability
				$users = array_merge( $users, get_users( array( 'role' => $roleID ) ) ); // Get users who have this role
			endif;
		endforeach;
		return $users;
	}

	/** Static Methods ********************************************************/

    /**
     * Returns current page URL.
     *
     * @since 1.0
     */
	static protected function cur_page_url() {
		$pageURL = 'http';
		if ($_SERVER['HTTPS'] == 'on')
			$pageURL .= 's';
		$pageURL .= '://';
		if ($_SERVER['SERVER_PORT'] != '80')
			$pageURL .= $_SERVER['SERVER_NAME'].':'.$_SERVER['SERVER_PORT'].$_SERVER['REQUEST_URI'];
		else
			$pageURL .= $_SERVER['SERVER_NAME'].$_SERVER['REQUEST_URI'];
		return $pageURL;
	}

    /**
     * Checks if saved post meta is serialized
     *
     * @global object $wpdb
	 * @param string $str
     * @since 1.0
     */
	static protected function isSerialized($str) {
		return ($str == serialize(false) || @unserialize($str) !== false);
	}

    /**
     * Converts Yes & No to 1 & 0
     *
	 * @param string $text
     * @since 1.0
     */
	static protected function textToBool( $text ) {
		if ( $text == 'Yes' )
			return true;
		else
			return false;
	}

    /**
     * Converts 1 & 0 to Yes & No
     *
	 * @param boolean $bool
     * @since 1.0
     */
	static protected function boolToText( $bool ) {
		if ( $bool == 1 )
			return 'Yes';
		else
			return 'No';
	}

    /**
     * Converts string to an associative array
     *
	 * @param string $text
     * @since 1.0
     */
	static protected function textToArray( $text ) {
		if ( !empty($text) ) :
			$pairs = explode( '|', $text );
			foreach( $pairs as $pair ) :
				$pair = explode( ',', $pair );
				$array[$pair[0]] = $pair[1];
			endforeach;
			return $array;
		else :
			return false;
		endif;
	}

    /**
     * Converts associative array to string
     *
	 * @param string $text
     * @since 1.0
     */
	static protected function arrayToText( $array ) {
		if ( !empty($array) ) :
			foreach( $array as $key => $val ) :
				$texts[] = $key . ',' . $val;
			endforeach;
			$text = implode( '|', $texts );
			return $text;
		else :
			return false;
		endif;
	}

    /**
     * Converts MySQL time to custom formatted string for DataTables.js
     *
	 * @param string $time
     * @since 1.0
     */
	static protected function format_time( $time ) {
		$time = explode(':', $time);
		$mins = $time[1];
		$hours = $time[0];
		$days = floor( $hours/24 );
		if ( 1 <= $days ) {
			$time_string .= $days . ' day';
			if ( 1 < $days ) {
				$time_string .= 's';
			}
			$time_string .= '<br />';
			$hours = $hours-($days*24);
		}
		if ( 0 < $hours ) {
			$time_string .= ltrim( $hours, 0 ) . ' hour';
			if ( 1 < $hours ) {
				$time_string .= 's';
			}
			$time_string .= '<br />';
		}
		if ( 0 < $mins ) {
			$time_string .= ltrim( $mins, 0 ) . ' minute';
			if ( 1 < $mins ) {
				$time_string .= 's';
			}
		}
		return $time_string;
	}

    /**
     * Converts timestamps to custom formatted string for DataTables.js
     *
	 * @param string $time
     * @since 1.0
     */
	static protected function days_since( $date ) {
		$currentDate = new \DateTime( current_time('mysql') );
		$date = $currentDate->diff( new \DateTime($date) )->d;
		if ( $date == 0 )
			$date = 'today';
		else
			$date .= ' days ago';

		return $date;
	}
	
}

?>
