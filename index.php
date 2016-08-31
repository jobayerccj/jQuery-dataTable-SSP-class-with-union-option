<table id="example" class="table table-striped table-bordered dt-responsive nowrap" cellspacing="0" >
    <thead>
        <tr>
            <th>Order Number</th>
            <th>Product Name</th>
            <th>Ordered quantity</th>
            <th>price Each</th>
            <th>Order LineNumber"</th>
            
            
        </tr>
    </thead>
 
    <tfoot>
        <tr>
           <th>Customer Number</th>
            <th>Name</th>
            <th>Phone</th>
            <th>Address</th>
            <th>City</th>
        </tr>
    </tfoot>
</table>



<!-- Latest compiled and minified CSS -->
<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.6/css/bootstrap.min.css">

<link rel="stylesheet" href="https://cdn.datatables.net/1.10.12/css/dataTables.bootstrap.min.css" >

<link rel="stylesheet" href="https://cdn.datatables.net/responsive/2.1.0/css/responsive.bootstrap.min.css" >


<script type="text/javascript" src="https://code.jquery.com/jquery-1.12.3.js"></script>
<script type="text/javascript" src="https://cdn.datatables.net/1.10.12/js/jquery.dataTables.min.js"></script>
<script type="text/javascript" src="https://cdn.datatables.net/1.10.12/js/dataTables.bootstrap.min.js"></script>
<script type="text/javascript" src="https://cdn.datatables.net/responsive/2.1.0/js/dataTables.responsive.min.js"></script>
<script type="text/javascript" src="https://cdn.datatables.net/responsive/2.1.0/js/responsive.bootstrap.min.js"></script>

<script>
var columns_short = [   //{"db": "id", "dt": 0, "field": "id"},
                        {"db": "orderNumber", "dt": 0, "field": "orderNumber"},
                        {"db": "products.productName as productName", "dt": 1, "field": "productName"},
                        {"db": "quantityOrdered", "dt": 2, "field": "quantityOrdered"},
                        {"db": "priceEach", "dt": 3, "field": "priceEach"},
                        {"db": "orderLineNumber", "dt": 4, "field": "orderLineNumber"}  
                    ];

$(document).ready(function() {
    $('#example').dataTable( {
        "processing": true,
        "serverSide": true,
        "ajax": {
            "type": "POST",
            "url": "dataTable.php",
            "data": {"table": "orderdetails", "primary_key": "id", "page": "order_details", "columns": columns_short}
        }
    });
});
</script>