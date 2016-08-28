<table id="example" class="table table-striped table-bordered dt-responsive nowrap" cellspacing="0" width="100%">
    <thead>
        <tr>
            <th>First name</th>
            <th>Last name</th>
            <th>Position</th>
            <th>Office</th>
            <th>Email</th>
            <th>Phone</th>
            
        </tr>
    </thead>
 
    <tfoot>
        <tr>
            <th>First name</th>
            <th>Last name</th>
            <th>Position</th>
            <th>Office</th>
            <th>Email</th>
            <th>Phone</th>
            
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
var columns_short = [{"db": "customerNumber", "dt": 0, "field": "customerNumber"},
                        {"db": "customerName", "dt": 1, "field": "customerName"},
                        {"db": "phone", "dt": 2, "field": "phone"},
                        {"db": "addressLine1", "dt": 3, "field": "addressLine1"},
                        {"db": "city", "dt": 4, "field": "city"},
                        {"db": "country", "dt": 5, "field": "country"}
                    ];

$(document).ready(function() {
    $('#example').dataTable( {
        "processing": true,
        "serverSide": true,
        "ajax": {
            "type": "POST",
            "url": "dataTable.php",
            "data": {"table": "customers", "primary_key": 'customerNumber', "page": "artwork_list_short1", "columns": columns_short}
        }
    });
});
</script>