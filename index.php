<table id="example" class="table table-striped table-bordered dt-responsive " cellspacing="0" >
    <thead>
        <tr>
            <!--  you can set column pririty using data-priority tag, lowest number will get highest priority-->
            <th data-priority="1">Product Name</th> 
            <th >Order Number</th>
            <th>Ordered quantity</th>
            <th data-priority="2">price Each</th>
            <th>Order LineNumber</th>
            <th>Action</th>
        </tr>
    </thead>

</table>

<!-- Latest compiled and minified CSS -->
<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.6/css/bootstrap.min.css">

<link rel="stylesheet" href="https://cdn.datatables.net/1.10.12/css/dataTables.bootstrap.min.css" >

<link rel="stylesheet" href="https://cdn.datatables.net/responsive/2.1.0/css/responsive.bootstrap.min.css" >
<link href="jquery.dataTables.yadcf.css" rel="stylesheet" />

<script type="text/javascript" src="https://code.jquery.com/jquery-1.12.3.js"></script>
<script type="text/javascript" src="https://cdn.datatables.net/1.10.12/js/jquery.dataTables.min.js"></script>
<script type="text/javascript" src="https://cdn.datatables.net/1.10.12/js/dataTables.bootstrap.min.js"></script>
<script type="text/javascript" src="https://cdn.datatables.net/responsive/2.1.0/js/dataTables.responsive.min.js"></script>
<script type="text/javascript" src="https://cdn.datatables.net/responsive/2.1.0/js/responsive.bootstrap.min.js"></script>

<script type="text/javascript" src="jquery.dataTables.yadcf.js"></script>

<script>
var columns_short = [   
                        {"db": "orderNumber", "dt": 1, "field": "orderNumber"},
                        {"db": "products.productName as productName", "dt": 0, "field": "productName"},
                        {"db": "quantityOrdered", "dt": 2, "field": "quantityOrdered"},
                        {"db": "priceEach", "dt": 3, "field": "priceEach"},
                        {"db": "orderLineNumber", "dt": 4, "field": "orderLineNumber"},
                        {"db": "orderNumber as action", "dt": 5, "field": "action"}, //column used for showing action list
                        {"db": "orderNumber as action2", "dt": 6, "field": "action2"}, //data used for action column & hidden from users
                    ];

$(document).ready(function() {
    var tableElement = $('#example');
    var exampleTable = tableElement.DataTable( {
        //state save for remembering last visited page using localstorage, we have used localstorage, because if list is very big, it can't store all data to cookies
        "stateSave": true,
        "stateSaveCallback": function (settings, data) {
            localStorage.setItem('DataTables_example_state', JSON.stringify(data))
        },
        "stateLoadCallback": function (settings) {
            return JSON.parse(localStorage.getItem('DataTables_example_state'))
        },
        "processing": true,
        "serverSide": true,
        // If you need to change any columns appearance or need to add any custom html, you can do it using columnDefs
        "columnDefs": [
            
            {
                "targets": 3,
                "render": function (data, type, full, meta) {
                    if (full[3] > 100 ) {
                        return "<b>" + full[3] + "</b>";
                    }
                    else {
                        return full[3];
                    }
                }
            },

            // If you need to show any custom column like action column, you can do it like below column rendering
            {
            "targets": 5,
            "render": function (data, type, full, meta) {
                    return "Action List for ID <b>" + full[6] + "<br/>";
                }
            },

            // If you need to hide any column data, use below code
            {
                "targets": 6,
                "visible": false
            } 

        ],
        "ajax": {
            "type": "POST",
            "url": "dataTable.php",
            "data": {"table": "orderdetails", "primary_key": "orderNumber", "page": "order_details", "columns": columns_short}
        }
    });

    //adding tfoot using js, after loading main table, otherwise yadcf will not work properly
    $("#example").append('<tfoot><tr><th></th><th></th><th></th><th></th><th></th><th></th></tr></tfoot>');

    //individual column filtering using yadcf plugin
    yadcf.init(exampleTable, [
        {column_number: 1, filter_type: "text"},
        {column_number: 2, filter_type: "text"},
      ], 'footer');
});


</script>

<style>
    @media (max-width: 1023px) {
      #example tfoot {
        display: none;
      }
    }
</style>
