
<!DOCTYPE html>
<html lang="en">
    <head>
    <title><?php echo $title; ?></title>
    <!-- Bootstrap core CSS -->
    <link href="<?php  echo $base;?>others/css/bootstrap.css" rel="stylesheet">
    <!-- Bootstrap theme -->
    <link href="<?php  echo $base;?>others/css/bootstrap-theme.min.css" rel="stylesheet">
  
    <link rel="stylesheet" href="http://code.jquery.com/ui/1.10.3/themes/smoothness/jquery-ui.css" />
    <link type="text/css" href="<?php  echo $base;?>others/css/jquery.simple-dtpicker.css" rel="stylesheet" />
    
    <style type="text/css" title="currentStyle">
        @import "<?php echo $base;?>others/css/demo_table.css";               
    </style>
    <!-- Footer -->
    <link href="<?php  echo $base;?>others/css/sticky-footer-navbar.css" rel="stylesheet">
    
    <script src="http://ajax.googleapis.com/ajax/libs/jquery/1/jquery.min.js"></script>  
    <script type="text/javascript" language="javascript" src="<?php echo $base;?>others/js/jquery.dataTables.js"></script>
    <script type="text/javascript" src="<?php  echo $base;?>others/js/jquery.simple-dtpicker.js"></script>    
    
    <script src="http://code.jquery.com/ui/1.10.3/jquery-ui.js"></script>
    <script src="http://malsup.github.io/jquery.blockUI.js"></script>
    <script>
        $(document).ready(function($) {
            //Globals
            var row = 0;
            var rowPos = 0;
            var rowVal = '';  
            //initialize html elements
            $('#datePosted').appendDtpicker({"closeOnSelected": true});
            $('#submit').click(function() {
                // update the block message
				var datePosted = $('#datePosted').val();
				var username =$('#username').val();
				var hashtag = $('#hashtag').val();
				var entrylink = $('#entrylink').val();
				var tin = $('#tin').val();
				var orno = $('#orno').val();
				var amount = $('#amount').val();
				var status = $('#status').val();
				var mediaid = $('#mediaid').val();
				blockUI();
				$.ajax({
                    url: "http://localhost/PSRInstagram/index.php/instagram/addEntryForTesting/?hashtag="+hashtag+"&username="+username+
					"&tin="+tin+"&orno="+orno+"&amount="+amount+"&mediaid="+mediaid+"&datePosted="+datePosted+"&status="+status,
                    cache: false,
                    success: function (data) {
                        $("#ajaxResult").empty();
                        $('#ajaxResult').append("<div class='alert alert-success'><center><b>"+data+"</b></center></div>");
                        $.unblockUI();
                    },
                    error: function(xhr, textStatus, error){
                        $.unblockUI();
                        $('#ajaxResult').append(" <div class='alert alert-danger'><center><b><br/>"+xhr.statusText+"<br/>"+textStatus+"<br/>"+error+"</b></center></div>");
                    }
                });
            });
             function blockUI()
             {
                 $.blockUI({ css: { 
                        border: 'none', 
                        padding: '15px', 
                        backgroundColor: '#000', 
                        '-webkit-border-radius': '10px', 
                        '-moz-border-radius': '10px', 
                        opacity: .5, 
                        color: '#fff' 
                    } }); 
             }
             
        });
    </script>
    </head>
    <body>
        <?php echo $navBar; ?>
        <!--/Main Container -->
        <div id="wrap">
            <div class="container">
                <div class="jumbotron"> 
                    <h3>View Report</h3>
                     <div class="row">           
                         <p>Search: </p>
                         <div class="input-group input-group-sm">
                            <span class="input-group-addon">datePosted</span>
                            <input type="text" class="form-control" id="datePosted" readonly="true" value="">
                               
                        </div>
                     </div>
					 <div class="row">           
                         <p>Search: </p>
                         <div class="input-group input-group-sm">
						 <span class="input-group-addon">username</span>
                            <input type="text" class="form-control" id="username"  value="">
                            <span class="input-group-addon">hashtag</span>
                            <input type="text" class="form-control" id="hashtag"  value="">
                            <span class="input-group-addon">status</span>
                            <input type="text" class="form-control" id="status"  value="">
                            <span class="input-group-addon">entry link</span>
                            <input type="text" class="form-control" id="entrylink"  value="">
                            
						    </div>
                     </div>
					
                     <div class="row">           
                         <p>Search: </p>
                         <div class="input-group input-group-sm">
						 <span class="input-group-addon">tin</span>
                            <input type="text" class="form-control" id="tin"  value="">
                         </span>
						<span class="input-group-addon">orno</span>
                            <input type="text" class="form-control" id="orno"  value="">
                         </span>
						<span class="input-group-addon">amount</span>
								<input type="text" class="form-control" id="amount"  value="">
							 </span>
						<span class="input-group-addon">mediaid</span>
								<input type="text" class="form-control" id="mediaid"  value="">
							 </span>
						<span class="input-group-btn">
                                     <button id="submit" class="btn btn-xs btn-primary">Insert</button>
                            </span> 
						 </div>
                     </div>

                     <div class="row" id="ajaxResult">
                     </div>
                </div>  
            </div>  
        </div>
        <?php echo $footer; ?>
    <script src="http://code.jquery.com/jquery-1.9.1.js"></script>
  
    <script src="<?php  echo $base;?>others/js/bootstrap.min.js"></script>
   
  
    </body>
</html>