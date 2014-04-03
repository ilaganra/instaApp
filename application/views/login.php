
    <?php  
    echo $header; ?>
    <!-- Footer -->
    <link href="<?php  echo $base;?>others/css/sticky-footer-navbar.css" rel="stylesheet">
    <script src="<?php  echo $base;?>others/js/jquery.min.js"></script>  
    <script src="<?php  echo $base;?>others/js/jquery.blockUI.js"></script>
    <script src="<?php  echo $base;?>others/js/flash_detect_min.js"></script>
    <script>
        function detectFlashVersion(){	
            if(FlashDetect.installed){
                if(FlashDetect.versionAtLeast(10)){
                    return true;
		}else{
                    return false;
                }   
            }
            else{
                return false;
            }
	}
        $(document).ready(function($) {
            var ongoingLogin = false;
            setInterval(function () {
                if (ongoingLogin == false) location.reload(true);
            }, 60 * 5000);
            if (detectFlashVersion())
            {
                var div = document.getElementById('ok');
                div.style.display = 'block';
                $('#jumbotron').keypress(function (e) {
                    if (e.keyCode == 13)
                        login();
                });
                $('#signin').click(function() {
                    login();
                });
            }
            else{
                var div = document.getElementById('notOk');
                div.style.display = 'block';
            }
            function validateStr(str) {
                if ( /^[a-zA-Z0-9_\.\-]*$/.test(str) == true) { 
                    return true;
                }
                return false;
            }
            function login(){
                ongoingLogin = true;
                $("#ajaxResult").empty();
                $('#signin').button('loading');                      
                var username=($( "#username" ).val());
                var password=($( "#password" ).val());
                if ((username.match(/^\s*$/))||(password.match(/^\s*$/))) {
                     $('#ajaxResult').append("<div class='alert alert-warning'>Please make sure all fields are not empty.</div>");
                      $('#signin').button('reset');
                }
                else if(!((validateStr(username))&&(validateStr(password)))){
                    $('#ajaxResult').append("<div class='alert alert-warning'>Illegal characters are not accepted.</div>");
                    $('#signin').button('reset');
                }
                else{
                    var params = { username:username, password:password };
                    $.ajax({
                        type: 'post',
                        url: '<?php echo $base;?>users/login',
                        data: params,
                        cache: false,
                        success: function (data) {
                            //$('#ajaxResult').append("<div class='alert alert-warning'>"+data+"</div>");
                            if (data==='true'){
                                window.location.replace("<?php echo $base;?>users");      
                            }
                            else if (data == 'for_deactivation'){
                                $('#ajaxResult').append("<div class='alert alert-danger'>Sorry, this account will be deactivated since you have reached the maximum number of login tries.Please contact the administrator for more details.</div>");
                                $('#signin').button('reset');
                            }
                            else if (data == 'deactivated'){
                                $('#ajaxResult').append("<div class='alert alert-warning'>Sorry, this account is deactivated. Please contact the administrator for more details.</div>");
                                $('#signin').button('reset');
                            }
                            else{
                                $('#ajaxResult').append("<div class='alert alert-warning'>"+data+"</div>");
                                $('#signin').button('reset');
                            }
                        },
                        error: function(xhr, textStatus, error){
                            $('#signin').button('reset');
                            $('#ajaxResult').append("<div class='alert alert-warning'>Server Error</div>");
                        }
                    });
                }
                ongoingLogin = false;
            }
        });
    </script>
    </head>
    <body>
        <?php echo $navBar; ?>
        <!--/Main Container -->
        <div id="wrap">
            <div class="container"><center>
                <div id="jumbotron" class="jumbotron"> 
                    <div id="ok" style="display:none">
                        <div class="input-group input-group-sm">
                            <h5  class="text-primary">Please login</h5>
                            <input type="text" id="username" class="input-block-level" placeholder="Username" autofocus="autofocus"><br/>
                            <input type="password" id="password" class="input-block-level" placeholder="Password"><br/><br/>
                            <button class="btn btn-xs btn-primary  btn-block" data-loading-text="Loading..." id="signin">Sign in</button><br/><br/>
                        </div>
                        <div class="row" id="ajaxResult">         
                        </div>   
                    </div>
                    <!--[if lt IE 10]>
                    <div id="lessIE10" >
                      <div class='alert alert-danger'>This application works best on browsers IE10 and above.</div>
                    </div>
                    <![endif]-->
                    <div id="notOk" class ="alert alert-danger" style="display:none">
                        Flash-enabled browser is required to view this application. Thank you.
                    </div>
                    </center>  
                </div>
           </div>  
        </div>
        <?php echo $footer; ?>
        <script src="<?php  echo $base;?>others/js/bootstrap.min.js"></script>
    
    </body>
</html>