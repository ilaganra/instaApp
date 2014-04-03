    <?php echo $header; ?>
    <link rel="stylesheet" href="<?php  echo $base;?>others/css/jquery-ui.css" />
    <link type="text/css" href="<?php  echo $base;?>others/css/jquery.simple-dtpicker.css" rel="stylesheet" />
    
    <style type="text/css" title="currentStyle">
        @import "<?php echo $base;?>others/css/demo_table.css"; 
        @import "<?php echo $base;?>others/css/TableTools.css";
    </style>
    <!-- Footer -->
    <link href="<?php  echo $base;?>others/css/sticky-footer-navbar.css" rel="stylesheet">
    <style type="text/css" >
        .dataTables_length
        {
            display:none;
        }
        .dataTables_filter {
            display: none;
        }
         #example_length{
            display: none;
        }
    </style>
    <script src="<?php  echo $base;?>others/js/jquery.min.js"></script>  
    <script type="text/javascript" language="javascript" src="<?php echo $base;?>others/js/jquery.dataTables.js"></script>
    
    <script type="text/javascript" language="javascript" src="<?php echo $base;?>others/js/TableTools.min.js"></script>
    <script type="text/javascript" src="<?php  echo $base;?>others/js/jquery.simple-dtpicker.js"></script>    
    <script src="<?php  echo $base;?>others/js/jquery-ui.js"></script>
    <script src="<?php  echo $base;?>others/js/jquery.blockUI.js"></script>
    <script>
    $(document).ready(function ($) {

        $('#datepickerFrom,#datepickerTo').appendDtpicker({
            "closeOnSelected": true
        });
        $("#tinOption").change(function() {
            var tinOption = $("#tinOption").val();	
            if (tinOption === 'wotin')
            {
                $("#tin").val('');
                $("#tin").attr("readonly", true);
            }
            else{
                $("#tin").attr("readonly", false);
            }
        });
        $('#submit').on('click', function () {
            $.blockUI({
                message: '<h3>Processing...</h3>'
            });
            $("#ajaxResult").empty();
            var tinOption = $("#tinOption").val();	
            var dateFrom = $("#datepickerFrom").val();
            var dateTo = $("#datepickerTo").val();
            var tin = $("#tin").val();
            var amount = $("#amountFilter").val();
            if ((tinOption =='wtin')&&(tin.match(/^\s*$/))) {
                $("#ajaxResult").empty();
                $('#ajaxResult').append("<div class='alert alert-warning'>Make sure all fields are not empty.</div>");
            } else {
                if (tinOption =='wotin')
                    tin = 'all';
                var params = {dateFrom:dateFrom,dateTo:dateTo,tin:tin,amount:amount};
                $.ajax({
                    type: 'post',
                    url: "<?php echo $base;?>instagram/processReport/",
                    cache: false,
                    data:params,
                    success: function (data) {
                        var result = $.parseJSON(data);
                        var status = result.status;
                        var description = result.description;
                        $("#ajaxResult").empty();
                        if (status == 'success') {
                            //alert (status + "" + description);
                            $('#ajaxResult').append(description);
                            var oTable = $('#example').dataTable({
                                "sDom": '<"H"TCfr>t<"F"ip>',

                                "oTableTools": {
                                    "sSwfPath": "<?php  echo $base;?>others/media/copy_csv_xls_pdf.swf",
                                    "aButtons": [{
                                        "sExtends": "csv",
                                        "sButtonText": "Save to CSV"

                                    }, {
                                        "sExtends": "print",
                                        "sButtonText": "Print Preview"
                                    }]
                                },
                                "bPaginate": false,
                                "bLengthChange": false,
                                "bFilter": true,
                                "bSort": false,
                                "bInfo": false,
                                "bAutoWidth": false
                            });

                            $('th').unbind('click.DT');
                            oTable.fnLengthChange(100);
                        } else {
                            $('#ajaxResult').append("<div class='alert alert-" + status + "'><center><b>" + description + "</b></center></div>");
                        }

                    },
                    error: function (xhr, textStatus, error) {
                        $('#ajaxResult').append(" <div class='alert alert-danger'><center><b><br/>" + xhr.statusText + "<br/>" + textStatus + "<br/>" + error + "</b></center></div>");
                    }
                });
            }
            $.unblockUI();
        });
    });
    </script>
    </head>
    <body>
        <?php echo $navBar; ?>
        <!--/Main Container -->
        <div id="wrap">
            <div class="container">
                <h3>Web Tool Report</h3>
                <div class="jumbotron"> 
                    <div class="row">           
                         <br/>
                         <div class="input-group input-group-sm">
                            <span class="input-group-addon">Date Posted</span>
                            <span class="input-group-addon">Start Date</span>
                            <input type="text" class="form-control" id="datepickerFrom" readonly="true" value="<?php echo $startDate;?>">
                            <span class="input-group-addon">End Date</span>
                            <input type="text" class="form-control" id="datepickerTo" readonly="true" value="<?php echo $startDate;?>">
                            <span class="input-group-addon">Tin Option</span>
                            <select id="tinOption" class="selectpicker show-tick form-control" >
                                <option selected value="wtin">with TIN</option>
                                <option  value="wotin">without TIN</option>
                            </select>
                            <span class="input-group-addon">TIN</span>
                            <input type="text" id="tin" length="20" class="form-control" placeholder="tax identification number">  
                            <span class="input-group-addon">Amount</span>
                                <select id="amountFilter" class="selectpicker show-tick form-control">
                                    <option selected value="0"  >0-100</option>
                                    <option  value="1" >101-500</option>
                                    <option  value="2" >501-1000</option>
                                    <option  value="3" >1001-Above</option>
                                    <option  value="4" >All</option>
                                </select>
                            <span class="input-group-btn">
                                     <button id="submit" class="btn btn-xs btn-primary">Search</button>
                            </span>                               
                        </div>
                     </div>
                     <br/><br/>
                     <div class="row" id="ajaxResult">
                     </div>                     
                </div>  
            </div>  
        </div>
        <?php echo $footer; ?>
    <script src="<?php  echo $base;?>others/js/bootstrap.min.js"></script>
    </body>
</html>