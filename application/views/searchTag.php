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
        code {
            font-size: 80%;
        }
        .container1{
            max-width: 1700px ;
        }
    </style>
    <script src="<?php  echo $base;?>others/js/jquery.min.js"></script>  
    <script type="text/javascript" language="javascript" src="<?php echo $base;?>others/js/jquery.dataTables.js"></script>
    <script type="text/javascript" language="javascript" src="<?php echo $base;?>others/js/TableTools.min.js"></script>
    <script type="text/javascript" src="<?php  echo $base;?>others/js/jquery.simple-dtpicker.js"></script>    
    <script src="<?php  echo $base;?>others/js/jquery-ui.js"></script>
    <script src="<?php  echo $base;?>others/js/jquery.blockUI.js"></script>
    <script type="text/javascript" src="<?php  echo $base;?>others/js/ZeroClipboard.min.js"></script>
    <script>
        $(document).ready(function($) {
           var oTable;
           var clip;
           var ongoingCopy = false;
           var rowPos;
           var rowVal;
           var tagEntryStatus = $.parseJSON(JSON.stringify(<?php print_r( $tagEntryStatus);?>));
           
           $("#tagStatus").change(function() {
                var tagStatus = $("#tagStatus").val();	
                $("#entryStatus").empty();
                if (tagStatus !='base')
                {
                    $.each(tagEntryStatus, function(index, value) {
                       if (!((tagStatus < 2)&&(value.status== 3)))
                       {
                            $("#entryStatus").append("<option selected value="+ value.status+ ">" + value.description + "</option>");
                            changeDateFilter(value.status);   
                       }
                    });
                    $("#entryStatus").append("<option  value='all'>All</option>");
                }
                else{
                    $("#entryStatus").append("<option selected value='base'>Choose a tag first</option>");
                }
            }); 
            $("#entryStatus").change(function() {
                var entryStatus = $("#entryStatus").val();
                changeDateFilter(entryStatus);
            });
            $('#datepickerFrom').appendDtpicker();
            $('#datepickerTo').appendDtpicker();
            
            $(document).on('click', 'a[id^="revoke"]',function(){
                if (ongoingCopy == true){
                    $('#ajaxResult').empty();
                    $('#ajaxResult').append(" <div class='alert alert-success'><center><b>Please close opened remarks.</b></center></div>");
                }
                else{
                    getRowSelected(this.parentNode.parentNode);
                    $('#revoke .modal-body').html("<center><p>Are you sure you want to revoke the invalidation of this entry?</p><p><b>Account: </b>"+rowVal[1]+"<b> Tin: </b>"+rowVal[11]+"<b> OrNo: </b>"+rowVal[12]+"<b> Amount: </b>"+rowVal[13]+"</p></center>");
                    $("#revoke").modal({ backdrop: 'static',keyboard: false});   
                }
            });
            $('#yesRevoke').on('click', function(){
                $('#revoke').modal('hide');
                $.blockUI({ message: '<h3>Processing...</h3>' });
                var params = { id:rowVal[0]};
                $.ajax({
                    type: 'post',
                    url: '<?php echo $base;?>instagram/revoke',
                    cache: false,
                    data: params,
                    success: function (data) {
                        $.unblockUI();
                        $("#ajaxResult").empty();
                        if (data == '0'){
                            oTable.fnDeleteRow(rowPos);
                            $('#revokeResult .modal-body').html("Successfully revoked invalidation on entry! Please see the entry in validation page.");
                            $("#revokeResult").modal({ backdrop: 'static',keyboard: false});      
                        }
                        else{
                            $('#revokeResult .modal-body').html("Unsuccessfully revoked invalidation on entry. Please try again later.");
                            $("#revokeResult").modal({ backdrop: 'static',keyboard: false});  
                        }  
                    },
                    error: function(xhr, textStatus, error){
                        $.unblockUI();
                        $('#revokeResult .modal-body').html("Unsuccessfully revoked invalidation on entry. Please try again later.");
                        $("#revokeResult").modal({ backdrop: 'static',keyboard: false}); 
                    }
                });                
            });
            $('#saveResults').on('click', function(){
                $.blockUI({ message: '<h3>Processing...</h3>' });
                var cells = '';
                var captions = [];
                var tagName = $("#tagStatus option:selected").text();
                var myFilteredRows = oTable._('tr', {"filter":"applied"});
                
                for(var i=0;i<myFilteredRows.length;i++)
                {
                    if (i != myFilteredRows.length-1)
                        cells = cells + myFilteredRows[i][0] + "-";
                    else
                        cells = cells + myFilteredRows[i][0];                   
                }
                var url = "<?php echo $base;?>instagram/saveSearchedEntries";
                var params = {ids:cells, tagName:tagName};
                $.ajax({
                        type:'post',
                        url: url, 
                        cache: false,
                        data:params,
                        success: function (data) {
                            $.unblockUI();
                            var result = $.parseJSON(data);
                            var status = result.status;
                            var description = result.description;      
                            if (status == "warning")
                            {
                                $('#saveResultsDiv').append(" <div class='alert alert-warning'><center><b>"+description+"</b></center></div>");
                            }
                            if (status == "success")
                            {
                                $('#saveResultsDiv').append(" <div class='alert alert-success'><center><b>"+description+"</b></center></div>");
                            }
                       },
                        error: function(xhr, textStatus, error){
                            $.unblockUI();
                            $('#saveResultsDiv').append(" <div class='alert alert-danger'><center><b>"+url+"<br/>"+xhr.statusText+"<br/>"+textStatus+"<br/>"+error+"</b></center></div>");

                        }
                });
                var div = document.getElementById('addtlButtons');
                div.style.display = 'none';
                $('#searchResult').empty();
                
            });
            $('#search').on('click', function(){
                var div = document.getElementById('addtlButtons');
                div.style.display = 'none';
                var tagName = $("#tagStatus option:selected").text();
                var tagStatus = $("#tagStatus").val();
                var entryStatus = $("#entryStatus").val();
                var dateFrom = $( "#datepickerFrom" ).val();
                var dateTo = $( "#datepickerTo" ).val();
                var dateFilter = $( "#dateFilter" ).val();
                if (tagStatus === "base")
                {
                    $("#searchResult").empty();
                    $('#ajaxResult').empty();
                    $('#searchResult').append(" <div class='alert alert-warning'><center><b>Please select a tag first. </b></center></div>");
                }
                else if (ongoingCopy == true){
                    $('#ajaxResult').empty();
                    $('#ajaxResult').append(" <div class='alert alert-success'><center><b>Please close opened remarks.</b></center></div>");
                }
                else{
                    $(this).button('loading'); 
                    $.blockUI({
                        css: {
                            border: 'none',
                            padding: '15px',
                            backgroundColor: '#000',
                            '-webkit-border-radius': '10px',
                            '-moz-border-radius': '10px',
                            opacity: .5,
                            color: '#fff'
                        }
                    });
                    $("#searchResult").empty();
                    $('#saveResultsDiv').empty();
                    var params = {tagName: tagName,tagStatus:tagStatus,dateFrom:dateFrom,dateTo:dateTo,dateFilter:dateFilter,entryStatus:entryStatus};
                    $.ajax({
                        type: 'post',
                        url: "<?php echo $base;?>instagram/search", 
                        cache: false,
                        data:params,
                        success: function (data) {
                            var result = $.parseJSON(data);
                            var status = result.status;
                            var description = result.description;      
                            
                            if (status == "warning")
                            {
                                $('#searchResult').append(" <div class='alert alert-warning'><center><b>"+description+"</b></center></div>");
                            }
                            if (status == "empty")
                            {
                                $('#searchResult').append(" <div class='alert alert-success'><center><b>"+description+"</b></center></div>");
                            }
                            if (status == "success")
                            {
                                
                                $('#searchResult').append(description);
                                var hideColumns = [0,5,6,9];
                                var printColumns= [0,5,6,9];
                                switch(entryStatus) {
                                    case '0':
                                        hideColumns = [0,4,5,6,7,9,10,14,16];
                                        printColumns= [1,2,3,4,7,8,11,12,13,15];
                                        break;
                                    case '1':
                                        hideColumns = [0,5,6,9,16];
                                        printColumns= [1,2,3,4,7,8,16,11,12,13,14,15];
                                        break;
                                    case '2':
                                        hideColumns = [0,5,6,9,11,12,13,14,16];
                                        printColumns= [1,2,3,4,7,8,16,15];
                                        break;
                                    case '3':
                                        hideColumns = [0,4,5,6,7,9,10,11,12,13,14,16];
                                        printColumns= [1,2,3,15];
                                        break;  
                                    case 'all':
                                        hideColumns = [0,5,6,7,9,10,11,12,13,14,16];
                                        printColumns= [1,2,3,4,8,15];
                                        break; 
                                }
                                oTable = $('#example').dataTable( {
                                    "sDom": 'T<"clear">lfrtip',
                                    "oLanguage": {
                                        "sSearch": "Search all columns:"

                                    },
                                    "bDestroy": true,
                                    "oTableTools": {
                                        "sSwfPath": "<?php  echo $base;?>others/media/copy_csv_xls_pdf.swf",
                                        "aButtons": [ 
                                            {
                                            "sExtends":    "csv",
                                            "sButtonText": "Save to CSV",
                                            "mColumns":  printColumns
                                            },
                                            {
                                            "sExtends":    "print",
                                            "sButtonText": "Print Preview",
                                            "mColumns": printColumns
                                            }
                                        ]
                                    },
                                    'aoColumnDefs':[
                                           { 'bVisible': false, 'aTargets': hideColumns }
                                     ]
                                });
                                
                            }
                        },
                        complete: function (data) {
                            
                                setTimeout(function(){
                                    $.unblockUI();
                                    $('#search').button('reset');
                                }, 10);

                        }
                    });
                    
                }
                
            });
            $(document).on('click', 'button[id^="show"]',function(){
                //alert('showw');
                if (ongoingCopy == true){
                     $('#ajaxResult').empty();
                    $('#ajaxResult').append(" <div class='alert alert-success'><center><b>Please close opened remarks.</b></center></div>");
                }
                else{
                    ongoingCopy = true;
                    getRowSelected(this.parentNode.parentNode);
                    oTable.fnUpdate("<textarea id='reasonsTxt' class='form-control' rows='3'> "+rowVal[16]+"</textarea>"+
                                   " <button id='copy"+rowVal[0]+"' class='btn btn-xs btn-success btn-block' data-clipboard-text='"+rowVal[16]+"'>Copy</button>"+
                                   " <a href='#' id='hide"+rowVal[0]+"'>Hide</a>", rowPos, 10);
                            clip = new ZeroClipboard( document.getElementById("copy"+rowVal[0]), {
                                    moviePath: "<?php  echo $base;?>others/media/ZeroClipboard.swf"
                                });
                               
                    
                                clip.on( "load", function(client) {
                                    
                                    client.on( "complete", function(client, args) {
                                        
                                             $('#ajaxResult').empty();
                                            $('#ajaxResult').append(" <div class='alert alert-success'><center><b>Text copied</b></center></div>");
                 
                                    });
                                });
                }
            });
            $(document).on('click', 'a[id^="hide"]',function(){
                ongoingCopy = false;
                 $('#ajaxResult').empty();
                oTable.fnUpdate("<button id='show$row->id'  class='btn btn-xs btn-primary'   >Show</button>", rowPos, 10);
               
            });
            function changeDateFilter(statusFilter){
                $("#dateFilter").empty();
                if(((statusFilter == 0)||(statusFilter == 3)))
                {
                    $("#dateFilter").append("<option selected value='timePosted'>Date Posted</option>");   
                }
                else{
                    $("#dateFilter").append("<option selected value='timePosted'>Date Posted</option>");  
                    $("#dateFilter").append("<option  value='timeProcessed'>Date Processed</option>");  
                }
            }
            function getRowSelected(row)
            {
                rowPos = oTable.fnGetPosition( row );
                rowVal = oTable.fnGetData( row ); 
            }

        });
    </script>
    </head>
    <body>
        <?php echo $navBar; ?>
        <!--/Main Container -->
        <div id="wrap">
            <div class="container">
                <div class="container1">
                <h3>View Entries</h3> 
                <div class="row">
                    <div class="input-group input-group-sm">
                        <span class="input-group-addon">Tag</span>
                        <select id="tagStatus" class="selectpicker show-tick form-control">
                            <option selected value="base" >Select a tag subscription</option>
                            <?php 
                            foreach($subscriptions as $row)
                            {
                                echo "<option value='$row->status'>$row->tagName</option>";
                            }
                            echo "<option value='All'>All</option>";
                            ?>
                        </select>
                        <span class="input-group-addon"> Entry Status </span>
                        <select id="entryStatus" class="selectpicker show-tick form-control">
                            <option selected>Choose a tag first</option>
                        </select>
                        <span class="input-group-addon"></span>
                        <span class="input-group-addon">Date Filter</span>
                        <select id="dateFilter" class="selectpicker show-tick form-control">
                            <option  value="timeProcessed" >Date Processed</option>
                            <option selected value="timePosted" >Date Posted</option>
                        </select>
                        <span class="input-group-addon">Start Date</span>
                        <input type="text" class="form-control" id="datepickerFrom" readonly="true" value="<?php echo $startDate;?>"/>
                        <span class="input-group-addon">End Date</span>
                        <input type="text" class="form-control" id="datepickerTo" readonly="true" value="<?php echo $endDate;?>"/>
                        <span class="input-group-btn">
                            <button id="search" class="btn btn-xs btn-primary" >Search</button>
                        </span>      
                    </div>
                </div>
                <div id="ajaxResult">
                </div>
                <br/><br/><br/><br/>
                <div id="searchResult">
                </div>
                <div id="addtlButtons"  style="display:none">
                    <br/><br/>
                    <button id="saveResults" class="btn btn-primary btn-lg">Save Results For Validation</button>
                </div>
                <br/><br/><br/><br/>
                <div id="saveResultsDiv">
                </div></div>
                <div class="modal fade" id="revoke" tabindex="-1" role="dialog" aria-labelledby="tableLabel" aria-hidden="true">
                    <div class="modal-dialog  modal-vertical-centered">
                      <div class="modal-content">
                        <div class="modal-body">
                          <p class="text-center">Are you sure you want to revoke the invalidation of entry?</p>
                        </div>
                        <div class="modal-footer">
                             <button type="button" class="btn btn-success" id="yesRevoke">Yes</button>    
                              <button type="button" class="btn btn-default" data-dismiss="modal">No</button>   
                        </div>
                      </div><!-- /.modal-content -->
                    </div><!-- /.modal-dialog -->
                  </div><!-- /.modal -->
                    <div class="modal fade" id="revokeResult" tabindex="-1" role="dialog" aria-labelledby="tableLabel" aria-hidden="true">
                    <div class="modal-dialog  modal-vertical-centered">
                      <div class="modal-content">
                        <div class="modal-body">
                          <p class="text-center">Are you sure you want to revoke the invalidation of entry?</p>
                        </div>
                        <div class="modal-footer">
                             <button type="button" class="btn btn-default" data-dismiss="modal">Ok</button>   
                        </div>
                      </div><!-- /.modal-content -->
                    </div><!-- /.modal-dialog -->
                  </div><!-- /.modal -->
            </div>
        </div>
        <?php echo $footer; ?>
    <script src="<?php  echo $base;?>others/js/bootstrap.min.js"></script>
    </body>
</html>