<?php

/*
 * Helper functions for building a DataTables server-side processing SQL query
 *
 * The static functions in this class are just helper functions to help build
 * the SQL used in the DataTables demo server-side processing scripts. These
 * functions obviously do not represent all that can be done with server-side
 * processing, they are intentionally simple to show how it works. More complex
 * server-side processing operations will likely require a custom script.
 *
 * See http://datatables.net/usage/server-side for full details on the server-
 * side processing requirements of DataTables.
 *
 * @license MIT - http://datatables.net/license_mit
 */


class SSP {
	/**
	 * Create the data output array for the DataTables rows
	 *
	 *  @param  array $columns Column information array
	 *  @param  array $data    Data from the SQL get
	 *  @return array          Formatted data in a row based format
	 */
	static function data_output ( $columns, $data )
	{
		$out = array();

		for ( $i=0, $ien=count($data) ; $i<$ien ; $i++ ) {
			$row = array();

			for ( $j=0, $jen=count($columns) ; $j<$jen ; $j++ ) {
				$column = $columns[$j];

				// Is there a formatter?
				if ( isset( $column['formatter'] ) ) {
					$row[ $column['dt'] ] = $column['formatter']( $data[$i][ $column['db'] ], $data[$i] );
				}
				else {
					$row[ $column['dt'] ] = $data[$i][ $columns[$j]['db'] ];
				}
			}

			$out[] = $row;
		}

		return $out;
	}


	/**
	 * Paging
	 *
	 * Construct the LIMIT clause for server-side processing SQL query
	 *
	 *  @param  array $request Data sent to server by DataTables
	 *  @param  array $columns Column information array
	 *  @return string SQL limit clause
	 */
	static function limit ( $request, $columns )
	{
		$limit = '';

		if ( isset($request['start']) && $request['length'] != -1 ) {
			$limit = "LIMIT ".intval($request['start']).", ".intval($request['length']);
		}

		return $limit;
	}


	/**
	 * Ordering
	 *
	 * Construct the ORDER BY clause for server-side processing SQL query
	 *
	 *  @param  array $request Data sent to server by DataTables
	 *  @param  array $columns Column information array
	 *  @return string SQL order by clause
	 */
	static function order ( $request, $columns )
	{
		$order = '';

		if ( isset($request['order']) && count($request['order']) ) {
			$orderBy = array();
			$dtColumns = SSP::pluck( $columns, 'dt' );

			for ( $i=0, $ien=count($request['order']) ; $i<$ien ; $i++ ) {
				// Convert the column index into the column data property
				$columnIdx = intval($request['order'][$i]['column']);
				$requestColumn = $request['columns'][$columnIdx];

				$columnIdx = array_search( $requestColumn['data'], $dtColumns );
				$column = $columns[ $columnIdx ];

				if ( $requestColumn['orderable'] == 'true' ) {
					$dir = $request['order'][$i]['dir'] === 'asc' ?
						'ASC' :
						'DESC';

					$orderBy[] = '`'.$column['db'].'` '.$dir;
				}
			}

			$order = 'ORDER BY '.implode(', ', $orderBy);
		}

		return $order;
	}


	/**
	 * Searching / Filtering
	 *
	 * Construct the WHERE clause for server-side processing SQL query.
	 *
	 * NOTE this does not match the built-in DataTables filtering which does it
	 * word by word on any field. It's possible to do here performance on large
	 * databases would be very poor
	 *
	 *  @param  array $request Data sent to server by DataTables
	 *  @param  array $columns Column information array
	 *  @param  array $bindings Array of values for PDO bindings, used in the
	 *    sql_exec() function
	 *  @return string SQL where clause
	 */
	static function filter ( $request, $columns, &$bindings )
	{
		$globalSearch = array();
		$columnSearch = array();
		$dtColumns = SSP::pluck( $columns, 'dt' );

		if ( isset($request['search']) && $request['search']['value'] != '' ) {
			$strs = explode(' ', $request['search']['value'] );
			
			foreach ( $strs as $str ) :
				for ( $i=0, $ien=count($request['columns']) ; $i<$ien ; $i++ ) {
					$requestColumn = $request['columns'][$i];
					$columnIdx = array_search( $requestColumn['data'], $dtColumns );
					$column = $columns[ $columnIdx ];
	
					if ( $requestColumn['searchable'] == 'true' ) {
						$binding = SSP::bind( $bindings, '%'.$str.'%', PDO::PARAM_STR );
						$globalSearch[] = "`".$column['db']."` LIKE ".$binding;
					}
				}
			endforeach;
		}

		// Individual column filtering
		for ( $i=0, $ien=count($request['columns']) ; $i<$ien ; $i++ ) {
			$requestColumn = $request['columns'][$i];
			$columnIdx = array_search( $requestColumn['data'], $dtColumns );
			$column = $columns[ $columnIdx ];

			$like = $requestColumn['search']['value'];
			$min = $requestColumn['search']['min'];
			$max = $requestColumn['search']['max'];
			$equals = $requestColumn['search']['equals'];

			if ( $requestColumn['searchable'] == 'true' ) {
				if ( $like != '' ) {
					$binding = SSP::bind( $bindings, '%' . $like . '%', PDO::PARAM_STR );
					$columnSearch[] = "`" . $column['db'] . "` LIKE " . $binding;
				}
				if ( $min != '' ) {
					$binding = SSP::bind( $bindings, $min, PDO::PARAM_INT );
					$columnSearch[] = "`" . $column['db'] . "` >= " . $binding;
				}
				if ( $max != '' ) {
					$binding = SSP::bind( $bindings, $max, PDO::PARAM_INT );
					$columnSearch[] = "`" . $column['db'] . "` <= " . $binding;
				}
				if ( $equals != '' ) {
					$equals = explode(',', $equals );
					foreach ( $equals as $equal ) :
						$binding = SSP::bind( $bindings, $equal, PDO::PARAM_STR );
						$columnEquals[] = "`" . $column['db'] . "` LIKE " . $binding;
					endforeach;
				}
			}
		}

		// Combine the filters into a single string
		$where = '';
		$where = apply_filters( 'dt_ssp_filter', $where, $request['id'] ); // Allow filtering of query args before fetching posts

		if ( count( $globalSearch ) ) {
			if ( $where === '' ) {
				$where = '('.implode(' OR ', $globalSearch).')';
			} else {
				$where .= ' AND ('.implode(' OR ', $globalSearch).')';
			}				
		}

		if ( count( $columnSearch ) ) {
			if ( $where === '' ) {
				$where = implode(' AND ', $columnSearch);
			} else {
				$where .= ' AND '. implode(' AND ', $columnSearch);
			}				
		}
		
		if ( count( $columnEquals ) ) {
			if ( $where === '' ) {
				$where = '( ' . implode(' OR ', $columnEquals) . ' )';
			} else {
				$where .= ' AND ( ' . implode(' OR ', $columnEquals) . ' )';
			}				
		}

		if ( $where !== '' ) {
			$where = 'WHERE '.$where;
		}

		return $where;
	}


