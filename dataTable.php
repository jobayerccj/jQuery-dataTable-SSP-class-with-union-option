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
     
    //require( 'ssp.class.php' );

    //$joinQuery = "FROM {$table} AS u LEFT JOIN country AS m on u.country_id = m.country_id LEFT JOIN region AS r ON u.region_id = r.region_id AND u.country_id = r.country_id"; 


    if($_POST['page'] == "order_details"){

        $joinQuery = "FROM orderdetails LEFT JOIN products on (products.productCode = orderdetails.productCode)";
        //$joinQuery ="";

        $extraWhere = "";
        $extraWhere2 = "";
        
        $group_by = "";
        
    }

   

    else if($_POST['page'] == "portfolio_list_short"){
        $joinQuery = "FROM artwork_portfolio LEFT JOIN artwork_portfolio_groups on (artwork_portfolio.pg_id = artwork_portfolio_groups.pg_id) LEFT JOIN user on (artwork_portfolio.portfolio_owner_id = user.user_id) LEFT JOIN artwork_portfolio_lang on (artwork_portfolio_lang.portfolio_id = artwork_portfolio.portfolio_id AND artwork_portfolio_lang.lang_code ='".getLanguage()."') ";
        //$extraWhere = "((artwork_portfolio.portfolio_licensee_id='$user_id' OR artwork_portfolio.portfolio_owner_id='$user_id')  AND artwork_portfolio.pg_id = 0)";
        //$extraWhere2 = "((artwork_portfolio.portfolio_licensee_id='$user_id' OR artwork_portfolio.portfolio_owner_id='$user_id') AND artwork_portfolio.pg_id > 0)";
        $extraWhere = "((SELECT COUNT(*) FROM artwork WHERE artwork.portfolio_id = artwork_portfolio.portfolio_id AND (artwork.artwork_licenseeID='$user_id' OR artwork.artwork_ownerID='$user_id')) > 0 AND artwork_portfolio.pg_id = 0)";
        $extraWhere2 = "(SELECT COUNT(*) FROM artwork WHERE artwork.portfolio_id = artwork_portfolio.portfolio_id AND (artwork.artwork_licenseeID='$user_id' OR artwork.artwork_ownerID='$user_id')) > 0 AND artwork_portfolio.pg_id > 0 GROUP BY artwork_portfolio.pg_id";
        $group_by = "";
    }

    else if($_POST['page'] == "portfolio_list_full"){
        $joinQuery = "FROM artwork_portfolio LEFT JOIN artwork_portfolio_groups on (artwork_portfolio.pg_id = artwork_portfolio_groups.pg_id) LEFT JOIN user on (artwork_portfolio.portfolio_owner_id = user.user_id) LEFT JOIN artwork_portfolio_lang on (artwork_portfolio_lang.portfolio_id = artwork_portfolio.portfolio_id AND artwork_portfolio_lang.lang_code ='".getLanguage()."') ";
        //$extraWhere = "(artwork_portfolio.portfolio_licensee_id='$user_id' OR artwork_portfolio.portfolio_owner_id='$user_id')";
        $extraWhere = "((SELECT COUNT(*) FROM artwork WHERE artwork.portfolio_id = artwork_portfolio.portfolio_id AND (artwork.artwork_licenseeID='$user_id' OR artwork.artwork_ownerID='$user_id')) > 0)";
        $extraWhere2 = "";
        $group_by = "";
    }
    
    
    
   
    require('ssp.php' );
    
    echo json_encode(SSP::simple( $_POST, $sql_details, $table, $primaryKey, $columns, $joinQuery, $extraWhere, $group_by , $_POST['page']));
    

 
?>