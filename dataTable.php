<?php
 
/*
 * DataTables example server-side processing script.
 *
 * Please note that this script is intentionally extremely simply to show how
 * server-side processing can be implemented, and probably shouldn't be used as
 * the basis for a large complex system. It is suitable for simple use cases as
 * for learning.
 *
 * See http://datatables.net/usage/server-side for full details on the server-
 * side processing requirements of DataTables.
 *
 * @license MIT - http://datatables.net/license_mit
 */
 
/* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *
 * Easy set variables
 */


    // DB table to use
    $table = $_POST['table'];
     
    // Table's primary key
    $primaryKey = $_POST['primary_key'];

    // Array of database columns which should be read and sent back to DataTables.
    // The `db` parameter represents the column name in the database, while the `dt`
    // parameter represents the DataTables column identifier. In this case simple
    // indexes
    
    $columns = $_POST['columns'];

    //$columns['id'] = $columns[`artwork`.`artwork_id`];
   
     
    // SQL server connection information

    $sql_details = array(
            'user' => 'root',
            'pass' => '',
            'db'   => 'dataTable',
            'host' => 'localhost'
        );

     
    /* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *
     * If you just want to use the basic configuration for DataTables with PHP
     * server-side, there is no need to edit below this line.
     */

    if($_POST['page'] == "order_details"){

        // Basic Join with Union example, you can do this type of work using another query quite easily, it's an example for union
        $joinQuery = "FROM orderdetails JOIN products on (products.productCode = orderdetails.productCode)";
       
        $extraWhere = "orderNumber = '10100'";
        $unionWhere = "orderNumber != '10100'";
        
        $group_by = "";
        
    }

    require('ssp.php' );
    
    echo json_encode(SSP::simple( $_POST, $sql_details, $table, $primaryKey, $columns, $joinQuery, $extraWhere, $group_by , $_POST['page'], $unionWhere));
    
?>