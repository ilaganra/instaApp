    <?php echo $header; ?>
    <!-- Datetime Picker -->
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
        .dataTables_filter {
            display: none;
        }
    </style>
    <!-- For datatables -->
    <script src="<?php  echo $base;?>others/js/jquery.min.js"></script>  
    <script type="text/javascript" language="javascript" src="<?php echo $base;?>others/js/jquery.dataTables.js"></script>
    <script type="text/javascript" language="javascript" src="<?php echo $base;?>others/js/TableTools.min.js"></script>
    <script src="<?php  echo $base;?>others/js/jquery-ui.js"></script>
    
    <script type="text/javascript" src="<?php  echo $base;?>others/js/jquery.simple-dtpicker.js"></script>    
    <script src="<?php  echo $base;?>others/js/jquery.blockUI.js"></script>
    <script type="text/javascript"  charset="utf-8">
        
         $.fn.dataTableExt.afnFiltering.push(

    function (oSettings, aData, iDataIndex) {
        var pattern = /-/g;
        var dFrom = $('#datepickerFrom').val();
        dFrom = dFrom.replace(pattern,'/');
        var dTo =$('#datepickerTo').val();
        dTo = dTo.replace(pattern,'/');
        var myDate = new Date(dFrom);
        var myEpoch = (myDate.getTime() / 1000.0) + (8 * 60 * 60);
        var iMin = myEpoch;
        var myDate = new Date(dTo);
        var myEpoch = (myDate.getTime() / 1000.0) + (8 * 60 * 60);
        var iMax = myEpoch;
        if (iMin == iMax) {
            iMin = iMax = "";
        }
        var iVersion = aData[1] == "-" ? 0 : aData[1]*1;
        if ( iMin == "" && iMax == "" )
        {
            return true;
        }
        else if ( iMin == "" && iVersion < iMax )
        {
            return true;
        }
        else if ( iMin <= iVersion && "" == iMax )
        {
            return true;
        }
        else if ( iMin <= iVersion && iVersion <= iMax )
        {
            return true;
        }
        else{
            return false;
        }
    });
   
    $.fn.dataTableExt.oApi.fnGetColumnData = function (oSettings, iColumn, bUnique, bFiltered, bIgnoreEmpty) {
        // check that we have a column id
        if (typeof iColumn == "undefined") return new Array();

        // by default we only want unique data
        if (typeof bUnique == "undefined") bUnique = true;

        // by default we do want to only look at filtered data
        if (typeof bFiltered == "undefined") bFiltered = true;

        // by default we do not want to include empty values
        if (typeof bIgnoreEmpty == "undefined") bIgnoreEmpty = true;

        // list of rows which we're going to loop through
        var aiRows;

        // use only filtered rows
        if (bFiltered == true) aiRows = oSettings.aiDisplay;
        // use all rows
        else aiRows = oSettings.aiDisplayMaster; // all row numbers
        // set up data array    
        var asResultData = new Array();

        for (var i = 0, c = aiRows.length; i < c; i++) {
            iRow = aiRows[i];
            var aData = this.fnGetData(iRow);
            var sValue = aData[iColumn];

            // ignore empty values?
            if (bIgnoreEmpty == true && sValue.length == 0) continue;

            // ignore unique values?
            else if (bUnique == true && jQuery.inArray(sValue, asResultData) > -1) continue;

            // else push the value onto the result data array
            else asResultData.push(sValue);
        }
        return asResultData;
    };
    $(document).ready(function ($) {
        var users = <?php echo json_encode($userArray); ?> ;//all distinct users
        $('#datepickerFrom,#datepickerTo').appendDtpicker();
        $("#user").autocomplete({// set autocomplete for user input field
            source: users
        });
        var oTable = $('#table').dataTable({
            "bSort": false,
            'aoColumnDefs': [{
                'bVisible': false,
                'aTargets': [1, 2]
            }]
        });
        $('#filter').click(function () {//filter table according to desired  output
            
            oTable.fnFilter("", 2);
            oTable.fnFilter($('#user').val(), 2);
            oTable.fnDraw();
            
        });
        
    }); 
        
 
    </script>
    </head>
    <body>
        <?php echo $navBar; ?>
        <!--/Main Container -->
        <div id="wrap">
            <div class="container">
            <div class="container1">
                
                <h3>Audit Trail</h3>  
                <br/><h5  class="text-primary">Filter Results: </h5>
                <div class="input-group input-group-sm">
                <span class="input-group-addon">Start Date</span>
                <input type="text" class="form-control" id="datepickerFrom" readonly="true" value='' >
                <span class="input-group-addon">End Date</span>
                <input type="text" class="form-control" id="datepickerTo" readonly="true" value=''>
                <span class="input-group-addon">User:</span>
                <input type="text" class="form-control" id="user"  >
                <span class="input-group-btn">
                    <button id="filter" class="btn btn-xs btn-primary">Filter</button>
                </span>
            </div>
            <br/><br/>
            <table cellpadding="0" cellspacing="0" border="0" class="display" id="table">
            <thead>
                <tr>
                    <th>Date</th>
                    <th>Raw Date</th>
                    <th>User</th>
                    <th>Details</th>
                </tr>
            </thead>
            <tbody>
                        <?php
                        $ctr=0;
                        foreach($history->result() as $row)
                        {
                            $content = "";
                            $content = $content."<tr>";
                            $content = $content."<td>".gmdate("M. d, Y  g:i a",$row->dateProcessed)."</td>";
                            $content = $content."<td>$row->dateProcessed</td>";
                            $content = $content."<td>$row->username</td>";
                            if (($row->tagStatus) > 1)
                                $tagType = 'search';
                            else
                                $tagType = 'validation';
                            if (($row->filter) == 'timePosted')
                                $filter = 'posted';
                            else
                                $filter = 'processed';
                            if ($row->amount == 0){
                                $amount = 'P0-100';
                            }
                            else if ($row->amount == 1){
                                $amount = 'P101-500';
                            }
                            else if ($row->amount == 1){
                                $amount = 'P501-1000';
                            }
                            else {
                                $amount = 'greather than 1000';
                            }
                            switch($row->action)
                            {
                                //*****User Mgt 
                                case 'attemptLogin':
                                    $content = $content."<td>User with ip ($row->ipAddr) and username '".$row->processedUsername."' has attempted to login with browser $row->browser version $row->version.</td>";
                                    break;
                                case 'loggedIn':
                                    $content = $content."<td>User '".$row->username."'($row->ipAddr) has logged in with browser $row->browser version $row->version.</td>";
                                    break;
                                case 'loggedOut':
                                    $content = $content."<td>User '".$row->username."' has logged out.</td>";
                                    break;
                                case 'activateUser':
                                    if (empty($row->processedUsername))
                                        $content = $content."<td>User '".$row->username."' has activated user '$row->processedUser'.</td>";
                                    else
                                        $content = $content."<td>User '".$row->username."' has activated user '$row->processedUsername'.</td>";
                                    break;
                                case 'addUser':
                                    $content = $content."<td>User '".$row->username."' has added user '$row->processedUsername'.</td>";
                                    break;
                                case 'deactivateUser':
                                    if (empty($row->username))
                                        $content = $content."<td>User '".$row->processedUsername."' was deactivated due to invalid login attempts.</td>";
                                    else if (empty($row->processedUsername))
                                        $content = $content."<td>User '".$row->username."' has deactivated user '$row->processedUser'.</td>";
                                    else
                                        $content = $content."<td>User '".$row->username."' has deactivated user '$row->processedUsername'.</td>";
                                    break;
                                case 'updateUser':
                                    $content = $content."<td>User '".$row->username."' has updated access rights of user '$row->processedUsername'.</td>";
                                    break;
                                case 'deleteUser':
                                    if (empty($row->processedUsername))
                                        $content = $content."<td>User '".$row->username."' has deleted user '$row->processedUser'.</td>";
                                    else
                                        $content = $content."<td>User '".$row->username."' has deleted user '$row->processedUsername'.</td>";
                                    break;
                                case 'userResetPassword':
                                    $content = $content."<td>User '".$row->username."' has changed his password.</td>";
                                    break;
                                case 'resetPassword':
                                    if (empty($row->processedUsername))
                                        $content = $content."<td>User '".$row->username."' has reset password of user '$row->processedUser'.</td>";
                                    else
                                        $content = $content."<td>User '".$row->username."' has reset password of user '$row->processedUsername'.</td>";
                                    break;
                                //*****Subscriptions
                                case 'disableSubscription':
                                    $content = $content."<td>User '".$row->username."' has disabled tag '$row->tagName'.</td>";
                                    break;
                                case 'enableSubscription':
                                    $content = $content."<td>User '".$row->username."' has enabled tag '$row->tagName' from ".gmdate("M. d, Y  g:i a",$row->sdate)." to ".gmdate("M. d, Y  g:i a",$row->edate).".</td>";
                                    break;
                                case 'deleteSubscription':
                                    $content = $content."<td>User '".$row->username."' has deleted tag '$row->tagName'.</td>";
                                    break;
                                case 'addSubscription':
                                    $content = $content."<td>User '".$row->username."' has added tag '$row->tagName' for $tagType that will be active from ".gmdate("M. d, Y  g:i a",$row->sdate)." to ".gmdate("M. d, Y  g:i a",$row->edate).".</td>";
                                    break;
                                //*****View Entries
                                case 'revoke':
                                    $content = $content."<td>User '".$row->username."' has revoked invalidation on an <a href='$row->entryLink' target='_blank'>entry.</a></td>";
                                    break;
                                case 'saveEntries':
                                    $content = $content."<td>User '".$row->username."' has saved entries under tag '$row->tagName'.</td>";
                                    break;
                                case 'viewEntries':
                                    $content = $content."<td>User '".$row->username."' has viewed entries under tag '$row->tagName' that are $filter from ".gmdate("M. d, Y  g:i a",$row->sdate)." to ".gmdate("M. d, Y  g:i a",$row->edate).".</td>";
                                    break;
                                //*****Validation
                                case 'invalidate':
                                    $content = $content."<td>User '".$row->username."' has invalidated  an <a href='$row->entryLink' target='_blank'>entry.</a></td>";
                                    break;
                                case 'validate':
                                    $content = $content."<td>User '".$row->username."' has validated  an <a href='$row->entryLink' target='_blank'>entry.</a></td>";
                                    break;
                                //*****Report
                                case 'report':
                                    $content = $content."<td>User '".$row->username."' has searched entries for report from ".gmdate("M. d, Y  g:i a",$row->sdate)." to ".gmdate("M. d, Y  g:i a",$row->edate)." with TIN $row->tin and amount $amount.</td>";
                                    break;
                                
                                default:
                                    $content = $content."<td>$row->action</td>";
                                    break;
                            }
                            $content = $content."</tr>";   
                            echo $content;
                        }
                        ?>
                    </tbody>
                    <tfoot>
                        <tr>
                            <th>Date</th>
                            <th></th>
                            <th></th>
                            <th>Details</th>
                        </tr>
                    </tfoot>
                </table>
                <div id="ajaxResult">
                </div></div>
            </div>
        </div>
        <?php echo $footer; ?>    
    <script src="<?php  echo $base;?>others/js/bootstrap.min.js"></script>
    </body>
</html>
