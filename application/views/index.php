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
    <script>
    $(document).ready(function ($) {
        var div = document.getElementById('pwdDiv');
        var oTable = $('#table').dataTable({
            "bSort": false,
            'aoColumnDefs': [{
                'bVisible': false,
                'aTargets': [1, 2]
            }]
        });
        $('#changePwd').on('click', function () {
            $('#ajaxResult').empty();
            $('#changePwd').hide();
            div.style.display = 'block';
            $('#ajaxResult').append("<div class='alert alert-info'><center><b>You will be logged out when update is successful.</b></center></div>");
            
        });
        $('#confirm').on('click', function () {
            $('#ajaxResult').empty();
            var oldPwd = encodeURIComponent(($('#oldPwd').val()));//.replace(/\s+/g, '');
            var newPwd = encodeURIComponent(($('#newPwd').val()));//.replace(/\s+/g, '');
            var confNewPwd = ($('#confNewPwd').val()).replace(/\s+/g, '');
            //alert(oldPwd + confNewPwd + newPwd );
            if (newPwd != confNewPwd) {
                $('#ajaxResult').append("<div class='alert alert-danger'><center><b>Your new password and confirmation password do not match.</b></center></div>");
                $('#newPwd').val('');
                $('#confNewPwd').val('');
            } else if (newPwd == oldPwd) {
                $('#ajaxResult').append("<div class='alert alert-danger'><center><b>New password must be different with old password.</b></center></div>");
            } else if (newPwd.length < 8) {
                $('#ajaxResult').append("<div class='alert alert-danger'><center><b>New password length must be at least 8 characters.</b></center></div>");
            } else if (validateStr(newPwd) == false) {
                $('#ajaxResult').append("<div class='alert alert-danger'><center><b>Username and Password must not contain illegal characters.</b></center></div>");
            } else {
                $.blockUI({ css: { 
                        border: 'none', 
                        padding: '15px', 
                        backgroundColor: '#000', 
                        '-webkit-border-radius': '10px', 
                        '-moz-border-radius': '10px', 
                        opacity: .5, 
                        color: '#fff' 
                    } });  
                var params = { oldPwd:oldPwd, newPwd:newPwd };
                $.ajax({
                    type: 'post',
                    url: "<?php echo $base;?>index.php/users/userResetPassword",
                    data: params,
                    cache: false,
                    success: function (data) {
                        var result = $.parseJSON(data);
                        var status = result.status;
                        var description = result.description;
                        if (status == 'success') {
                            div.style.display = 'none';
                            window.location.replace("<?php echo $base;?>index.php/users/destroy");
                        }
                        $('#ajaxResult').append("<div class='alert alert-" + status + "'><center><b>" + description + "</b></center></div>");
                        
                    },
                    error: function (xhr, textStatus, error) {
                        $('#ajaxResult').append(" <div class='alert alert-danger'><center><b><br/>" + xhr.statusText + "<br/>" + textStatus + "<br/>" + error + "</b></center></div>");
                    }
                });
                $.unblockUI();
            }
        });
        $('#cancel').on('click', function () {
            $('#ajaxResult').empty();
            $('#changePwd').show();
            div.style.display = 'none';
        });

        function validateStr(str) {
            if (/^[a-zA-Z0-9-.-_ ]*$/.test(str) == false) {
                return false;
            }
            return true;
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
                <div class="jumbotron">      
                    <center>
                    <h3>Hello <?php echo $username;?>!</h3>
                    <p>First Name: <?php echo $fname;?></p>
                    <p>Last Name: <?php echo $lname;?></p>
                    <p>Email Address: <?php echo $eadd;?></p>
                    <p> <button class="btn btn-xs btn-primary" id="changePwd">Change Password</button></p>
                    <div class="row" id="pwdDiv" style="display:none">
                        <div class='input-group input-group-sm'>
                            <span class='input-group-addon'>Old Password</span>
                            <input type='password' id='oldPwd' length='20' class='form-control' placeholder=''/>  
                            <span class='input-group-addon'>New Password</span>
                            <input type='password' id='newPwd' length='20' class='form-control' placeholder=''/>  
                            <span class='input-group-addon'>Confirm New Password</span>
                            <input type='password' id='confNewPwd' length='20' class='form-control' placeholder=''/>  
                            <span class="input-group-btn">
                                     <button id="confirm" class="btn btn-xs btn-primary">Change</button>
                            </span>  
                            <span class="input-group-btn">
                                     <button id="cancel" class="btn btn-xs btn-danger">Cancel</button>
                            </span>  
                        </div>
                    </div>  
                    <div class="row" id="ajaxResult">
                    </div>  
                    </center>
                    <div class='table'>
                    <br/><br/><h5  class="text-primary">List of Transactions</h5>   
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
                                case 'loggedIn':
                                    $content = $content."<td>You ($row->ipAddr) have   logged in.</td>";
                                    break;
                                case 'loggedOut':
                                    $content = $content."<td>You have logged out.</td>";
                                    break;
                                case 'activateUser':
                                    if (empty($row->processedUsername))
                                        $content = $content."<td>You have activated user '$row->processedUser'.</td>";
                                    else
                                        $content = $content."<td>You have activated user '$row->processedUsername'.</td>";
                                    break;
                                case 'addUser':
                                    $content = $content."<td>You have added user '$row->processedUsername'.</td>";
                                    break;
                                case 'deactivateUser':
                                    if (empty($row->processedUsername))
                                        $content = $content."<td>You have deactivated user '$row->processedUser'.</td>";
                                    else
                                        $content = $content."<td>You have deactivated user '$row->processedUsername'.</td>";
                                    break;
                                case 'updateUser':
                                    $content = $content."<td>You have updated access rights of user '$row->processedUsername'.</td>";
                                    break;
                                case 'deleteUser':
                                    if (empty($row->processedUsername))
                                        $content = $content."<td>You have deleted user '$row->processedUser'.</td>";
                                    else
                                        $content = $content."<td>You have deleted user '$row->processedUsername'.</td>";
                                    break;
                                case 'userResetPassword':
                                    $content = $content."<td>You have changed your password.</td>";
                                    break;
                                case 'resetPassword':
                                    if (empty($row->processedUsername))
                                        $content = $content."<td>You have reset password of user '$row->processedUser'.</td>";
                                    else
                                        $content = $content."<td>You have reset password of user '$row->processedUsername'.</td>";
                                    break;
                                //*****Subscriptions
                                case 'disableSubscription':
                                    $content = $content."<td>You have disabled tag '$row->tagName'.</td>";
                                    break;
                                case 'enableSubscription':
                                    $content = $content."<td>You have enabled tag '$row->tagName' from ".gmdate("M. d, Y  g:i a",$row->sdate)." to ".gmdate("M. d, Y  g:i a",$row->edate).".</td>";
                                    break;
                                case 'deleteSubscription':
                                    $content = $content."<td>You have has deleted tag '$row->tagName'.</td>";
                                    break;
                                case 'addSubscription':
                                    $content = $content."<td>You have added tag '$row->tagName' for $tagType that will be active from ".gmdate("M. d, Y  g:i a",$row->sdate)." to ".gmdate("M. d, Y  g:i a",$row->edate).".</td>";
                                    break;
                                //*****View Entries
                                case 'saveEntries':
                                    $content = $content."<td>You have saved entries under tag '$row->tagName'.</td>";
                                    break;
                                case 'revoke':
                                    $content = $content."<td>You have revoked invalidation on an <a href='$row->entryLink' target='_blank'>entry.</a></td>";
                                    break;
                                case 'viewEntries':
                                    $content = $content."<td>You have viewed entries under tag '$row->tagName' that are $filter from ".gmdate("M. d, Y  g:i a",$row->sdate)." to ".gmdate("M. d, Y  g:i a",$row->edate).".</td>";
                                    break;
                                //*****Validation
                                case 'invalidate':
                                    $content = $content."<td>You have invalidated an <a href='$row->entryLink' target='_blank'>entry.</a></td>";
                                    break;
                                case 'validate':
                                    $content = $content."<td>You have validated an <a href='$row->entryLink' target='_blank'>entry.</a></td>";
                                    break;
                                //*****Report
                                case 'report':
                                    $content = $content."<td>You have searched entries for report from ".gmdate("M. d, Y  g:i a",$row->sdate)." to ".gmdate("M. d, Y  g:i a",$row->edate)." with TIN $row->tin and amount $amount.</td>";
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
                
                    </div>
                    </div>
                </div>
           </div>  
        </div>
        <?php echo $footer; ?>
    <script src="<?php  echo $base;?>others/js/bootstrap.min.js"></script>
    </body>
</html>