	/**
	 * Perform the SQL queries needed for an server-side processing requested,
	 * utilising the helper functions of this class, limit(), order() and
	 * filter() among others. The returned array is ready to be encoded as JSON
	 * in response to an SSP request, or can be modified if needed before
	 * sending back to the client.
	 *
	 *  @param  array $request Data sent to server by DataTables
	 *  @param  string $table SQL table to query
	 *  @param  string $primaryKey Primary key of the table
	 *  @param  array $columns Column information array
	 *  @return array          Server-side processing response array
	 */
	static function simple ( $request, $table, $primaryKey, $columns )
	{
		$bindings = array();
		$db = SSP::sql_connect();
		global $wpdb, $webapper;

		if ( $table === $wpdb->prefix . 'web_apper_records' ) :

			foreach ( $_POST['columns'] as $col ) :  // Foreach Field,
				$cfs[] = $col['name'];
			endforeach;

			// Create a temporary table with each custom field as a column
			foreach ( $cfs as $cf ) : // Add each custom fields as a column to the temporary table SQL statement
				$fields .= "web_apper_" . str_replace( '-', '_', $cf ) . " TEXT DEFAULT NULL,
				";
				$fields = apply_filters( 'dt_ssp_temp_table_cols', $fields ); // Create hook to 
			endforeach;

			$stmt = $db->prepare(
				"CREATE TEMPORARY TABLE `{$wpdb->prefix}web_apper_records` (
					ID BIGINT(20) UNSIGNED NOT NULL,
					post_author BIGINT(20) UNSIGNED NOT NULL,
					post_date DATETIME NOT NULL,
					post_status VARCHAR(20) NOT NULL,
					post_modified DATETIME NOT NULL,
					post_type VARCHAR(20) NOT NULL,
					" . $fields . "PRIMARY KEY (ID)
				);"
			);  // Execute the temporary table SQL statement
			$stmt->execute();
			
			// Get posts to add to the temporary table
			global $wpdb;
			$query_args = array( 'post_type' => $request['web_apper_posttype'], 'posts_per_page' => -1 );  // Set query arguments
			$query_args = apply_filters( 'dt_ssp_cf_table_query_args', $query_args, $request['id'] ); // Allow filtering of query args before fetching posts
			$posts = get_posts( $query_args );  // Get the posts
			// Insert posts into temporary table
			$fields = "(`ID`, `post_author`, `post_date`, `post_status`, `post_modified`, `post_type`";
			foreach ( $cfs as $cf ) : // Custom field columns
				$fields .= ", `web_apper_" . str_replace( '-', '_', $cf ) . "`";
			endforeach;
			
			$fields = apply_filters( 'dt_ssp_cf_table_insert_cols', $fields ); // Create hook to 
			$fields .= ")";
			foreach ( $posts as $post ) : // 
				$values .= "('" . $post->ID . "', '" . $post->post_author . "', '" 
				. $post->post_date . "', '" . $post->post_status . "', '" . $post->post_modified . "', '" . $post->post_type . "'";
				
