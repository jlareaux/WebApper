<?php
namespace WebApper\Module;

class Item extends \WebApper\Base {

	/**
	 * The name of the module DB table
	 *
	 * @var string
	 */
	public $table;

	/**
	 * The item id
	 *
	 * @var integer
	 */
	public $ID;

	/**
	 * Associative array module properties
	 *
	 * @var array
	 */
	private $_properties;


	/** Public Methods ********************************************************/

	/**
	 * Set a module property
	 *
	 * @param string $name The porperty name
	 * @param string $val The porperty value
	 * @return mixed false on failure
	 */
	public function __set( $name, $val ) { // Magic method
		if ( is_array( $val ) ) :
			$val = serialize( $val );
		endif;
		$this->_properties[$name] = $val; // set value,
	}

	/**
	 * Get a module property
	 *
	 * @param string $name The porperty name
	 * @return mixed false on failure
	 */
	public function __get( $name ) {
		if ( !array_key_exists( $name, $this->_properties ) || !isset($this->_properties[$name]) ) :
			return false; 
		else :
			if ( $this->isSerialized( $val ) ) :
				$val = unserialize( $val );
			endif;
			return $this->_properties[$name];
		endif;
	}

	/**
	 * Unset a module property
	 *
	 * @param string $name The porperty name
	 * @return mixed false on failure
	 */
	public function __unset( $name ) {
        unset( $this->_properties[$name] );
    }

	/**
	 * Populates the module properties if an ID is passed
	 *
	 * @param integer $ID
	 */
	public function __construct( $ID = 0 ) {
		if ( !empty( $ID ) ) {
			$this->ID = $ID;
			$this->populate();
		}
	}

	/**
	 * Fetches an Item's data from the database.
	 *
	 * @return arr Associative array of Item data
	 */
	function get_data() {
 		return $this->_properties;
	}

	/**
	 * Update or insert a item
	 *
	 * @global wpdb $wpdb WordPress database object
	 * @global webapper $webapper The WebApper object
	 * @return bool true on success, false on failure
	 */
	public function save() {
		global $wpdb;

		// Insert
		if ( empty( $this->ID ) ) {
			$result = $wpdb->insert( 
				$wpdb->prefix . $this->table, 
				$this->_properties
			);
			$this->ID = $wpdb->insert_id;

		// Update
		} else {
			$result = $wpdb->update( 
				$wpdb->prefix . $this->table, 
				$this->_properties, 
				array( 'ID' => $this->ID )
			);	
			if ( $result == 0 ) {
				return $result = 1;
			}
		}
	
		if ( !$result )
			return false;

		return true;
	}

    /**
     * Delete a item
     *
	 * @global wpdb $wpdb WordPress database object
	 * @global webapper $webapper The WebApper object
	 * @return bool true on success, false on failure
     */
	public function delete() {
		global $wpdb;

		$result = $wpdb->delete(
			$wpdb->prefix . $this->table, 
			array( 'ID' => $this->ID ), 
			array( '%d' )
		);

		if ( !$result )
			return false;

		return true;
	}

	/**
	 * Gets results for any query
	 *
	 * @global wpdb $wpdb WordPress database object
	 * @return array Associative array
	 * @static
	 */
	public function get_results( $query ) {
		global $wpdb;

 		return $wpdb->get_results( $query, ARRAY_A );
	}

	/** Private Methods *******************************************************/

	/**
	 * Fetches the item data from the database.
	 *
	 * @global wpdb $wpdb WordPress database object
	 */
	private function populate() {
		global $wpdb;

		if ( $item = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM {$wpdb->prefix}{$this->table} WHERE ID = %d", $this->ID ) ) ) {
			foreach ( $item as $key => $val ) :
				$this->_properties[$key] = $val;
			endforeach;
		}
	}

}

?>