    <?php echo $header; ?>
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
        $(document).ready(function ($) {
            //Globals
            var row = 0;
            var clip = '';
            var rowPos = 0;
            var rowOngoingValidation = '';
            var rowVal = '';
            var ongoingValidation = false;
            var oTable = $('#display').dataTable({
                "bSort":false,
                "sDom": 'T<"clear">lfrtip',
                "oLanguage": {
                    "sSearch": "Search a tag:"
                },
                'aoColumnDefs': [{
                    'bVisible': false,
                    'aTargets': [0, 2, 10]
                }, {
                    "bSearchable": false,
                    "aTargets": [0, 1, 2, 3, 4, 5, 6, 8, 9]
                }]
            });
            //check if there are new entries every minute
            setInterval(function () {
                var id = $('#hiddenId').val();
                if ((id != '')|| (empty(id) == false))
                {
                    $.ajax({
                        type: 'post',
                        url: "<?php echo $base;?>instagram/getNextEntries",
                        cache: false,
                        data: {id:id},
                        success: function (data) {
                            if (data != 0) {// if there are no new entries, hide view more div
                                var btn = document.getElementById('viewMore');
                                btn.innerHTML = 'View More Entries ('+data+')';
                                if (ongoingValidation == false){
                                var div = document.getElementById('addtlButtons');
                                div.style.display = 'block'; }
                            }
                        }
                    });
                }
            }, 60 * 1000);
            //reloads the page to get all new entries
            $('#viewMore').on('click', function () {
                $('#ajaxResult').empty();
                if (ongoingValidation == true) $('#ajaxResult').append("<div class='alert alert-danger'><center><b>Ongoing validation detected.</b></center></div>");
                else {
                    window.location = "<?php echo $base; ?>" + "instagram/validation";
                }
            });
            $('#confirmInvalidation').on('click', function () {
                ongoingValidation = true;
                $('#invalidateModal').modal('hide');
                blockUI();
                var reason = ($('#reasonsTxt').val()).replace(/[^\w\s]/gi, '');
                if (reason == '') reason = 'invalid';
                var id = rowVal[0];
                var username = rowVal[3];
                var datePosted = rowVal[10];
                var status = '2';
                var tin = rowVal[4];
                var orNo = rowVal[5];
                var amount = rowVal[6];
                
                if ((tin == '' )||(orNo == '')||(amount == ''))
                {
                    tin = 0; orNo = 0; amount = 0;
                }
                var params = {id:id,remarks:reason,status:status,username:username,timePosted:datePosted, tin:tin,orNo:orNo,amount:amount};
                $.ajax({
                    type:'post',
                    url: "<?php echo $base;?>instagram/validateEntry",
                    cache: false,
                    data: params,
                    success: function (data) {
                        $("#ajaxResult").empty();
                        var result = $.parseJSON(data);
                        status = result.status;
                        var description = result.description;
                        if (status == "validated") {
                            oTable.fnDeleteRow(rowPos);
                            ongoingValidation = false;
                        }
                        if (status == "success") {
                            var copy = result.copy;
                             oTable.fnUpdate("<textarea id='reasonsTxt' class='form-control' rows='3'> "+copy+"</textarea>"+
                                            " <button id='copy"+rowVal[0]+"' class='btn btn-xs btn-success btn-block' data-clipboard-text='"+copy+"'>Click to copy and comment</button>"
                                            +"<a href='" + rowVal[2] + "' id='linkOpened" + rowVal[0] + "'target='_blank'>View Entry</a>&nbsp&nbsp"+
                                            "<a href='#' id='hideRow" + rowVal[0] + "' >Close Entry</a>",
                                            rowPos, 9);
                            clip = new ZeroClipboard(document.getElementById("copy" + rowVal[0]), {
                                moviePath: "<?php  echo $base;?>others/media/ZeroClipboard.swf"
                            });
                            clip.on("load", function (client) {
                                client.on("complete", function (client, args) {
                                    $('#copyModal .modal-body').html("Text Copied!");
                                    $("#copyModal").modal();
                                });
                            });
                        }
                        $('#ajaxResult').append("<div class='alert alert-" + status + "'><center><b>" + description + "</b></center></div>");
                        $.unblockUI();
                    },
                    error: function (xhr, textStatus, error) {
                        $.unblockUI();
                        $('#ajaxResult').append(" <div class='alert alert-danger'><center><b><br/>" + xhr.statusText + "<br/>" + textStatus + "<br/>" + error + "</b></center></div>");
                    }
                });

            });
            $('#confirmValidation').on('click', function () {
                ongoingValidation = true;
                $('#validateModal').modal('hide');
                blockUI();
                var status = '1';
                var username = rowVal[3];
                var id = rowVal[0];
                var datePosted = rowVal[10];
                var tin = rowVal[4];
                var orNo = rowVal[5];
                var amount = rowVal[6];
                if ((tin == '' )||(orNo == '')||(amount == ''))
                {
                    tin = 0; orNo = 0; amount = 0;
                }
                var params = {id:id,remarks:0,status:status,username:username,timePosted:datePosted, tin:tin,orNo:orNo,amount:amount};
                $.ajax({
                    type: 'post',
                    url: "<?php echo $base;?>instagram/validateEntry/",
                    type: 'post',
                    url: "<?php echo $base;?>instagram/validateEntry",
                    cache: false,
                    data: params,
                    success: function (data) {
                        $("#ajaxResult").empty();
                        var result = $.parseJSON(data);
                        status = result.status;
                        var description = result.description;
                        if (status == "validated") {
                            oTable.fnDeleteRow(rowPos);
                            ongoingValidation = false;
                        }
                        if (status == "invalid") {
                            var copy = result.copy;
                             oTable.fnUpdate("<textarea id='reasonsTxt' class='form-control' rows='3'> "+copy+"</textarea>"+
                                            " <button id='copy"+rowVal[0]+"' class='btn btn-xs btn-success btn-block' data-clipboard-text='"+copy+"'>Click to copy and comment</button>"
                                            +"<a href='" + rowVal[2] + "' id='linkOpened" + rowVal[0] + "'target='_blank'>View Entry</a>&nbsp&nbsp"+
                                            "<a href='#' id='hideRow" + rowVal[0] + "' >Close Entry</a>",
                                            rowPos, 9);
                        }
                        if (status == "success") {
                            var copy = result.copy;
                            ongoingValidation = true;
                             oTable.fnUpdate("<textarea id='reasonsTxt' class='form-control' rows='3'> "+copy+"</textarea>"+
                                            "<button id='copy"+rowVal[0]+"' class='btn btn-xs btn-success btn-block' data-clipboard-text='"+copy+"'>Click to copy and comment</button>"
                                            +"<a href='" + rowVal[2] + "' id='linkOpened" + rowVal[0] + "'target='_blank'>View Entry</a>&nbsp&nbsp"+
                                            "<a href='#' id='hideRow" + rowVal[0] + "' >Close Entry</a>",
                                    rowPos, 9);
                        }
                        if ((status == "success") || (status == "invalid")) {
                            if (status == "invalid") status == 'danger';
                            clip = new ZeroClipboard(document.getElementById("copy" + rowVal[0]), {
                                moviePath: "<?php  echo $base;?>others/media/ZeroClipboard.swf"
                            });

                            clip.on("load", function (client) {
                                client.on("complete", function (client, args) {
                                    $('#copyModal .modal-body').html("Text Copied!");
                                    $("#copyModal").modal();
                                });
                            });
                        }
                        $('#ajaxResult').append("<div class='alert alert-success'><center><b>" + description + "</b></center></div>");
                        $.unblockUI();
                    },
                    error: function (xhr, textStatus, error) {
                        $.unblockUI();
                        $('#ajaxResult').append(" <div class='alert alert-danger'><center><b><br/>" + xhr.statusText + "<br/>" + textStatus + "<br/>" + error + "</b></center></div>");
                    }
                });
            });
            $('#display tbody').on('click', 'a[id^="viewImg"]', function () {
                //alert(ongoingValidation +" "+ this.id +" "+ rowVal[0]);
                if ((ongoingValidation == false) || (this.id == 'viewImg' + rowVal[0])) {
                    getRowSelected(this.parentNode.parentNode);
                    $('#filterModal .modal-body').html(" <iframe src='" + rowVal[2].replace(/^http:+/, "") + "embed/' width='500' height='600' frameborder-'0' scrolling='no' allowtransparency='true'></iframe>\n\
                                                        <center><p class='text-primary'>Details</p><p><b>Tin: </b>"+rowVal[4]+"<b> OrNo: </b>"+rowVal[5]+"<b> Amount: </b>"+rowVal[6]+"</p></center>");
                    if (ongoingValidation)
                        $('#filterModal .modal-footer').html("<button type='button' data-dismiss='modal' class='btn btn-default'>Close</button>");
                    else
                        $('#filterModal .modal-footer').html("<button type='button' id='validate"+rowVal[0]+"' class='btn btn-primary'>Validate</button><button type='button' id='invalidate"+rowVal[0]+"' class='btn btn-danger'>Invalidate</button><button type='button' data-dismiss='modal' class='btn btn-default'>Close</button>");
                    $("#filterModal").modal({
                        backdrop: 'static',
                        keyboard: false
                    });
                } else {
                    $("#ajaxResult").empty();
                    $('#ajaxResult').append("<div class='alert alert-danger'><center><b>Ongoing validation detected.</b></center></div>");
                }
            });
            
            
            
            $('#display tbody').on('click', 'a[id^="hideRow"]', function () {
                ongoingValidation = false;
                $("#ajaxResult").empty();
                getRowSelected(this.parentNode.parentNode);
                oTable.fnDeleteRow(rowPos);
            });
            /**$('#display tbody').on('click', 'a[id^="linkOpened"]', function () {
                getRowSelected(this.parentNode.parentNode);
                oTable.fnUpdate("<a href='#' id='hideRow" + rowVal[0] + "' >Close Entry</a>", rowPos, 9); //
            });*/
            $('#filterModal').on('click', 'button[id^="invalidate"]', function () {
               $('#filterModal').modal('hide');
                if (ongoingValidation == true) {
                    $("#ajaxResult").empty();
                    $('#ajaxResult').append("<div class='alert alert-danger'><center><b>Ongoing validation detected.</b></center></div>");
                } else {
                    $("textarea[id=reasonsTxt]").val("");
                    $('#invalidateModal .modal-body p').html("Are you sure you want to invalidate this entry of <b>" + rowVal[3] + "</b> with <br/><b>TIN:</b> " + rowVal[4] + "<b> ORNO: </b>" + rowVal[5] + "<b> Amount: </b>" + rowVal[6] + "?");
                    $("#invalidateModal").modal({
                        backdrop: 'static',
                        keyboard: false
                    });
                }
            });
            $('#filterModal').on('click', 'button[id^="validate"]', function () {
                $('#filterModal').modal('hide');
                if (ongoingValidation == true) {
                    $("#ajaxResult").empty();
                    $('#ajaxResult').append("<div class='alert alert-danger'><center><b>Ongoing validation detected.</b></center></div>");
                } else {
                    $('#validateModal .modal-body p').html("Are you sure you want to validate this entry of <b>" + rowVal[3] + "</b> with <br/><b>TIN:</b> " + rowVal[4] + "<b> ORNO: </b>" + rowVal[5] + "<b> Amount: </b>" + rowVal[6] + "?");
                    $("#validateModal").modal({
                        backdrop: 'static',
                        keyboard: false
                    });
                }
            });

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
            }

            function getRowSelected(row) {
                rowPos = oTable.fnGetPosition(row);
                rowVal = oTable.fnGetData(row);
            }
    });
    </script>
    </head>
    <body>
        <?php echo $navBar; ?>
        <!--/Main Container -->
        <div id="wrap">
            <div class="container">
                <h3>Validation</h3>
                <div class="jumbotron"> 
                     <div class="row" id="ajaxResult">
                     </div>
                    <div id="addtlButtons" style="display:none" >
                    <br/><br/>
                    <center><button id="viewMore" class="btn btn-primary btn-lg" value="View More"></button></center>
                    </div>
                    <table cellpadding="0" cellspacing="0" border="0" class="display" id="display">
                        <thead>
                                <tr>
                                        <th>id</th>
                                        <th>Date Posted</th>
                                        <th>link</th>
                                        <th>Username</th>
                                        <th>Tin</th>
                                        <th>OR No</th>
                                        <th>Amount</th>
                                        <th>Tag</th>
                                        <th>Action</th>
                                        <th>Result</th>
                                        <th>TimeP</th>
                                          
                                </tr>
                        </thead>
                        <tbody>
                         <?php
                                $ctr=0;
                                $id=0;
                                foreach($entries as $row)
                                {       
                                        $content = "";
                                        if ($row->id > $id)
                                            $id = $row->id;
                                        $content = $content."<tr>";
                                        $content = $content."<td>".$row->id."</td>";
                                        $content = $content."<td><a href='$row->entryLink' target='_blank'>".gmdate("M. d, Y  g:i a",$row->timePosted)."</a></td>";
                                        $content = $content."<td>".$row->entryLink."</td>";
                                        $content = $content."<td>".$row->username."</td>";
                                        $content = $content."<td>".$row->tin."</td>";
                                        $content = $content."<td>".$row->orNo."</td>";
                                        $content = $content."<td>".$row->amount."</td>";
                                        $content = $content."<td>".$row->tagName."</td>";
                                        $content = $content."<td><a  href='#' id='viewImg$row->id' >View Image</a></td>";
                                        $content = $content."<td></td>";
                                        $content = $content."<td>$row->timePosted</td>";
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
                    <input type='hidden' id='hiddenId' value='<?php echo $id; ?>'/>
                </div>   
            </div>  
        </div>
        
        <div id="dialog-confirm" title="Action Confirmation"  style="display:none">
            <p class="validateTips">Are you sure you want to do this?</p>
        </div>
        
        <!-- /.Modal for Image -->
        <div class="modal fade" id="filterModal" tabindex="-1" role="dialog" aria-labelledby="filterModalLabel" aria-hidden="true">
          <div class="modal-dialog  modal-vertical-centered">
            <div class="modal-content">             
              <div class="modal-body">              
                <iframe src="//instagram.com/p/gXkDQZqAdA/embed/" width="500" height="600" frameborder="0" scrolling="no" allowtransparency="true"></iframe>
              </div>
              <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>               
              </div>
            </div><!-- /.modal-content -->
          </div><!-- /.modal-dialog -->
        </div><!-- /.modal -->
        <!-- /.Modal for Copying text -->
        <div class="modal fade" id="copyModal" tabindex="-1" role="dialog" aria-labelledby="filterModalLabel" aria-hidden="true">
          <div class="modal-dialog modal-vertical-centered">
            <div class="modal-content">             
              <div class="modal-body">              
              </div>
            </div><!-- /.modal-content -->
          </div><!-- /.modal-dialog -->
        </div><!-- /.modal -->
        
        <!-- /Modal for validation -->
        <div class="modal fade" id="validateModal" tabindex="-1" role="dialog" aria-labelledby="validateLabel" aria-hidden="true">
          <div class="modal-dialog  modal-vertical-centered">
            <div class="modal-content ">
              <div class="modal-body">
                <p class="text-center"></p>
              </div>
              <div class="modal-footer">
                <button type="button" class="btn btn-success" id="confirmValidation">Confirm Validation</button>    
                <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>  
              </div>
            </div><!-- /.modal-content -->
          </div><!-- /.modal-dialog -->
        </div><!-- /.modal -->
        
        <!-- /Modal for Invalidation -->
        <div class="modal fade" id="invalidateModal" tabindex="-1" role="dialog" aria-labelledby="invalidateLabel" aria-hidden="true">
          <div class="modal-dialog  modal-vertical-centered">
            <div class="modal-content">
              <div class="modal-body">               
                <p class="text-center"></p>
                 <div class="form-group">
                <label for="reasonsTxt">Reasons:</label>
                <select id="reasonsTxt" class="selectpicker show-tick form-control">
                    <?php 
                        foreach($invalidReplies as $row)
                        {
                            if ($row->tid > 5)
                            echo "<option value='$row->tid'>$row->description</option>";
                        }
                    ?>
                </select>
                </div>
              </div>
              <div class="modal-footer">
                   <button type="button" class="btn btn-success" id="confirmInvalidation">Confirm Invalidation</button>    
                <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>               
              </div>
            </div><!-- /.modal-content -->
          </div><!-- /.modal-dialog -->
        </div><!-- /.modal -->
        <?php echo $footer; ?>

        

    <script type="text/javascript" language="javascript" src="<?php  echo $base;?>others/js/bootstrap.min.js"></script>
   
  
    </body>
</html>