				foreach ( $cfs as $cf ) : // Custom field values
					$values .= ", '" . str_replace( "'", "\'", get_post_meta( $post->ID, $cf, true ) ) . "'";
				endforeach;
				$values = apply_filters( 'dt_ssp_cf_table_insert_vals', $values, $post->ID ); // Create hook to 
				$values .= "), ";
			endforeach;

			$stmt = $db->prepare(
				"INSERT INTO `{$wpdb->prefix}web_apper_records` "
				. $fields
				. " VALUES "
				. substr($values, 0, -2)
			);  // Execute posts insert into temporary table
			try { $stmt->execute(); }
			catch (PDOException $e) { /*SSP::fatal( "An SQL error occurred: ".$e->getMessage() );*/ }
		endif;
		



		// Build the SQL query string from the request
		$limit = SSP::limit( $request, $columns );
		$order = SSP::order( $request, $columns );
		$where = SSP::filter( $request, $columns, $bindings );

		$data = SSP::sql_exec( $db, $bindings,
			"SELECT SQL_CALC_FOUND_ROWS `".implode("`, `", SSP::pluck($columns, 'db'))."`
			 FROM `$table`
			 $where
			 $order
			 $limit"
		);
		// Data set length after filtering
		$resFilterLength = SSP::sql_exec( $db,
			"SELECT FOUND_ROWS()"
		);
		$recordsFiltered = $resFilterLength[0][0];

		// Total data set length
		$where = '';
		$where = apply_filters( 'dt_ssp_filter', $where, $request['id'] ); // Allow filtering of query args before fetching posts
		if ( $where !== '' ) {
			$where = 'WHERE '.$where;
		}
		$resTotalLength = SSP::sql_exec( $db,
			"SELECT COUNT(`{$primaryKey}`)
			 FROM `$table`
			 $where"
		);
		$recordsTotal = $resTotalLength[0][0];


		/*
		 * Output
		 */
		return array(
			"draw"            => intval( $request['draw'] ),
			"recordsTotal"    => intval( $recordsTotal ),
			"recordsFiltered" => intval( $recordsFiltered ),
			"data"            => SSP::data_output( $columns, $data )
		);
	}


	/**
	 * Connect to the database
	 *
	 * @return resource Database connection handle
	 */
	static function sql_connect ()
	{
		try {
			$db = @new PDO(
				"mysql:host=" . DB_HOST . ";dbname=" . DB_NAME,
				DB_USER,
				DB_PASSWORD,
				array( PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION )
			);
		}
		catch (PDOException $e) {
			SSP::fatal(
				"An error occurred while connecting to the database. ".
				"The error reported by the server was: ".$e->getMessage()
			);
		}

		return $db;
	}


	/**
	 * Execute an SQL query on the database
	 *
	 * @param  resource $db  Database handler
	 * @param  array    $bindings Array of PDO binding values from bind() to be
	 *   used for safely escaping strings. Note that this can be given as the
	 *   SQL query string if no bindings are required.
	 * @param  string   $sql SQL query to execute.
	 * @return array         Result from the query (all rows)
	 */
	static function sql_exec ( $db, $bindings, $sql=null )
	{
		// Argument shifting
		if ( $sql === null ) {
			$sql = $bindings;
		}

		$stmt = $db->prepare( $sql );
		//echo $sql;

		// Bind parameters
		if ( is_array( $bindings ) ) {
			for ( $i=0, $ien=count($bindings) ; $i<$ien ; $i++ ) {
				$binding = $bindings[$i];
				$stmt->bindValue( $binding['key'], $binding['val'], $binding['type'] );
			}
		}

		// Execute
		try {
			$stmt->execute();
		}
		catch (PDOException $e) {
			SSP::fatal( "An SQL error occurred: ".$e->getMessage() );
		}

		// Return all
		return $stmt->fetchAll();
	}


	/* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *
	 * Internal methods
	 */

	/**
	 * Throw a fatal error.
	 *
	 * This writes out an error message in a JSON string which DataTables will
	 * see and show to the user in the browser.
	 *
	 * @param  string $msg Message to send to the client
	 */
	static function fatal ( $msg )
	{
		echo json_encode( array( 
			"error" => $msg
		) );

		exit(0);
	}

	/**
	 * Create a PDO binding key which can be used for escaping variables safely
	 * when executing a query with sql_exec()
	 *
	 * @param  array &$a    Array of bindings
	 * @param  *      $val  Value to bind
	 * @param  int    $type PDO field type
	 * @return string       Bound key to be used in the SQL where this parameter
	 *   would be used.
	 */
	static function bind ( &$a, $val, $type )
	{
		$key = ':binding_'.count( $a );

		$a[] = array(
			'key' => $key,
			'val' => $val,
			'type' => $type
		);

		return $key;
	}


	/**
	 * Pull a particular property from each assoc. array in a numeric array, 
	 * returning and array of the property values from each item.
	 *
	 *  @param  array  $a    Array to get data from
	 *  @param  string $prop Property to read
	 *  @return array        Array of property values
	 */
	static function pluck ( $a, $prop )
	{
		$out = array();

		for ( $i=0, $len=count($a) ; $i<$len ; $i++ ) {
			$out[] = $a[$i][$prop];
		}

		return $out;
	}
}