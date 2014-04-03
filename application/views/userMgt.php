    <?php echo $header;?>
    <link rel="stylesheet" href="<?php  echo $base;?>others/css/jquery-ui.css" />
    <link type="text/css" href="<?php  echo $base;?>others/css/jquery.simple-dtpicker.css" rel="stylesheet" />
    <style type="text/css" title="currentStyle">
        @import "<?php echo $base;?>others/css/demo_table.css";               
    </style>
    <!-- Footer -->
    <link href="<?php  echo $base;?>others/css/sticky-footer-navbar.css" rel="stylesheet">

    <script src="<?php  echo $base;?>others/js/jquery.min.js"></script>  
    <script type="text/javascript" language="javascript" src="<?php echo $base;?>others/js/jquery.dataTables.js"></script>
    <script type="text/javascript" src="<?php  echo $base;?>others/js/jquery.simple-dtpicker.js"></script>    
    <script src="<?php  echo $base;?>others/js/jquery-ui.js"></script>
    <script src="<?php  echo $base;?>others/js/jquery.blockUI.js"></script>
    <script type="text/javascript" src="<?php  echo $base;?>others/js/ZeroClipboard.min.js"></script>
    <script>
        $(document).ready(function($) {
            
            var FF = !(window.mozInnerScreenX == null);
            if(FF) {
               var sheet = (document.styleSheets[0]);
               sheet.insertRule(".btn-block { width: auto  !important; }", 1);
            }
            var rowPos;
            var rowVal;
            var action = '';
            var callback = '';
            var ongoingChange=false;
            var oTable = $('#table').dataTable( {
                            "oLanguage": {
                                "sSearch": "Search all columns:"
                            },
                            
                            'aoColumnDefs':[
                                { 'bVisible': false, 'aTargets': [0,14] }
                            ]
                        });
            var modalFooter = document.getElementById('modal-footer');
            function getRowSelected(row)
            {
                rowPos = oTable.fnGetPosition( row );
                rowVal = oTable.fnGetData( row ); 
            }
            $('#table tbody').on('click','input[id^="change"]', function(){
                $('#ajaxResult').empty();
                if(ongoingChange == true){
                    $('#ajaxResult').append(" <div class='alert alert-warning'><center>Please finish ongoing change on an entry.</center></div>");

                }
                else{
                    ongoingChange=true;
                    getRowSelected( this.parentNode.parentNode);                 
                    var id = rowVal[0];
                    var username= rowVal[2];
                    var accessRight=[];
                    var updateCol=[];
                    var editId =  ((id === undefined )||(id==='')) ? username:id;
                    accessRight[0]= $( "#subscription"+editId ).val();
                    accessRight[1]= $( "#viewEntries"+editId ).val() ;
                    accessRight[2]= $( "#validation"+editId ).val() ;
                    accessRight[3]= $( "#webToolReport"+editId ).val();
                    accessRight[4]= $( "#userAccess"+editId ).val();
                    accessRight[5]= $( "#history"+editId ).val();
                    for (var i=0; i<accessRight.length; i++) {
                        if (accessRight[i] == 1) {
                           updateCol.push("<label class='checkbox-inline' ><input type='checkbox' class='editAccessRight' checked></label>");
                        }
                        else{
                           updateCol.push("<label class='checkbox-inline' ><input type='checkbox' class='editAccessRight'></label>");
                        }
                    }
                    oTable.fnUpdate( [rowVal[0],rowVal[1],rowVal[2],rowVal[3],rowVal[4],rowVal[5],updateCol[0],updateCol[1],updateCol[2],updateCol[3],updateCol[4],updateCol[5],
                                    "<input type='button' id='confirm"+editId+"'  class='btn btn-xs btn-primary btn-block' value='Confirm'/>\n\
                                     <input type='button' id='cancel"+editId+"'  class='btn btn-xs btn-danger btn-block' value='Cancel'/>",rowVal[13],rowVal[14]],rowPos);
                }
             });
             $('#table tbody').on('click','input[id^="cancel"]', function(){
                  $('#ajaxResult').empty();
                  oTable.fnUpdate( [rowVal[0],rowVal[1],rowVal[2],rowVal[3],rowVal[4],rowVal[5],rowVal[6],rowVal[7],rowVal[8],rowVal[9],rowVal[10],rowVal[11],rowVal[12],rowVal[13],rowVal[14]],rowPos);
                  ongoingChange=false;
             });
             $('#table tbody').on('click','input[id^="confirm"]', function(){
                 callback = confirm;
                 $('#tableModal .modal-body p').html("Are you sure you want to change access rights of  user '"+rowVal[2]+"'?");
                 openModal();
             });
             $('#table tbody').on('click','input[id^="delete"]', function(){
                if(ongoingChange == true){
                    $('#ajaxResult').append(" <div class='alert alert-warning'><center>Please finish ongoing change on an entry.</center></div>");
                }
                else{
                    getRowSelected( this.parentNode.parentNode); 
                    callback = deleteRow;
                    $('#tableModal .modal-body p').html("Are you sure you want to delete user '"+rowVal[2]+"'?");
                    openModal();   
                }   
            });
            $('#table tbody').on('click','input[id^="activate"]', function(){
                if(ongoingChange == true){
                    $('#ajaxResult').append(" <div class='alert alert-warning'><center>Please finish ongoing change on an entry.</center></div>");
                }
                else{
                    getRowSelected( this.parentNode.parentNode); 
                    callback = activate;
                    $('#tableModal .modal-body p').html("Are you sure you want to activate  user '"+rowVal[2]+"'?");
                    openModal();   
                }
            });
            $('#table tbody').on('click','input[id^="deactivate"]', function(){
                if(ongoingChange == true){
                    $('#ajaxResult').append(" <div class='alert alert-warning'><center>Please finish ongoing change on an entry.</center></div>");
                }
                else{
                    getRowSelected( this.parentNode.parentNode); 
                    callback = deactivate;
                    $('#tableModal .modal-body p').html("Are you sure you want to deactivate  user '"+rowVal[2]+"'?");
                    openModal();   
                }
            });
            $('#table tbody').on('click','button[id^="changePwd"]', function(){
                if(ongoingChange == true){
                    $('#ajaxResult').append(" <div class='alert alert-warning'><center>Please finish ongoing change on an entry.</center></div>");
                }
                else{
                    getRowSelected( this.parentNode.parentNode); 
                    callback = changePwd;
                    $('#tableModal .modal-body p').html("Are you sure you want to reset password of  user '"+rowVal[2]+"'?");
                    openModal();   
                }
            });
            
            $('#add').on('click', function(){
                if(ongoingChange == true){
                    $('#ajaxResult').append(" <div class='alert alert-warning'><center>Please finish ongoing change on an entry.</center></div>");
                }
                else{
                    callback = add;
                    $('#tableModal .modal-body p').html("Are you sure you want to add this user?");
                    openModal();   
                }
             });
            $('#clear').on('click', function(){
                clearFields();
            });  
            $('#yes').on('click', function(){
                
                //blockUI();
                callback();
                //$.unblockUI();
            }); 
            function closeModal(){
                $('#tableModal').modal('hide');
                modalFooter.style.display = 'block';
                ongoingChange = false;
            }
            function openModal(){
                $('#ajaxResult').empty();
                $("#tableModal").modal({ backdrop: 'static',keyboard: false});
            }
            
            function confirm(){
                modalFooter.style.display = 'none';
                $('#tableModal .modal-body p').html("Please wait...");
                var id=rowVal[0];
                var username=rowVal[2];
                var fname=rowVal[3];
                var lname=rowVal[4];
                var eadd=rowVal[5];
                var chkArray=  getCheckedBoxes('editAccessRight'); 
                var checkedBoxes = chkArray.join('-');
                var params = { username:username, accessRights:checkedBoxes };
                $.ajax({
                    type: 'post',
                    url: "<?php echo $base;?>users/updateUser/", 
                    cache: false,
                    data: params,
                    success: function (data) {  
                        var result = $.parseJSON(data);
                        var status = result.status;
                        var description = result.description;
                        var icon=[];
                        icon[0] = 'glyphicon glyphicon-remove';
                        icon[1] = 'glyphicon glyphicon-ok';
                        var editId =  ((id === undefined )||(id==='')) ? username:id;
                        if (status=='success')
                        {
                            clearFields();
                            oTable.fnUpdate( [id,
                                            rowVal[1],
                                            username,
                                            fname,
                                            lname,
                                            eadd,
                                            "<span class='"+ icon[chkArray[0]]+"'></span><input type='hidden' id='subscription"+ editId+"'  value='"+ chkArray[0]+"'/>",
                                            "<span class='"+ icon[chkArray[1]]+"'></span><input type='hidden' id='viewEntries"+ editId+"'  value='"+ chkArray[1]+"'/>",
                                            "<span class='"+ icon[chkArray[2]]+"'></span><input type='hidden' id='validation"+ editId+"'  value='"+ chkArray[2]+"'/>",
                                            "<span class='"+ icon[chkArray[3]]+"'></span><input type='hidden' id='webToolReport"+ editId+"'  value='"+ chkArray[3]+"'/>",
                                            "<span class='"+ icon[chkArray[4]]+"'></span><input type='hidden' id='userAccess"+ editId+"'  value='"+ chkArray[4]+"'/>",
                                            "<span class='"+ icon[chkArray[5]]+"'></span><input type='hidden' id='history"+ editId+"'  value='"+ chkArray[5]+"'/>",
                                            "<input type='button' id='change"+editId+"'  class='btn btn-xs btn-primary btn-block' value='Change'/><input type='button' id='delete"+editId+"'  class='btn btn-xs btn-danger btn-block' value='Delete'/>",
                                            rowVal[13],rowVal[14]],rowPos );         
                        }
                        $('#ajaxResult').append("<div class='alert alert-"+status+"'><center><b>"+description+"</b></center></div>");
                        closeModal();
                    },
                    error: function(xhr, textStatus, error){
                        $('#ajaxResult').append(" <div class='alert alert-danger'><center><b><br/>"+xhr.statusText+"<br/>"+textStatus+"<br/>"+error+"</b></center></div>");
                        closeModal();
                    }
                });                    
            }
            function deleteRow(){
                modalFooter.style.display = 'none';
                $('#tableModal .modal-body p').html("Please wait...");
                $('#ajaxResult').empty();
                ongoingChange=true;
                var field =  ((rowVal[0] === undefined )||(rowVal[0]==='')) ? 'username':'id';
                var editId =  ((rowVal[0] === undefined )||(rowVal[0]==='')) ? rowVal[2]:rowVal[0];
                var params = { field:field, value:editId };
                $.ajax({
                    type: 'post',
                    url: "<?php echo $base;?>users/deleteUser", 
                    data: params,
                    cache: false,
                    success: function (data) {  
                        var result = $.parseJSON(data);
                        var status = result.status;
                        var description = result.description;
                        if (status=='success')
                        {
                            oTable.fnDeleteRow( rowPos);
                        }
                        $('#ajaxResult').append("<div class='alert alert-"+status+"'><center><b>"+description+"</b></center></div>");
                        closeModal();
                    },
                    error: function(xhr, textStatus, error){
                        $('#ajaxResult').append(" <div class='alert alert-danger'><center><b><br/>"+xhr.statusText+"<br/>"+textStatus+"<br/>"+error+"</b></center></div>");
                        closeModal();
                    }
                });
            }
            function activate(){
                modalFooter.style.display = 'none';
                $('#tableModal .modal-body p').html("Please wait...");
                $('#ajaxResult').empty();
                ongoingChange=true;
                var field =  ((rowVal[0] === undefined )||(rowVal[0]==='')) ? 'username':'id';
                var editId =  ((rowVal[0] === undefined )||(rowVal[0]==='')) ? rowVal[2]:rowVal[0];
                var params = { field:field, value:editId };
                $.ajax({
                    type: 'post',
                    url: "<?php echo $base;?>users/activateUser",
                    data: params,
                    cache: false,
                    success: function (data) {  
                        var result = $.parseJSON(data);
                        var status = result.status;
                        var description = result.description;
                        if (status=='success')
                        {
                            oTable.fnUpdate( "<b>Valid</b><br/><input type='button' id='deactivate"+editId+"'  class='btn btn-xs btn-danger btn-block' value='Deactivate'/>", rowPos, 13 );
                            oTable.fnUpdate( "1", rowPos, 14 );
                            
                        }
                        $('#ajaxResult').append("<div class='alert alert-"+status+"'><center><b>"+description+"</b></center></div>");
                        closeModal();
                        
                    },
                    error: function(xhr, textStatus, error){
                        $('#ajaxResult').append(" <div class='alert alert-danger'><center><b><br/>"+xhr.statusText+"<br/>"+textStatus+"<br/>"+error+"</b></center></div>");
                        closeModal();
                    }
                });
            
            }
            function deactivate(){
                modalFooter.style.display = 'none';
                $('#tableModal .modal-body p').html("Please wait...");
                $('#ajaxResult').empty();
                ongoingChange=true;
                var field =  ((rowVal[0] === undefined )||(rowVal[0]==='')) ? 'username':'id';
                var editId =  ((rowVal[0] === undefined )||(rowVal[0]==='')) ? rowVal[2]:rowVal[0];
                var params = { field:field, value:editId };
                $.ajax({
                    type: 'post',
                    url: "<?php echo $base;?>users/deactivateUser", 
                    data: params,
                    cache: false,
                    success: function (data) {  
                        var result = $.parseJSON(data);
                        var status = result.status;
                        var description = result.description;
                        if (status=='success')
                        {
                            oTable.fnUpdate( "<b>Invalid</b><br/><input type='button' id='activate"+editId+"'  class='btn btn-xs btn-primary btn-block' value='Activate'/>", rowPos, 13 );
                            oTable.fnUpdate( "2", rowPos, 14 );
                        }
                        $('#ajaxResult').append("<div class='alert alert-"+status+"'><center><b>"+description+"</b></center></div>");
                        closeModal();
                    },
                    error: function(xhr, textStatus, error){
                        $('#ajaxResult').append(" <div class='alert alert-danger'><center><b><br/>"+xhr.statusText+"<br/>"+textStatus+"<br/>"+error+"</b></center></div>");
                        closeModal();
                    }
                });
                
            }
            function changePwd(){
                ongoingChange=true;
                modalFooter.style.display = 'none';
                $('#tableModal .modal-body p').html("please wait...");
                var field =  ((rowVal[0] === undefined )||(rowVal[0]==='')) ? 'username':'id';
                var value =  ((rowVal[0] === undefined )||(rowVal[0]==='')) ? rowVal[2]:rowVal[0];
                var ajaxParams = { 'eadd':rowVal[5],'field':field,'value':value };
                $.ajax({
                    type: 'post',
                    url: "<?php echo $base;?>users/resetPassword", 
                    data: ajaxParams,
                    cache: false,
                    success: function (data) {                          
                        var result = $.parseJSON(data);
                        var status = result.status;
                        var description = result.description;                          
                        $('#ajaxResult').append("<div class='alert alert-"+status+"'><center><b>"+description+"</b></center></div>");
                        closeModal();
                    },
                    error: function(xhr, textStatus, error){
                        $('#ajaxResult').append(" <div class='alert alert-danger'><center><b><br/>"+xhr.statusText+"<br/>"+textStatus+"<br/>"+error+"</b></center></div>");
                        closeModal();
                    }
                });
                  
            }
            function validateEmail(email) { 
                var re = /^(([^<>()[\]\\.,;:\s@\"]+(\.[^<>()[\]\\.,;:\s@\"]+)*)|(\".+\"))@((\[[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\])|(([a-zA-Z\-0-9]+\.)+[a-zA-Z]{2,}))$/;
                return re.test(email);
            }
            function validateStr(str) {
                if ( /^[a-zA-Z0-9_\.\-]*$/.test(str) == true) { 
                    return true;
                }
                return false;
            }
            function add(){
                modalFooter.style.display = 'none';
                $('#tableModal .modal-body p').html("Please wait...");
                var username = $( "#username" ).val();
                var lname = $( "#lname" ).val();
                var fname = $( "#fname" ).val();
                var eadd = $( "#eadd" ).val();
                $('#ajaxResult').empty();
                var chkArray=  getCheckedBoxes('addAccessRight'); 
                var checkedBoxes = chkArray.join('-');
                //alert(checkedBoxes);
                if ((username.match(/^\s*$/))||(lname.match(/^\s*$/))||(fname.match(/^\s*$/))||(eadd.match(/^\s*$/)))
                {
                     $('#ajaxResult').append("<div class='alert alert-warning'><center>Please fill up all input fields.</center></div>");
                     closeModal();
                }
                else if (!((validateStr(username))&&(username.match(/[a-zA-Z]/g))&&(validateUsername(lname))&&(validateUsername(fname))))
                {
                    $('#ajaxResult').append("<div class='alert alert-warning'><center>Invalid username or last name or first name.</center></div>");
                    closeModal();
                }
                else if ((username.length)<6)
                {
                    $('#ajaxResult').append("<div class='alert alert-warning'><center>Username field must be atleast 6 characters.</center></div>");
                    closeModal();
                }
                else if(!(validateEmail(eadd)))
                {
                     $('#ajaxResult').append("<div class='alert alert-warning'><center>Invalid Email address.</center></div>");
                     closeModal();
                }
                else{
                    var params = { username:username, checkedBoxes:checkedBoxes, lname:lname, fname:fname, eadd:eadd };
                    $.ajax({
                        type: 'post',
                        url: "<?php echo $base;?>users/addUser", 
                        data: params,
                        cache: false,
                        success: function (data) {                          
                            var result = $.parseJSON(data);
                            var status = result.status;
                            var description = result.description;
                            var icon=[];
                            icon[0] = 'glyphicon glyphicon-remove';
                            icon[1] = 'glyphicon glyphicon-ok';
                            if (status=='success')
                            {
                                clearFields();
                                     $('#table').dataTable().fnAddData( ["",
                                                                        "<button id='changePwd"+username+"' class='btn btn-xs btn-danger btn-block' title='Reset Password'><span class='glyphicon glyphicon-flash'></span></button>",
                                                                        username,
                                                                        fname,
                                                                        lname,
                                                                        eadd,
                                                                        "<span class='"+ icon[chkArray[0]]+"'></span><input type='hidden' id='subscription"+ username+"'  value='"+ chkArray[0]+"'/>",
                                                                        "<span class='"+ icon[chkArray[1]]+"'></span><input type='hidden' id='viewEntries"+ username+"'  value='"+ chkArray[1]+"'/>",
                                                                        "<span class='"+ icon[chkArray[2]]+"'></span><input type='hidden' id='validation"+ username+"'  value='"+ chkArray[2]+"'/>",
                                                                        "<span class='"+ icon[chkArray[3]]+"'></span><input type='hidden' id='webToolReport"+ username+"'  value='"+ chkArray[3]+"'/>",
                                                                        "<span class='"+ icon[chkArray[4]]+"'></span><input type='hidden' id='userAccess"+ username+"'  value='"+ chkArray[4]+"'/>",
                                                                        "<span class='"+ icon[chkArray[5]]+"'></span><input type='hidden' id='history"+ username+"'  value='"+ chkArray[5]+"'/>",
                                                                        "<input type='button' id='change"+username+"'  class='btn btn-xs btn-primary btn-block' value='Change'/>\n\
                                                                        <input type='button' id='delete"+username+"'  class='btn btn-xs btn-danger btn-block' value='Delete'/>",
                                                                        "<b>Valid</b><br/><input type='button' id='deactivate"+ username+"'  class='btn btn-xs btn-danger btn-block' value='Deactivate'/>",
                                                                        "1"] );                        
                            }
                            $('#ajaxResult').append("<div class='alert alert-"+status+"'><center><b>"+description+"</b></center></div>");
                            closeModal();
                        },
                        error: function(xhr, textStatus, error){
                            $('#ajaxResult').append(" <div class='alert alert-danger'><center><b><br/>"+xhr.statusText+"<br/>"+textStatus+"<br/>"+error+"</b></center></div>");
                            closeModal();
                        }
                    });
                }
            }
            function validateUsername(username){
                if(/^[a-zA-Z0-9-.-_ ]*$/.test(username) == false) {
                   return false;
                }
                return true;
            }
            function clearFields(){
                $( "#username").val('');
                $( "#fname").val('');
                $( "#lname").val('');
                $( "#eadd").val('');
                var classChk =".addAccessRight";
                $(classChk).each(function () {
                            this.checked= false;
                });
            }
            function getCheckedBoxes(className) {
                /* declare an checkbox array */
                var chkArray = [];
                var checkedBoxes='';
                /* look for all checkboes that have a class 'chk' attached to it and check if it was checked */
                var classChk ="."+className;
                $(classChk).each(function () {
                    if (this.checked){
                        chkArray.push('1');
                    }
                    else
                        chkArray.push('0');
                });
               // checkedBoxes = chkArray.join('-');
                return chkArray;        
            }
        });
    </script>
    </head>
    <body>
        <?php echo $navBar; ?>
        <!--/Main Container -->
        <div id="wrap">
            <div class="container">
                <h3>User Management</h3>
                <div class="jumbotron">
                    <br/><h5  class="text-primary">Add new user: </h5>
                    <div class="row">
                        <div class="input-group input-group-sm">
                            <span class="input-group-addon">Username</span>
                            <input type="text" id="username" length="20" class="form-control" placeholder="">  
                            <span class="input-group-addon">First Name</span>
                            <input type="text" id="fname" length="20" class="form-control" placeholder="">  
                            <span class="input-group-addon">Last Name</span>
                            <input type="text" id="lname" length="20" class="form-control" placeholder="">  
                            <span class="input-group-addon">Email Address</span>
                            <input type="text" id="eadd" length="20" class="form-control" placeholder="">  
                                 
                        </div>
                    </div>
                    <div class="row">           
                         
                         <div class="input-group input-group-sm">
                            <span class="input-group-addon">Access Rights</span>
                            <span class="input-group-addon">
                                <label class="checkbox-inline">
                                  <input type="checkbox" class='addAccessRight' value="0"> Subscription Mgt
                                </label></span>
                            <span class="input-group-addon">
                                <label class="checkbox-inline" >
                                  <input type="checkbox" class='addAccessRight' value="1" > Entry View
                                </label>
                            </span>
                            <span class="input-group-addon">
                                <label class="checkbox-inline" >
                                  <input type="checkbox" class='addAccessRight' value="2"> Entry Validation
                                </label >
                            </span>
                            <span class="input-group-addon">
                                <label class="checkbox-inline" >
                                  <input type="checkbox" class='addAccessRight' value="3"> Web Tool Report
                                </label>
                            </span>
                            <span class="input-group-addon">
                                <label class="checkbox-inline" id="checkbox" >
                                  <input type="checkbox" class='addAccessRight' value="4"> Users
                                </label>
                            </span>
                            <span class="input-group-addon">
                                <label class="checkbox-inline" id="checkbox" class='chkDemo'>
                                  <input type="checkbox" class='addAccessRight' value="5"> History
                                </label>
                            </span>
                            <span class="input-group-btn">
                                <button id="add" class="btn btn-xs btn-primary btn-block">Add</button>
                            </span>
                            <span class="input-group-btn">
                                <button id="clear" class="btn btn-xs btn-info btn-block">Clear</button>
                            </span>
                        </div>
                     </div>
                    
                     <div class="row" id="ajaxResult">
                     </div>
                    <br/><br/><br/>
                    <table cellpadding="0" cellspacing="0" border="0" class="display" id="table">
                        <thead>
                                <tr>
                                        <th>id</th>
                                        <th></th>
                                        <th>Username</th>
                                        <th>First Name</th>
                                        <th>Last Name</th>
                                        <th>Email Address</th>
                                        <th>Subscription <br/>Mgt</th>
                                        <th>Entry View</th>
                                        <th>Validation</th>
                                        <th>Web Tool <br/>Report</th>
                                        <th>Users</th>
                                        <th>History</th>
                                        <th>Action</th>
                                        <th>Status</th>
                                        <th>S</th>
                                </tr>
                        </thead>
                        <tbody>
                            <?php
                            $icon[0] = 'glyphicon glyphicon-remove';
                            $icon[1] = 'glyphicon glyphicon-ok';
                            foreach($users as $row)
                            {
                                if (($row->id !=1) &&($row->id != $id))
                                {
                                    $content = "";
                                    $content = $content."<tr >";
                                    $content = $content."<td>".$row->id."</td>";
                                    $content = $content."<td><button id='changePwd$row->id' class='btn btn-xs btn-danger btn-block' title='Reset Password'><span class='glyphicon glyphicon-flash'></span></button></td>";
                                    $content = $content."<td>".$row->username."</td>";
                                    $content = $content."<td>".$row->firstName."</td>";
                                    $content = $content."<td>".$row->lastName."</td>";
                                    $content = $content."<td>".$row->emailAddress." </td>";
                                    $content = $content."<td><span class='".$icon[$row->subscription]."'></span>
                                                            <input type='hidden' id='subscription$row->id'  value='$row->subscription'/></td>";
                                    $content = $content."<td><span  class='".$icon[$row->viewEntries]."'></span>
                                                            <input type='hidden' id='viewEntries$row->id' value='$row->viewEntries' /></td>";
                                    $content = $content."<td><span class='".$icon[$row->validation]."'></span>
                                                            <input type='hidden' id='validation$row->id' value='$row->validation' /></td>";
                                    $content = $content."<td><span  class='".$icon[$row->webToolReport]."'></span>
                                                             <input type='hidden' id='webToolReport$row->id' value='$row->webToolReport'  /></td>";
                                    $content = $content."<td><span class='".$icon[$row->userAccess]."'></span>
                                                              <input type='hidden' id='userAccess$row->id' value='$row->userAccess'   /></td>";
                                    $content = $content."<td><span  class='".$icon[$row->history]."'></span>
                                                             <input type='hidden' id='history$row->id' value='$row->history'   /></td>";
                                    $content = $content."<td><input type='button' id='change$row->id'  class='btn btn-xs btn-primary btn-block' value='Change'/>
                                                             <input type='button' id='delete$row->id'  class='btn btn-xs btn-danger btn-block' value='Delete'/></td>";
                                    if ($row->status == 2)
                                        $content = $content."<td><b>Invalid</b><br/><input type='button' id='activate$row->id'  class='btn btn-xs btn-primary btn-block' value='Activate'/></td>";
                                    else
                                        $content = $content."<td><b>Valid</b><br/><input type='button' id='deactivate$row->id'  class='btn btn-xs btn-danger btn-block' value='Deactivate'/></td>";
                                    $content = $content."<td>".$row->status."</td>";
                                    $content = $content."</tr>";
                                    echo $content;
                                }
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
                <p class="text-center"></p>
              </div>
              <div class="modal-footer" id="modal-footer">
                   <button type="button" class="btn btn-success" id="yes">Yes</button>    
                    <button type="button" class="btn btn-default" data-dismiss="modal">No</button>   
              </div>
            </div><!-- /.modal-content -->
          </div><!-- /.modal-dialog -->
        </div><!-- /.modal -->
       
    <script type="text/javascript" language="javascript" src="<?php  echo $base;?>others/js/bootstrap.min.js"></script>
    </body>
</html>