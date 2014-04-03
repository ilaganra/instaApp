    <?php echo $header; ?>
    <!-- Datetime Picker -->
    <link type="text/css" href="<?php  echo $base;?>others/css/jquery.simple-dtpicker.css" rel="stylesheet" />
    <!-- For modal-->
    <link rel="stylesheet" href="<?php  echo $base;?>others/css/jquery-ui.css" />
    
    <!-- Datatables -->
    <style type="text/css" title="currentStyle">
        @import "<?php echo $base;?>others/css/demo_table.css";               
    </style>
    <!-- Footer -->
    <link href="<?php  echo $base;?>others/css/sticky-footer-navbar.css" rel="stylesheet">
    <!-- Jquery for datatables-->
    <script src="<?php  echo $base;?>others/js/jquery.min.js"></script>  
    <script type="text/javascript" language="javascript" src="<?php echo $base;?>others/js/jquery.dataTables.js"></script>
    <script type="text/javascript" src="<?php  echo $base;?>others/js/jquery.simple-dtpicker.js"></script>    
    <!-- For modal-->
    <script src="<?php  echo $base;?>others/js/jquery-ui.js"></script>
    <script src="<?php  echo $base;?>others/js/jquery.blockUI.js"></script>
    <script>
    $(document).ready(function ($) {
        //Globals
        var toEnableTag = false;
        var toEnableDateStart;
        var toEnableDateEnd;
        var toEnableAction;
        var row = 0;
        var rowPos = 0;
        var rowVal = '';
        var pageBlock = false;
        var Gaction ='';
        var Gcallback = '';
        var Ghashtag =  '';
        var GdateFrom =  '';
        var GdateTo = '';
        var Gid = '';
        var GsubsId = '';
        var Gstatus = '';
        //initialize html elements
        setInterval(function () {
            if (pageBlock == false) location.reload(true);
        }, 60 * 1000);
        $('#datepickerFrom,#datepickerTo').appendDtpicker({
            "futureOnly": true,
            "closeOnSelected": true
        });
        var oTable = $('#table').dataTable({
            "oLanguage": {
                "sSearch": "Search all columns:"
            },
            'aoColumnDefs': [{
                'bVisible': false,
                'aTargets': [0, 1, 5]
            }]
        });
        $("#tagStatus").change(function() {
            var tagStatus = $("#tagStatus").val();	
            //alert(tagStatus);
            $("#maxEntriesDdown").empty();
            if (tagStatus ==='0')
            {
                $("#maxEntriesDdown").attr("disabled", false);
                $("#maxEntriesDdown").append("<option selected value='unli'>Unlimited</option>");   
                $("#maxEntriesDdown").append("<option  value='others'>Others</option>"); 
            }
            else{
                $("#maxEntriesDdown").append("<option selected value='0'>None</option>"); 
                $("#maxEntriesDdown").attr("disabled", true);
                $("#maxValidTxt").val('');
                $("#maxValidTxt").attr("readonly", true);
                
            }
        });  
        $("#maxEntriesDdown").change(function() {
            var maxEntriesDdown = $("#maxEntriesDdown").val();	
            if (maxEntriesDdown === 'unli')
            {
                $("#maxValidTxt").val('');
                $("#maxValidTxt").attr("readonly", true);
            }
            else{
                $("#maxValidTxt").attr("readonly", false);
            }
        });
        //  disable,enable,delete click event
        $('#table tbody').on('click', 'input[id^="btnAction"]', function () {
            $("#ajaxResult").empty();
            var action = $(this).val();
            if (action == "Cancel") {
                toEnableTag = false;
                oTable.fnUpdate(toEnableDateStart, rowPos, 3); // date start
                oTable.fnUpdate(toEnableDateEnd, rowPos, 4); // date end
                oTable.fnUpdate(toEnableAction, rowPos, 10); // action    
                //openDialog(subscribe,rowVal[2],dateFrom,dateTo,"","",status);
            } else if (action == "Confirm") {
                openDialog('confirm', confirmEnableTag, rowVal[2], $('#startDate').val(), $('#endDate').val(), "", "", rowVal[5]);
            } else if (toEnableTag == true) {
                //$('#ajaxResult').append("<div class='alert alert-warning'>Please finish enabling tag.</div>");
                showError("Please finish enabling tag. ");
            } else {
                getRowSelected(this.parentNode.parentNode);
                if (action == "Disable") {
                    openDialog('disable', disableSubscription, rowVal[2], "", "", "", rowVal[1], rowVal[5]);
                } else if (action == "Enable") {
                    enableTag();
                } else {
                    openDialog('delete', deleteSubscription, rowVal[2], "", "", "", "", "");
                }
            }
        });
        //clicked event for subscribe button
        $('#subscribe').click(function () {
            // update the block message
            $("#ajaxResult").empty();
            var hashtag = $("#inputHashTag").val();
            var dateFrom = $("#datepickerFrom").val();
            var dateTo = $("#datepickerTo").val();
            var status = $("#tagStatus").val();
            var maxValidEntries = $("#maxValidTxt").val();
            var maxEntriesDdown = $("#maxEntriesDdown").val();
            //alert(maxEntriesDdown + " "+ maxValidEntries);
            if ((status == "2")||(maxEntriesDdown === "unli"))
                maxValidEntries = "0";
            if ((hashtag.match(/^\s*$/)) || (dateFrom.match(/^\s*$/)) || (dateTo.match(/^\s*$/)|| (maxValidEntries.match(/^\s*$/)))) {
                $("#ajaxResult").empty();
                $('#ajaxResult').append("<div class='alert alert-warning'>Make sure all fields are not empty.</div>");
            }
            else if (hashtag.match(/[^a-zA-Z0-9]+/)){
                $("#ajaxResult").empty();
                $('#ajaxResult').append("<div class='alert alert-warning'>Hashtags must be alphanumeric characters only.</div>");
            }
            else if (maxValidEntries.match(/[^0-9]+/)){
                $("#ajaxResult").empty();
                $('#ajaxResult').append("<div class='alert alert-warning'>Max valid entries per user in a day must be in number.</div>");
            }
            else if ((maxEntriesDdown === "others")&&(maxValidEntries == "0")){
                $("#ajaxResult").empty();
                $('#ajaxResult').append("<div class='alert alert-warning'>Max valid entries per user in a day must be 1 or above.</div>");
            }
            else if (toEnableTag == true) $('#ajaxResult').append("<div class='alert alert-warning'>Please finish enabling tag.</div>");
            else {
                hashtag = 'psr' + (hashtag.replace(/[^a-zA-Z0-9]+/g, '')).toLowerCase(); 
                //alert((hashtag+ " "+ dateFrom+ " "+ dateTo+ " "+  maxValidEntries+ " "+ status));
                openDialog('subscribe', subscribe, hashtag, dateFrom, dateTo, "", maxValidEntries, status);
            }
        });
        //enable tag
        function enableTag() {
            toEnableTag = true;
            toEnableDateStart = rowVal[3];
            toEnableDateEnd = rowVal[4];
            toEnableAction = rowVal[10];

            //alert(toEnableTag + " "+ toEnableDateStart+ " "+ toEnableDateEnd + " "+ toEnableAction);
            oTable.fnUpdate("<input type='text' class='form-control' id='startDate'  value='<?php echo $startDate;?>'>", rowPos, 3); // date start
            oTable.fnUpdate("<input type='text' class='form-control' id='endDate'  value='<?php echo $startDate;?>'>", rowPos, 4); // date end
            oTable.fnUpdate("<input type='button'  class='btn btn-xs btn-info btn-block' id='btnActionEnableTag'  value='Confirm'>\n\
                            <input type='button' class='btn btn-xs btn-primary btn-block' id='btnActionCancelEnableTag'  value='Cancel'>", rowPos, 10); // action
            $('#startDate,#endDate').appendDtpicker({
                "futureOnly": true,
                "closeOnSelected": true
            });
        }
        //get row selected on datatables
        function getRowSelected(row) {
            rowPos = oTable.fnGetPosition(row);
            rowVal = oTable.fnGetData(row);
        }
        //disable tag selected
        function disableSubscription(subsId, hashtag, tagStatus) {
            $("#dialog-confirm").dialog("close");
            //alert(subsId+" "+hashtag+" "+tagStatus);
            blockUI();
            var params = { subsId:subsId, hashtag:hashtag, status:tagStatus };
            $.ajax({
                type: 'post',
                url: "<?php echo $base;?>instagram/disableSubscription",
                data: params,
                cache: false,
                success: function (data) {
                    $("#ajaxResult").empty();
                    var result = $.parseJSON(data);
                    var status = result.status;
                    var description = result.description;
                    var statusDesc = result.statusDesc;
                    if (status == "success") {
                        oTable.fnUpdate("<input type='button'  id='btnAction" + subsId + "' name='" + subsId + "' class='btn btn-xs btn-danger btn-block' value='Enable'/>", rowPos, 10); // button
                        oTable.fnUpdate(parseInt(tagStatus) + 1, rowPos, 5); // status
                        oTable.fnUpdate(statusDesc, rowPos, 6); // status description

                    }
                    //$('#ajaxResult').append("<div class='alert alert-" + status + "'><center><b>" + description + "</b></center></div>");
                    unBlockUI();
                    showError(description);
                },
                error: function (xhr, textStatus, error) {
                    unBlockUI();
                    //$('#ajaxResult').append(" <div class='alert alert-danger'><center><b><br/>" + xhr.statusText + "<br/>" + textStatus + "<br/>" + error + "</b></center></div>");
                    showError(xhr.statusText + "<br/>" + textStatus + "<br/>" + error);
                }
            });
        }
        //continue with enabling tag
        function confirmEnableTag(hashtag, dateFrom, dateTo, tagStatus) {
            //alert (hashtag + " "+dateFrom+ " "+dateTo+ " "+status);
            $("#dialog-confirm").dialog("close");
            //alert(subsId+" "+hashtag+" "+tagStatus);
            blockUI();
            var params = { hashtag:hashtag, startDate:dateFrom, endDate:dateTo,status: tagStatus };
            $.ajax({
                type: 'post',
                url: "<?php echo $base;?>instagram/enableSubscription",
                cache: false,
                data: params,
                success: function (data) {
                    $("#ajaxResult").empty();
                    var result = $.parseJSON(data);
                    var status = result.status;
                    var description = result.description;
                    var statusDesc = result.statusDesc;
                    if (status == "success") {

                        var newDateStart = result.newStartDate;
                        var newDateEnd = result.newEndDate;
                        var newStatusDesc = result.statusDesc;
                        var btnClass = '';
                        var action = '';
                        //var newAction = ;
                        if (!(statusDesc.indexOf("Queue") > -1)) //not in queue
                        {
                            btnClass = 'btn btn-xs btn-primary';
                            action = "Disable";
                            var newSubsId = result.subsId;
                            var newStatus = parseInt(tagStatus) - 1;
                            oTable.fnUpdate(newSubsId, rowPos, 3); // subs id
                            oTable.fnUpdate(newDateStart, rowPos, 3); // start date
                            oTable.fnUpdate(newDateEnd, rowPos, 4); // end date
                            oTable.fnUpdate(newStatus, rowPos, 5); // status id
                            oTable.fnUpdate(newStatusDesc, rowPos, 6); // status description
                            oTable.fnUpdate("<input type='button'  id='btnAction" + rowVal[1] + "' name='" + rowVal[1] + "' class='" + btnClass + " btn-block' value='" + action + "'/>", rowPos, 10); // button
                        } else {
                            btnClass = 'btn btn-xs btn-warning';
                            action = "Delete";
                            oTable.fnUpdate(newDateStart, rowPos, 3); // start date
                            oTable.fnUpdate(newDateEnd, rowPos, 4); // end date
                            oTable.fnUpdate(rowVal[6] + ' (Queue)', rowPos, 6); // status description
                            oTable.fnUpdate("<input type='button'  id='btnAction" + rowVal[1] + "' name='" + rowVal[1] + "' class='" + btnClass + " btn-block' value='" + action + "'/>", rowPos, 10); // button
                        }
                        toEnableTag = false;
                    }
                    //$('#aja
                    //xResult').append("<div class='alert alert-" + status + "'><center><b>" + description + "</b></center></div>");
                    unBlockUI();
                    showError(description);
                },
                error: function (xhr, textStatus, error) {
                    unBlockUI();
                    //$('#ajaxResult').append(" <div class='alert alert-danger'><center><b><br/>" + xhr.statusText + "<br/>" + textStatus + "<br/>" + error + "</b></center></div>");
                    showError(xhr.statusText + "<br/>" + textStatus + "<br/>" + error);
                }
            });
        }
        //delete selected tag
        function deleteSubscription(hashtag) {
            $("#dialog-confirm").dialog("close");
            blockUI();
            var params = { hashtag:hashtag };
            $.ajax({
                type: 'post',
                url: "<?php echo $base;?>instagram/deleteSubscription",
                cache: false,
                data: params,
                success: function (data) {
                    $("#ajaxResult").empty();
                    var result = $.parseJSON(data);
                    var status = result.status;
                    var description = result.description;
                    if (status == "success") {
                        //$('#ajaxResult').append(" <div class='alert alert-success'><center><b>" + description + "</b></center></div>");
                        oTable.fnDeleteRow(rowPos);
                    }
                    /**if (status == "danger") {
                        $('#ajaxResult').append("<div class='alert alert-danger'><center><b>" + description + "</b></center></div>");
                    }*/
                    unBlockUI();
                    if ((status == "danger")||(status == "success")) {
                        showError(description);
                    }
                },
                error: function (xhr, textStatus, error) {
                    unBlockUI();
                    //$('#ajaxResult').append(" <div class='alert alert-danger'><center><b><br/>" + xhr.statusText + "<br/>" + textStatus + "<br/>" + error + "</b></center></div>");
                    showError(xhr.statusText + "<br/>" + textStatus + "<br/>" + error);
                }
            });
        }
        //subscribe to the tag
        function subscribe(hashtag, dateFrom, dateTo, maxValidEntries, status) {
            var tagStatus = status;
            $("#dialog-confirm").dialog("close");
            blockUI();
            var params = { tag:hashtag ,dateFrom:dateFrom,dateTo:dateTo,status:status,maxValidEntries: maxValidEntries};
            $.ajax({
                type: 'post',
                url: '<?php echo $base;?>instagram/subscribe',
                cache: false,
                data: params,
                success: function (data) {
                    unBlockUI();
                    $("#ajaxResult").empty();
                    var result = $.parseJSON(data);
                    var status = result.status;
                    var description = result.description;
                    if (status == "warning") {
                        $('#ajaxResult').append(" <div class='alert alert-warning'><center><b>" + description + "</b></center></div>");
                    }
                    if (status == "success") {
                        var tagData = $.parseJSON(result.tagData);
                        var btnClass = "";
                        var action = "";
                        if (!((tagData.statusDesc).indexOf("Queue") > -1)) {
                            btnClass = 'btn btn-xs btn-primary btn-block';
                            action = "Disable";
                        } else {
                            tagStatus = tagData.status;
                            btnClass = 'btn btn-xs btn-warning btn-block';
                            action = "Delete";
                        }
                        $( "#inputHashTag").val('');
                        $('#ajaxResult').append("<div class='alert alert-success'><center><b>" + description + "</b></center></div>");
                        // alert (""+ " "+tagData.subscriptionId+ " "+hashtag+ " "+tagData.startDateTime+ " "+tagData.endDateTime+ " "+status+ " "+tagData.statusDesc+ " "+tagData.createdOn+ " "+tagData.createdBy + " "+btnClass+action);
                        $('#table').dataTable().fnAddData(["", tagData.subscriptionId, hashtag, tagData.startDateTime, tagData.endDateTime, tagStatus, tagData.statusDesc, tagData.maxValidEntries,tagData.createdOn, tagData.createdBy, "<input type='button'  id='btnAction" + tagData.subscriptionId + "' name='" + tagData.subscriptionId + "' class='" + btnClass + "' value='" + action + "'/>"]);
                    }
                    if (status == "danger") {
                        $('#ajaxResult').append("<div class='alert alert-danger'><center><b>" + description + "</b></center></div>");
                    }
                }
            });
        }
        //unblock page
        function unBlockUI() {
            $.unblockUI();
            pageBlock = false;
        }
        //block page
        function blockUI() {
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
            pageBlock = true;
        }
          
        $('#yes').on('click', function(){
            $('#tableModal').modal('hide');
            if (Gaction == 'subscribe') {
                Gcallback(Ghashtag, GdateFrom, GdateTo, GsubsId,Gstatus);
            } else if (Gaction == 'delete') {
                Gcallback(Ghashtag);
            } else if (Gaction == 'disable') {
            Gcallback(GsubsId, Ghashtag, Gstatus);
            } else if (Gaction == 'confirm') {
            Gcallback(Ghashtag, GdateFrom, GdateTo, Gstatus);
            } else {}
            Gaction = '';
            Gcallback = '';
            Ghashtag = '';
            GdateFrom = '';
            GdateTo = '';
            Gid = '';
            GsubsId = '';
            Gstatus = '';
            
        }); 
        function showError(description){
            $('#errorModal .modal-body p').html(description);
            $("#errorModal").modal({ backdrop: true,keyboard: true});   
        }
        //open dialog
        function openDialog(action, callback, hashtag, dateFrom, dateTo, id, subsId, status) {
            Gaction = action;
            Gcallback = callback;
            Ghashtag = hashtag;
            GdateFrom = dateFrom;
            GdateTo = dateTo;
            Gid = id;
            GsubsId = subsId;
            Gstatus = status;
            //alert (Gaction    + " " +Ghashtag  + " " +GdateFrom  + " " +GdateTo  + " " +Gid  + " " +GsubsId + " " +Gstatus);
            if (action =='subscribe')
                $('#tableModal .modal-body p').html("Are you sure you want to subscribe to this tag?");
            else if (action =='delete')
                $('#tableModal .modal-body p').html("Are you sure you want to delete this tag?");
            else if (action =='disable')
                $('#tableModal .modal-body p').html("Are you sure you want to disable this tag?");
            else
                $('#tableModal .modal-body p').html("Are you sure you want to enable this tag?");
            $("#tableModal").modal({ backdrop: 'static',keyboard: false});
        }
    });
    </script>
    </head>
    <body>
        <?php echo $navBar; ?>
        <!--/Main Container -->
        <div id="wrap">
            <div class="container">
                <h3>View Tag Subscriptions</h3> 
                <div class="jumbotron"> 
                    <div class="row">           
                        <br/>
                        <h5  class="text-primary">Add new subscription: </h5>
                        <div class="input-group input-group-sm">
                            <span class="input-group-addon"  title="Hashtags must start with prefix PSR">#PSR</span>
                            <input type="text" id="inputHashTag" maxlength="47" class="form-control" placeholder="hashtag without psr">  
                            <span class="input-group-addon" title="Date where the hashtag will be enabled">Start Date</span>
                            <input type="text" class="form-control" id="datepickerFrom" readonly="true" value="<?php echo $startDate;?>">
                            <span class="input-group-addon" title="Date where the hashtag will be disabled">End Date</span>
                            <input type="text" class="form-control" id="datepickerTo" readonly="true" value="<?php echo $startDate;?>">
                            <span class="input-group-addon" title="Entries under for validation will be validated on the format">Type</span>
                            <select id="tagStatus" class="selectpicker show-tick form-control">
                                <option selected value="0">For Validation</option>
                                <option value="2">For Search</option>
                            </select>
                            <span class="input-group-addon" title="Required max number of valid entries per user in a day">Max Valid Entries</span>
                            <select id="maxEntriesDdown" class="selectpicker show-tick form-control" >
                                <option selected value="unli">Unlimited</option>
                                <option  value="others">Others</option>
                            </select>
                            <span class="input-group-addon" title="Max number of valid entries" >No: </span>
                            <input type="text" class="form-control" id="maxValidTxt"  readonly="true" length="5" style="width:50px;" maxlength='3'/> 
                            <span class="input-group-btn">
                                <button id="subscribe" class="btn btn-xs btn-primary">Create</button>                       
                            </span>
                        </div>
                     </div>
                     <div class="row" id="ajaxResult">
                     </div>
                        <br/>
                        <table cellpadding="0" cellspacing="0" border="0" class="display" id="table">
                        <thead>
                                <tr>
                                        <th>id</th>
                                        <th>subscription id</th>
                                        <th>Tag Name</th>
                                        <th>Date Start    </th>
                                        <th>Date End     </th>
                                        <th>stat id</th>
                                        <th>Status</th>
                                        <th>Max Valid Entries</th>
                                        <th>Date Created</th>
                                        <th>Created By</th> 
                                        <th>Action</th> 
                                </tr>
                        </thead>
                        <tbody>
                         <?php
                                $ctr=0;
                                foreach($subscriptions as $row)
                                {
                                        $status = $subscriptionStatus[$row->status]->description;
                                        //subscription is queue if inactive AND (sdate >= datetoday OR round sdate == round datetoday)
                                        if ((($row->status  % 2) != 0)&&(($row->startDateTime  >= $dateToday)
                                           ||((floor($row->startDateTime/3600) * 3600)==(floor($dateToday/3600) * 3600)))){
                                            $status = $status." (Queue)";
                                            $action = "Delete";
                                            $btnClass = "btn btn-xs btn-warning";
                                        }
                                        else if(($row->status  % 2) == 0){ //active
                                            $action = "Disable";
                                            $btnClass = "btn btn-xs btn-primary btn-block";
                                        }
                                        else{//inactive
                                            $action = "Enable";
                                            $btnClass = "btn btn-xs btn-danger btn-block";
                                        }
                                        
                                        $content = "";
                                        $content = $content."<tr>";
                                        $content = $content."<td>".$row->id."</td>";
                                        $content = $content."<td>".$row->subscriptionId."</td>";
                                        $content = $content."<td>".$row->tagName."</td>";
                                        $content = $content."<td>".gmdate("M. d, Y  g:i a",$row->startDateTime)."</td>";
                                        $content = $content."<td>".gmdate("M. d, Y  g:i a",$row->endDateTime)."</td>";
                                        $content = $content."<td>".$row->status."</td>";
                                        $content = $content."<td>".$status."</td>";
                                        if ($row->maxValidEntries == 0)
                                            $content = $content."<td>None</td>";
                                        else
                                            $content = $content."<td>".$row->maxValidEntries."</td>";
                                        $content = $content."<td>".gmdate("M. d, Y  g:i a",$row->createdOn)."</td>";
                                        $content = $content."<td>".$row->createdBy."</td>";                   
                                        $content = $content."<td><input type='button' id='btnAction$row->id'  class='$btnClass btn-block' value='$action'/></td>";
                                        $content = $content."</tr>";
                                         echo $content;
                                        $ctr++;
                                }
                        ?>
                        </tbody>
                        <tfoot>
                                <tr>    
                                        <th></th>
                                        <th></th>
                                        <th></th>
                                        <th></th>
                                        <th></th>
                                        <th></th>
                                        <th></th>
                                        <th></th>
                                        <th></th>
                                        <th></th>
                                        <th></th>
                                </tr>
                        </tfoot>
                </table>
                </div>  
            </div>  
        </div>
        <!-- /Modal for validation -->
        <div class="modal fade" id="tableModal" tabindex="-1" role="dialog" aria-labelledby="tableLabel" aria-hidden="true">
          <div class="modal-dialog  modal-vertical-centered">
            <div class="modal-content">
              <div class="modal-body">
                <p class="text-center">Are you sure you want to do this?</p>
              </div>
              <div class="modal-footer">
                   <button type="button" class="btn btn-success" id="yes">Yes</button>    
                    <button type="button" class="btn btn-default" data-dismiss="modal">No</button>   
              </div>
            </div><!-- /.modal-content -->
          </div><!-- /.modal-dialog -->
        </div><!-- /.modal -->
        <!-- /Modal for Error Showing -->
        <div class="modal fade" id="errorModal" tabindex="-1" role="dialog" aria-labelledby="tableLabel" aria-hidden="true">
          <div class="modal-dialog  modal-vertical-centered">
            <div class="modal-content">
              <div class="modal-body">
                <p class="text-center">Are you sure you want to do this?</p>
              </div>
              <div class="modal-footer">
                   <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>   
              </div>
            </div><!-- /.modal-content -->
          </div><!-- /.modal-dialog -->
        </div><!-- /.modal -->
        <?php echo $footer; ?>
    <script src="<?php  echo $base;?>others/js/bootstrap.min.js"></script>
    </body>
</html>
