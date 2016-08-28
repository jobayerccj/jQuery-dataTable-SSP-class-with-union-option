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
 *
 * Customized By jobayercse@gmail.com | http://jobayer34.wordpress.com/
 */


// REMOVE THIS BLOCK - used for DataTables test environment only!
//$file = $_SERVER['DOCUMENT_ROOT'].'/datatables/mysql.php';
//if ( is_file( $file ) ) {
//    include( $file );
//}


class Ssp {

    /**
     * Create the data output array for the DataTables rows
     *
     * @param array $columns Column information array
     * @param array $data    Data from the SQL get
     * @param bool  $isJoin  Determine the query is complex or simple one
     *
     * @return array Formatted data in a row based format
     */
    
    public static function data_output ( $columns, $data, $isJoin = false )
    {
        $out = array();

        for ( $i=0, $ien=count($data) ; $i<$ien ; $i++ ) {
            $row = array();

            for ( $j=0, $jen=count($columns) ; $j<$jen ; $j++ ) {
                $column = $columns[$j];

                // Is there a formatter?
                if ( isset( $column['formatter'] ) ) {
                    $row[ $column['dt'] ] = ($isJoin) ? $column['formatter']( $data[$i][ $column['field'] ], $data[$i] ) : $column['formatter']( $data[$i][ $column['db'] ], $data[$i] );
                }
                else {
                    $row[ $column['dt'] ] = htmlentities( ($isJoin) ? $data[$i][ $columns[$j]['field'] ] : $data[$i][ $columns[$j]['db'] ] );
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
    public static function limit ( $request, $columns )
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
     *  @param  bool  $isJoin  Determine the query is complex or simple one
     *
     *  @return string SQL order by clause
     */
    public static function order ( $request, $columns, $isJoin = false )
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
                $column_name = explode(' ', $column['db']);
                //$column_name = (isset($column_name[2])) ? $column_name[2] : $column_name[0];
                if(isset($column_name[2])){
                    $column_name = $column_name[2];
                }
                else{
                    $column_name = $column_name[0];
                }
                

                if ( $requestColumn['orderable'] == 'true' ) {
                    $dir = $request['order'][$i]['dir'] === 'asc' ?
                        'ASC' :
                        'DESC';

                    $orderBy[] = ($isJoin) ? $column_name.' '.$dir : '`'.$column_name.'` '.$dir;
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
     *  @param  array $bindings Array of values for PDO bindings, used in the sql_exec() function
     *  @param  bool  $isJoin  Determine the the query is complex or simple one
     *
     *  @return string SQL where clause
     */
    public static function filter ( $request, $columns, &$bindings, $isJoin = false )
    {
        $globalSearch = array();
        $columnSearch = array();
        $dtColumns = SSP::pluck( $columns, 'dt' );

        if ( isset($request['search']) && $request['search']['value'] != '' ) {
            $str = $request['search']['value'];

            for ( $i=0, $ien=count($request['columns']) ; $i<$ien ; $i++ ) {
                $requestColumn = $request['columns'][$i];
                $columnIdx = array_search( $requestColumn['data'], $dtColumns );
                $column = $columns[ $columnIdx ];
                $column_name = explode(' ', $column['db']);

                if ( $requestColumn['searchable'] == 'true' ) {
                    $binding = SSP::bind( $bindings, '%'.$str.'%', PDO::PARAM_STR );
                    $globalSearch[] = ($isJoin) ? $column_name[0]." LIKE ".$binding : "`".$column_name[0]."` LIKE ".$binding;
                }
            }
        }

        // Individual column filtering
        for ( $i=0, $ien=count($request['columns']) ; $i<$ien ; $i++ ) {
            $requestColumn = $request['columns'][$i];
            $columnIdx = array_search( $requestColumn['data'], $dtColumns );
            $column = $columns[ $columnIdx ];
            $column_name = explode(' ', $column['db']);
            //$column_name = ($column_name[2]) ? $column_name[0] : $column_name[0];

            
            $str = $requestColumn['search']['value'];

            if ( $requestColumn['searchable'] == 'true' &&
                $str != '' ) {
                $binding = SSP::bind( $bindings, '%'.$str.'%', PDO::PARAM_STR );
                $columnSearch[] = ($isJoin) ? $column_name[0]." LIKE ".$binding : "`".$column_name[0]."` LIKE ".$binding;
            }
        }

        // Combine the filters into a single string
        $where = '';

        if ( count( $globalSearch ) ) {
            $where = '('.implode(' OR ', $globalSearch).')';
        }

        if ( count( $columnSearch ) ) {
            $where = $where === '' ?
                implode(' AND ', $columnSearch) :
                $where .' AND '. implode(' AND ', $columnSearch);
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
     *  @param  array $sql_details SQL connection details - see sql_connect()
     *  @param  string $table SQL table to query
     *  @param  string $primaryKey Primary key of the table
     *  @param  array $columns Column information array
     *  @param  array $joinQuery Join query String
     *  @param  string $extraWhere Where query String
     *
     *  @return array  Server-side processing response array
     *
     */
    public static function simple ( $request, $sql_details, $table, $primaryKey, $columns, $joinQuery = NULL, $extraWhere = '', $groupBy = '', $page = '', $unionWhere = '')
    {
        $bindings = array();
        $db = SSP::sql_connect( $sql_details );

        // Build the SQL query string from the request
        $limit = SSP::limit( $request, $columns );
        $order = SSP::order( $request, $columns, $joinQuery );
        $where = SSP::filter( $request, $columns, $bindings, $joinQuery );

        // IF Extra where set then set and prepare query
        if($extraWhere){
            $extraWhere = ($where) ? ' AND '.$extraWhere : ' WHERE '.$extraWhere;
        }
        if($unionWhere){
            $unionWhere = ($where) ? ' AND '.$unionWhere : ' WHERE '.$unionWhere;
        }
        $groupBy = ($groupBy) ? ' GROUP BY '.$groupBy .' ' : '';
        // Main query to actually get the data
        if($unionWhere && $joinQuery){
            $col = SSP::pluck($columns, 'db', $joinQuery);

            $query =  "SELECT SQL_CALC_FOUND_ROWS ".implode(", ", $col)."
             $joinQuery
             $where
             $extraWhere

             "." UNION "." SELECT  ".implode(", ", $col)."
             $joinQuery
             $where
             $unionWhere
             $groupBy
             $order
             $limit
             ";
        }
        elseif($joinQuery){
            
            $col = SSP::pluck($columns, 'db', $joinQuery);

            $query =  "SELECT SQL_CALC_FOUND_ROWS ".implode(", ", $col)."
			 $joinQuery
			 $where
			 $extraWhere
             $groupBy
             $order
             $limit
			 ";

        }else{
            $query =  "SELECT SQL_CALC_FOUND_ROWS `".implode("`, `", SSP::pluck($columns, 'db'))."`
			 FROM `$table`
			 $where
			 $extraWhere
			 $groupBy
             $order
			 $limit";
        }

        $data = SSP::sql_exec( $db, $bindings,$query);

        // Data set length after filtering
        $resFilterLength = SSP::sql_exec( $db,
            "SELECT FOUND_ROWS()"
        );
        $recordsFiltered = $resFilterLength[0][0];

         // Total data set length
        $count_request = "SELECT COUNT(`{$primaryKey}`)";
        if($joinQuery){
          $count_request .= $joinQuery;
        } else {
          $count_request .= "FROM   `$table`";
        }
        
        $resTotalLength = SSP::sql_exec( $db,$count_request);
        $recordsTotal = $resTotalLength[0][0];

        /*
         * Output
         */
        $all_data = array(
            "draw"            => intval( $request['draw'] ),
            "recordsTotal"    => intval( $recordsTotal ),
            //"recordsTotal"    => $query,
            "recordsFiltered" => intval( $recordsFiltered ),
            "data"            => SSP::data_output( $columns, $data, $joinQuery )
        );

        if($page == "artwork_list_full"){
        	 for($i = 0, $total = count($all_data["data"]); $i<$total; $i++ ){
	            $all_data["data"][$i]['artwork_id'] = $all_data["data"][$i][0];
	            $all_data["data"][$i]['group_id'] = $all_data["data"][$i][9];
	            $all_data["data"][$i]['code_status'] = $all_data["data"][$i][10];
	            $all_data["data"][$i]['product_type'] = $all_data["data"][$i][11];
	            $all_data["data"][$i]['portfolio_id'] = $all_data["data"][$i][12];
	            $all_data["data"][$i]['code'] = $all_data["data"][$i][13];
	            $all_data["data"][$i]['artwork_licenseeID'] = $all_data["data"][$i][7];

                $all_data["data"][$i][15] = artwork_common_action_items2($all_data["data"][$i]);
                $all_data["data"][$i][16] = getCodeBox($all_data["data"][$i]['code'], $all_data["data"][$i]['code_status'], $all_data["data"][$i]['product_type']);
                $all_data["data"][$i][17] = chkPrivacyProtection($all_data["data"][$i][0], 2);

                if($all_data["data"][$i][2] == ""){
                    $all_data["data"][$i][18] = get_owner_invitation_status($all_data["data"][$i][0]);
                }
                else{
                    $all_data["data"][$i][18] = "";
                }
                
                $all_data["data"][$i][19] = getUserdata("type");
            }
        }

        elseif($page == "artwork_list_short"){
             for($i = 0, $total = count($all_data["data"]); $i<$total; $i++ ){
                $all_data["data"][$i]['artwork_id'] = $all_data["data"][$i][0];
                $all_data["data"][$i]['group_id'] = $all_data["data"][$i][9];
                $all_data["data"][$i]['code_status'] = $all_data["data"][$i][10];
                $all_data["data"][$i]['product_type'] = $all_data["data"][$i][11];
                $all_data["data"][$i]['portfolio_id'] = $all_data["data"][$i][12];
                $all_data["data"][$i]['code'] = $all_data["data"][$i][13];
                $all_data["data"][$i]['artwork_licenseeID'] = $all_data["data"][$i][7];

                $all_data["data"][$i][15] = artwork_common_action_items2($all_data["data"][$i]);
                $all_data["data"][$i][16] = getCodeBox($all_data["data"][$i]['code'], $all_data["data"][$i]['code_status'], $all_data["data"][$i]['product_type']);
                $all_data["data"][$i][17] = getGroupCodeBox($all_data["data"][$i]['group_id']);
                $all_data["data"][$i][18] = getPortfolioCodeBox($all_data["data"][$i]['portfolio_id']);
                $all_data["data"][$i][19] = getArtworkMultiples($all_data["data"][$i][0], false, true);
                $all_data["data"][$i][20] = chkPrivacyProtection($all_data["data"][$i][0], 2);

                if($all_data["data"][$i][2] == ""){
                    $all_data["data"][$i][21] = get_owner_invitation_status($all_data["data"][$i][0]);
                }
                else{
                    $all_data["data"][$i][21] = "";
                }

                
            }

        }
        elseif($page == "artwork_code_list"){
            for($i = 0, $total = count($all_data["data"]); $i<$total; $i++ ){
                $all_data["data"][$i][5] = getCodeStatusColor($all_data["data"][$i][0]);

            }
            
        }

        elseif($page == "dsac_code_list"){
            for($i = 0, $total = count($all_data["data"]); $i<$total; $i++ ){
                $all_data["data"][$i][5] = getDsacCodeStatusColor($all_data["data"][$i][0]);

            }
            
        }

        elseif($page == "portfolio_list_short" || $page == "portfolio_list_full" ){
            
            for($i = 0, $total = count($all_data["data"]); $i<$total; $i++ ){
                $all_data["data"][$i][11] = $all_data["data"][$i][0];
                $all_data["data"][$i][0] = getPortfolioCodeBox($all_data["data"][$i][8], $all_data["data"][$i][0]);
                //$all_data["data"][$i][10] = $all_data["data"][$i][2];
                //$all_data["data"][$i][2] = getUser($all_data["data"][$i][2], "user_displayName");
                $all_data["data"][$i][12] = $all_data["data"][$i][9];
                $all_data["data"][$i][13] = getPGCodeBox($all_data["data"][$i][9]);
                $all_data["data"][$i][5] = getPortfolioMultipleCount($all_data["data"][$i][9]);
                $all_data["data"][$i][6] = date_formation("Y-m-d H:i:s",$all_data["data"][$i][6],  db_timezone(),  client_timezone(),"j M, Y h:i A");
                $all_data["data"][$i][3] = find_artworks_by_portfolio_id($all_data["data"][$i][8]);
                $all_data["data"][$i][14] = isPortfolioGreen($all_data["data"][$i][8]);
                $all_data["data"][$i][15] = yellow_artwork_count_by_portfolio_and_licensee($all_data["data"][$i][8]);
                $all_data["data"][$i][16] = artwork_count_by_portfolio_and_licensee($all_data["data"][$i][8]);
            }
            
        }
        elseif($page == "artwork_group_list_individual"){
            for($i = 0, $total = count($all_data["data"]); $i<$total; $i++ ){
                $all_data["data"][$i][12] = getMultiplesCountByGroupID($all_data["data"][$i][5]);
                $all_data["data"][$i][7] = find_artworks_by_group_id($all_data["data"][$i][5]);
                $all_data["data"][$i][8] = yellow_artwork_count_by_group_and_licensee($all_data["data"][$i][5]);
                $all_data["data"][$i][9] = getGroupGreenStatus($all_data["data"][$i][5],$all_data["data"][$i][0]);
                $all_data["data"][$i][10] = getGroupCodeBox($all_data["data"][$i][5],$all_data["data"][$i][0]);
                $all_data["data"][$i][11] = artwork_count_by_group_and_licensee($all_data["data"][$i][5]);

            }
            
        }
        elseif($page == "portfolio_group_list_individual"){
            for($i = 0, $total = count($all_data["data"]); $i<$total; $i++ ){
               $all_data["data"][$i][7] = count_portfolio_IDs_by_group_id($all_data["data"][$i][5]);
                $all_data["data"][$i][8] = select_portfolio_IDs_by_group_id($all_data["data"][$i][5]);
                $all_data["data"][$i][9] = getPGCodeBox($all_data["data"][$i][5],$all_data["data"][$i][0], true);
                $all_data["data"][$i][10] = generate_code_box($all_data["data"][$i][0],$all_data["data"][$i][9]);
                
            }
            
        }
        elseif($page == "artwork_multiple_list"){
             for($i = 0, $total = count($all_data["data"]); $i<$total; $i++ ){
                $all_data["data"][$i]['artwork_id'] = $all_data["data"][$i][0];
                $all_data["data"][$i]['group_id'] = $all_data["data"][$i][9];
                $all_data["data"][$i]['code_status'] = $all_data["data"][$i][10];
                $all_data["data"][$i]['product_type'] = $all_data["data"][$i][11];
                $all_data["data"][$i]['portfolio_id'] = $all_data["data"][$i][12];
                $all_data["data"][$i]['code'] = $all_data["data"][$i][13];
                $all_data["data"][$i]['artwork_licenseeID'] = $all_data["data"][$i][7];

                $all_data["data"][$i][15] = artwork_common_action_items2($all_data["data"][$i]);
                $all_data["data"][$i][16] = getCodeBox($all_data["data"][$i]['code'], $all_data["data"][$i]['code_status'], $all_data["data"][$i]['product_type']);
                $all_data["data"][$i][17] = chkPrivacyProtection($all_data["data"][$i][0], 2);
            }
        }
        elseif($page == "portfolio_multiple_list"){
            for($i = 0, $total = count($all_data["data"]); $i<$total; $i++ ){
                $all_data["data"][$i][8] = $all_data["data"][$i][0];
                $all_data["data"][$i][0] = getPortfolioCodeBox($all_data["data"][$i][7], $all_data["data"][$i][0]);
                
                $all_data["data"][$i][9] = find_artworks_by_portfolio_id($all_data["data"][$i][7]);
                $all_data["data"][$i][10] = count_artworks_by_portfolio_id($all_data["data"][$i][7]);
            }
            
        }
       
        return $all_data;
    }


    /**
     * Connect to the database
     *
     * @param  array $sql_details SQL server connection details array, with the
     *   properties:
     *     * host - host name
     *     * db   - database name
     *     * user - user name
     *     * pass - user password
     * @return resource Database connection handle
     */
    public static function sql_connect ( $sql_details )
    {
        try {
            $db = @new PDO(
                "mysql:host={$sql_details['host']};dbname={$sql_details['db']}",
                $sql_details['user'],
                $sql_details['pass'],
                array( PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION )
            );
            $db->query("SET NAMES 'utf8'");
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
    public static function sql_exec ( $db, $bindings, $sql=null )
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
    public static function fatal ( $msg )
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
    public static function bind ( &$a, $val, $type )
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
     *  @param  bool  $isJoin  Determine the the JOIN/complex query or simple one
     *  @return array        Array of property values
     */
    public static function pluck ( $a, $prop, $isJoin = false )
    {
        $out = array();

        for ( $i=0, $len=count($a) ; $i<$len ; $i++ ) {
            $out[] = ($isJoin && isset($a[$i]['as'])) ? $a[$i][$prop]. ' AS '.$a[$i]['as'] : $a[$i][$prop];
        }

        return $out;
    }
}
