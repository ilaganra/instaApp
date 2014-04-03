<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');
class users extends CI_Controller {
	function __construct()
	{		
            parent::__construct();
            $this->load->helper('url');
            $this->load->model('Instagram_model');
            $this->load->model('Web_model');
            $this->load->model('user_model');
            $this->load->library('instagram_api');
            $this->base = $this->config->item('base_url');
            $this->gmt = 8*60*60;
            date_default_timezone_set('Asia/Manila');
            $this->currentDateTime = ((strtotime(date("Y-m-d H:i:s"))+$this->gmt) );
            $this->output->set_header("Cache-Control: no-store, no-cache, must-revalidate");
	}
        public function checkSession($session){
            $session_data = $this->session->userdata('logged_in');
            if ($session_data[$session] != 1)
                redirect(site_url('users'));
        }
        public function userMgt(){
            $this->checkSession('userAccess'); //check if user have access right to the page
            $session_data = $this->session->userdata('logged_in');
            $data['header'] = $this->Web_model->getHeader('User Management');
            $data['navBar'] = $this->Web_model->getNavBar();
            $data['footer'] = $this->Web_model->getFooter();
            $data['base'] = $this->base;
            $data['id'] = $session_data['id'];
            $data['users'] = $this->user_model->getAllUsers();
            $this->load->view("userMgt",$data);
        }
        public function history(){
            $this->checkSession('history'); //check if user have access right to the page
            $data['header'] = $this->Web_model->getHeader('Audit Trail');
            $data['navBar'] = $this->Web_model->getNavBar();
            $data['footer'] = $this->Web_model->getFooter();
            $data['base'] = $this->base;
            $history = $this->user_model->getHistory();
            $data['history'] = $history;
            $users =  $this->user_model->getDistinctUsers();
            $ctr=0;
            foreach($users as $row)
            {
                $userArray[$ctr++] = $row->username;
            }
            $data['userArray'] = $userArray;
            $this->load->view('history',$data);     
        }
        public function index () {
            if(($this->session->userdata('logged_in')))
            {
               redirect('users/home'); 
            }
            else{
                if (!(($this->session->userdata('initial_login')))){
                    $login['tries'] = 0;
                    $login['date'] = $this->currentDateTime;
                    $this->session->set_userdata('initial_login',$login);
                }
                else{
                    $initial_login =  $this->session->userdata('initial_login');
                    if (($this->currentDateTime - $initial_login['date']) >= 120)
                    {
                        $login['tries'] = 0;
                        $login['date'] = $this->currentDateTime;
                        $this->session->set_userdata('initial_login',$login);
                    }
                }
                $data['header'] = $this->Web_model->getHeader('Login');
                $data['navBar'] = $this->Web_model->getNavBarLogin();
                $data['footer'] = $this->Web_model->getFooter();
                $data['base'] = $this->base;
                $this->load->view('login',$data);    
            }
        }
        public function home()
	{
            if(($this->session->userdata('logged_in')))
            {
                $data['header'] = $this->Web_model->getHeader('Home');
                $sessionData =$this->session->userdata('logged_in');
                $data['fname'] = $sessionData['fname'];
                $data['username'] = $sessionData['username'];
                $data['lname'] = $sessionData['lname'];
                $data['eadd'] = $sessionData['eadd'];
                $data['navBar'] = $this->Web_model->getNavBar();
                $data['footer'] = $this->Web_model->getFooter();
                $data['base'] = $this->base;
                $history = $this->user_model->getHistoryUser('id',$sessionData['id']);
                $data['history'] = $history;
                $users =  $this->user_model->getDistinctUsers();
                $ctr=0;
                foreach($users as $row)
                {
                    $userArray[$ctr++] = $row->username;
                }
                $data['userArray'] = $userArray;
                $this->load->view('index',$data);
            }
            else{
                redirect('users'); 
            }
	}
        public function login(){
            if (!((isset ($_POST['username']))&&(isset($_POST['password']))))
                redirect ('users');
            $username =htmlspecialchars(trim(($_POST['username'])), ENT_QUOTES);
            $password =htmlspecialchars(trim(($_POST['password'])), ENT_QUOTES);
            $result = $this->user_model->checkIfUserExist($username);
            $ua = $this->browser_info();
            if ($result->num_rows() == 1)
            {
                foreach( $result->result() as $row){
                    $data = array(
                            'id' => $row->id,
                            'status' => $row->status,
                            'fname' => $row->firstName,
                            'lname' => $row->lastName,
                            'eadd' => $row->emailAddress,
                            'username' => $row->username,
                            'password' => $row->password,
                            'subscription' => $row->subscription,
                            'viewEntries'=> $row->viewEntries,
                            'validation' => $row->validation,
                            'webToolReport' => $row->webToolReport,
                            'userAccess' => $row->userAccess,
                            'history' => $row->history,
                            'lastLogin' => $row->lastLogin,
                            'lastLogout' => $row->lastLogout
                        );
                        $hashedPwd = $row->password;
                }
                if ($data['status'] == 2){
                    echo 'deactivated';
                }
                else if ($this->verify($password,$hashedPwd))
                {
                    if ($this->session->userdata('alluserSession')){
                        $usernameAttempt = $this->session->userdata('alluserSession');
                        foreach ($usernameAttempt['user'] as $value) {
                            $this->session->unset_userdata($value);
                        }
                        $this->session->unset_userdata('alluserSession');
                    }
                    $this->user_model->updateLastLogin($data['id']);
                    $this->session->set_userdata('logged_in',$data);
                    //Insert action to history
                    $histData[0]['field'] = 'action'; 
                    $histData[0]['value'] = 'loggedIn'; 
                    $histData[1]['field'] = 'userId'; 
                    $histData[1]['value'] = $data['id'];
                    $histData[2]['field'] = 'dateProcessed'; 
                    $histData[2]['value'] = $this->currentDateTime;
                    $histData[3]['field'] = 'ipAddr'; 
                    $histData[3]['value'] = $this->get_client_ip();
                    $histData[4]['field'] = 'browser'; 
                    $histData[4]['value'] = $ua[0]['browser'];
                    $histData[5]['field'] = 'version'; 
                    $histData[5]['value'] = $ua[0]['version'];
                    $this->user_model->insertHistory($histData);
                    echo 'true';
                }
                else{
                    $this->checkSessions($username);
                }
            }
            else{
                echo  'Wrong username or password';
            }
            //Insert action to history
            $histData[0]['field'] = 'action'; 
            $histData[0]['value'] = 'attemptLogin'; 
            $histData[1]['field'] = 'processedUsername'; 
            $histData[1]['value'] = $username;
            $histData[2]['field'] = 'dateProcessed'; 
            $histData[2]['value'] = $this->currentDateTime;
            $histData[3]['field'] = 'ipAddr'; 
            $histData[3]['value'] = $this->get_client_ip();
            $histData[4]['field'] = 'browser'; 
            $histData[4]['value'] = $ua[0]['browser'];
            $histData[5]['field'] = 'version'; 
            $histData[5]['value'] = $ua[0]['version'];
            $this->user_model->insertHistory($histData);
        }
        public function checkSessions($username){
            if (($this->session->userdata($username))){
                $initial_login =  $this->session->userdata($username);
                $initial_login['tries']++;
                if ($initial_login['tries'] >= 3){
                    $this->session->unset_userdata($username);
                    $this->user_model->deactivateUser('username',$username);
                    //Insert action to history
                    $histData[0]['field'] = 'action'; 
                    $histData[0]['value'] = 'deactivateUser'; 
                    $histData[1]['field'] = 'processedUsername'; 
                    $histData[1]['value'] = $username;   
                    $histData[2]['field'] = 'dateProcessed'; 
                    $histData[2]['value'] = $this->currentDateTime;
                    $this->user_model->insertHistory($histData);
                    echo  'for_deactivation';
                }
                else{
                    $this->session->set_userdata($username,$initial_login);
                    echo  'Wrong username or password';
                }
            }
            else{
                $initial_login['tries'] = 1;
                $initial_login['date'] = $this->currentDateTime;
                $this->session->set_userdata($username,$initial_login);
                echo  'Wrong username or password';
            }
            //store all user with wrong login attempts
            if ($this->session->userdata('alluserSession')){
                $usernameAttempt = $this->session->userdata('alluserSession');
                $usernameAttempt['user'][$usernameAttempt['count']] = $username;
                $usernameAttempt['count']++;
                $this->session->set_userdata('alluserSession',$usernameAttempt);
            }
            else{
                $usernameAttempt['count'] = 0;
                $usernameAttempt['user'][0] = $username;
                $usernameAttempt['count']++;
                $this->session->set_userdata('alluserSession',$usernameAttempt);
            }
        }
        public function userResetPassword(){
            if (!((isset ($_POST['oldPwd']))&&(isset($_POST['newPwd']))))
                redirect ('users');
            $oldPwd = html_escape($_POST['oldPwd']);
            $newPwd = html_escape($_POST['newPwd']);
            $session_data = $this->session->userdata('logged_in');
            $checkPwd = $this->user_model->checkIfRightPwd($session_data['username'],$oldPwd); 
            if ($checkPwd){
                $resetPassword = $this->user_model->resetPassword($newPwd, 'id',$session_data['id']);
                if ($resetPassword){
                    $result['status']="success";
                    $result['description'] = "Successfully changed password of user.";
                    //Insert action to history
                    $histData[0]['field'] = 'action'; 
                    $histData[0]['value'] = 'userResetPassword'; 
                    $histData[1]['field'] = 'userId'; 
                    $histData[1]['value'] = $session_data['id'];
                    $histData[2]['field'] = 'dateProcessed'; 
                    $histData[2]['value'] = $this->currentDateTime; 
                    $this->user_model->insertHistory($histData);
                }else{
                    $result['status']="danger";
                    $result['description'] = "Please try changing the password later."; 
                }    
            }else{
                $result['status']="danger";
                $result['description'] = "Old Password incorrect."; 
            }
            echo json_encode($result); 
        }
        public function deleteUser(){
            $this->checkSession('userAccess'); //check if user have access right to the page
            if (!((isset ($_POST['field']))&&(isset($_POST['value']))))
                redirect ('users');
            $field = $_POST['field'];
            $value = $_POST['value'];
            $deleteUser = $this->user_model->deleteUser($field,$value);
            $session_data = $this->session->userdata('logged_in');
            if($deleteUser){
                $result['status']="success";
                $result['description'] = "Successfully deleted user.";
                //Insert action to history
                $histData[0]['field'] = 'action'; 
                $histData[0]['value'] = 'deleteUser'; 
                $histData[1]['field'] = 'userId'; 
                $histData[1]['value'] = $session_data['id'];
                if ($field == 'id'){
                    $histData[2]['field'] = 'processedUserId'; 
                    $histData[2]['value'] = $value;    
                }
                else{
                    $histData[2]['field'] = 'processedUsername'; 
                    $histData[2]['value'] = $value;   
                }
                $histData[3]['field'] = 'dateProcessed'; 
                $histData[3]['value'] = $this->currentDateTime;
                $this->user_model->insertHistory($histData);
            }else{
                $result['status']="danger";
                $result['description'] = "Something happened when deleting user. Try again later."; 
            }
            echo json_encode($result); 
        }
        public function activateUser(){
            $this->checkSession('userAccess'); //check if user have access right to the page
            if (!((isset ($_POST['field']))&&(isset($_POST['value']))))
                redirect ('users');
            $field = $_POST['field'];
            $value = $_POST['value'];
            $session_data = $this->session->userdata('logged_in');
            $activateUser = $this->user_model->activateUser($field,$value);
            if($activateUser){
                $result['status']="success";
                $result['description'] = "Successfully activated this user.";
                //Insert action to history
                $data[0]['field'] = 'action'; 
                $data[0]['value'] = 'activateUser'; 
                $data[1]['field'] = 'userId'; 
                $data[1]['value'] = $session_data['id'];
                if ($field == 'id'){
                    $data[2]['field'] = 'processedUserId'; 
                    $data[2]['value'] = $value;    
                }
                else{
                    $data[2]['field'] = 'processedUsername'; 
                    $data[2]['value'] = $value;   
                }
                $data[3]['field'] = 'dateProcessed'; 
                $data[3]['value'] = $this->currentDateTime;
                $this->user_model->insertHistory($data);
            }else{
                $result['status']="danger";
                $result['description'] = "Something happened when activating this user. Try again later."; 
            }
            echo json_encode($result); 
        }
        public function deactivateUser(){
            $this->checkSession('userAccess'); //check if user have access right to the page
            if (!((isset ($_POST['field']))&&(isset($_POST['value']))))
                redirect ('users');
            $field = $_POST['field'];
            $value = $_POST['value'];
            $session_data = $this->session->userdata('logged_in');
            $deactivateUser = $this->user_model->deactivateUser($field,$value);
            if($deactivateUser){
                $result['status']="success";
                $result['description'] = "Successfully deactivated this user.";
                //Insert action to history
                $data[0]['field'] = 'action'; 
                $data[0]['value'] = 'deactivateUser'; 
                $data[1]['field'] = 'userId'; 
                $data[1]['value'] = $session_data['id'];
                if ($field == 'id'){
                    $data[2]['field'] = 'processedUserId'; 
                    $data[2]['value'] = $value;    
                }
                else{
                    $data[2]['field'] = 'processedUsername'; 
                    $data[2]['value'] = $value;   
                }
                $data[3]['field'] = 'dateProcessed'; 
                $data[3]['value'] = $this->currentDateTime;
                $this->user_model->insertHistory($data);
            }else{
                $result['status']="danger";
                $result['description'] = "Something happened when deactivating this user. Try again later."; 
            }
            echo json_encode($result); 
        }
        public function addUser(){
            $this->checkSession('userAccess'); //check if user have access right to the page
            if (!((isset ($_POST['lname']))&&(isset($_POST['fname']))))
                redirect ('users');
            
            $session_data = $this->session->userdata('logged_in');
            $accessRights =htmlspecialchars(trim(($_POST['checkedBoxes'])), ENT_QUOTES);
            $lname =htmlspecialchars(trim(($_POST['lname'])), ENT_QUOTES);
            $fname =htmlspecialchars(trim(($_POST['fname'])), ENT_QUOTES);
            $eadd =htmlspecialchars(trim(($_POST['eadd'])), ENT_QUOTES);
            $username =htmlspecialchars(trim(($_POST['username'])), ENT_QUOTES);    
            $checkDup = $this->user_model->checkDupUsername($username);
            $checkDupEmail = $this->user_model->checkDupEmail($eadd);
            $pwd = $this->randomPassword();
            if ($checkDupEmail->num_rows()!=0){
                $result['status']="warning";
                $result['description'] = "Email address already exists.";
            }
            else if ($checkDup->num_rows()==0){
                $addedUser = $this->user_model->addUser($username,$pwd,$accessRights,$lname,$fname,$eadd);
                if($addedUser){
                    //Email User
                    $to =  $eadd;
                    $subject = 'PSRInstagram WebTool Password Verification';
                    $message = "<b><p>Hello $fname $lname,</b></p><p>You can now access the <a href='social.psr.com.ph'>PSRInstagram WebTool</a>. Your username is $username, and your temporary password is $pwd. Kindly change your password upon login.</p>";
                    $mailDate = gmdate("M. d, Y g:i a",$this->currentDateTime);
                    $emailResult = $this->Web_model->mail($to,$subject,$message,$mailDate);

                    if ($emailResult){
                        $result['status']="success";
                        $result['description'] = "Successfully added user. An email was sent at $eadd";
                    }
                    else{
                        $result['status']="success";
                        $result['description'] = "Successfully added user but no email was sent to $eadd";
                    }//**Insert action to history
                    $data[0]['field'] = 'action'; 
                    $data[0]['value'] = 'addUser'; 
                    $data[1]['field'] = 'userId'; 
                    $data[1]['value'] = $session_data['id'];
                    $data[2]['field'] = 'processedUsername'; 
                    $data[2]['value'] = $username;   
                    $data[3]['field'] = 'dateProcessed'; 
                    $data[3]['value'] = $this->currentDateTime;
                    $this->user_model->insertHistory($data);
                    
                }else{
                    $result['status']="danger";
                    $result['description'] = "Something happened when adding user. Try again later."; 
                }
            }
            else{
                $result['status']="warning";
                $result['description'] = "Username already exists.";
            }
        
            echo json_encode($result); 
        }
        public function resetPassword(){
            $this->checkSession('userAccess'); //check if user have access right to the page
            $session_data = $this->session->userdata('logged_in');
            if (!((isset($_POST['field']))&&(isset($_POST['value']))&&(isset($_POST['eadd']))))
                redirect ('users');
            $field =htmlspecialchars(trim(($_POST['field'])), ENT_QUOTES);
            $value =htmlspecialchars(trim(($_POST['value'])), ENT_QUOTES);
            $eadd =htmlspecialchars(trim(($_POST['eadd'])), ENT_QUOTES);
            $newPassword =  $this->randomPassword();
            $resetPassword = $this->user_model->resetPassword($newPassword, $field,$value);
            if ($resetPassword){
                $result['status']="success";
                $result['description'] = "Successfully reset password of user. An email was sent at $eadd";
                //Email User
                $to = $eadd;
                $subject = 'PSRInstagram WebTool Password Verification';
                $message = "<b><p>Hello, </p></b><p>Your password was reset by ".$session_data['fname']." ".$session_data['lname'].". Your new temporary password is $newPassword. Kindly change your password upon login.</p>";
                $mailDate = gmdate("M. d, Y g:i a",$this->currentDateTime);
                $emailResult = $this->Web_model->mail($to,$subject,$message,$mailDate);
                if ($emailResult){
                    $result['status']="success";
                    $result['description'] = "Successfully reset password of user. An email was sent at $eadd";
                }
                else{
                    $result['status']="success";
                    $result['description'] = "Email was sent to $eadd. Please try again later.";
                }
                //**Insert action to history
                $histData[0]['field'] = 'action'; 
                $histData[0]['value'] = 'resetPassword'; 
                $histData[1]['field'] = 'userId'; 
                $histData[1]['value'] = $session_data['id'];
                if ($field == 'id'){
                    $histData[2]['field'] = 'processedUserId'; 
                    $histData[2]['value'] = $value;    
                }
                else{
                    $histData[2]['field'] = 'processedUsername'; 
                    $histData[2]['value'] = $value;   
                } 
                $histData[3]['field'] = 'dateProcessed'; 
                $histData[3]['value'] = $this->currentDateTime;
                $this->user_model->insertHistory($histData);
            }else{
                $result['status']="danger";
                $result['description'] = "Please try resetting the password later."; 
            }
            echo json_encode($result); 
        }
        public function randomPassword() {
            $alphabet = "abcdefghijklmnopqrstuwxyzABCDEFGHIJKLMNOPQRSTUWXYZ0123456789";
            $pass = array(); //remember to declare $pass as an array
            $alphaLength = strlen($alphabet) - 1; //put the length -1 in cache
            for ($i = 0; $i < 8; $i++) {
                $n = rand(0, $alphaLength);
                $pass[] = $alphabet[$n];
            }
            return implode($pass); //turn the array into a string
        }
        function unique_id($length) {
            return substr(md5(uniqid(mt_rand(), true)), 0, $length);
        }
        public function updateUser(){
            $this->checkSession('userAccess'); //check if user have access right to the page
            $session_data = $this->session->userdata('logged_in');
            if (!((isset($_POST['username']))&&(isset($_POST['accessRights']))))
                redirect ('users');
            $username = $_POST['username'];
            $accessRights = $_POST['accessRights'];
            $updateUser = $this->user_model->updateUser($username,$accessRights);
            if ($updateUser){
                $result['status']="success";
                $result['description'] = "Successfully updated access rights of $username.";
                //**Insert action to history
                $data[0]['field'] = 'action'; 
                $data[0]['value'] = 'updateUser'; 
                $data[1]['field'] = 'userId'; 
                $data[1]['value'] = $session_data['id'];
                $data[2]['field'] = 'processedUsername'; 
                $data[2]['value'] = $username;   
                $data[3]['field'] = 'dateProcessed'; 
                $data[3]['value'] = $this->currentDateTime;
                $this->user_model->insertHistory($data);
            }
            else{
                $result['status']="warning";
                $result['description'] = "Something happened when updating user access rights. Try again later.";
            }
            echo json_encode($result); 
        }
        function test(){       
             echo $this->generateHash('railagan');
        }
        function verify($password, $hashedPassword) {
            //return crypt($password, $hashedPassword) == $hashedPassword;
            $this->load->library( 'PasswordHash' );
            return  $this->passwordhash->CheckPassword( $password, $hashedPassword );
              
        }
        function generateHash($password) {
            /**if (defined("CRYPT_BLOWFISH") && CRYPT_BLOWFISH) {
                $salt = '$2y$11$'. substr(md5(uniqid(rand(), true)), 0, 22);
                return crypt('adminonly', $salt);
            }*/
            $this->load->library( 'PasswordHash' );
            return $this->passwordhash->HashPassword( $password );
        }
        // Function to get the client ip address
        function get_client_ip() {
            $ipaddress = '';
            if (getenv('HTTP_CLIENT_IP'))
                $ipaddress = getenv('HTTP_CLIENT_IP');
            else if(getenv('HTTP_X_FORWARDED_FOR'))
                $ipaddress = getenv('HTTP_X_FORWARDED_FOR');
            else if(getenv('HTTP_X_FORWARDED'))
                $ipaddress = getenv('HTTP_X_FORWARDED');
            else if(getenv('HTTP_FORWARDED_FOR'))
                $ipaddress = getenv('HTTP_FORWARDED_FOR');
            else if(getenv('HTTP_FORWARDED'))
                $ipaddress = getenv('HTTP_FORWARDED');
            else if(getenv('REMOTE_ADDR'))
                $ipaddress = getenv('REMOTE_ADDR');
            else
                $ipaddress = 'UNKNOWN';
            return $ipaddress;
        }
        function browser_info($agent=null) {
            // Declare known browsers to look for
            $known = array('chrome','msie', 'firefox', 'safari', 'webkit', 'opera', 'netscape',
              'konqueror', 'gecko');

            // Clean up agent and build regex that matches phrases for known browsers
            // (e.g. "Firefox/2.0" or "MSIE 6.0" (This only matches the major and minor
            // version numbers.  E.g. "2.0.0.6" is parsed as simply "2.0"
            $agent = strtolower($agent ? $agent : $_SERVER['HTTP_USER_AGENT']);
            $pattern = '#(?<browser>' . join('|', $known) .
              ')[/ ]+(?<version>[0-9]+(?:\.[0-9]+)?)#';
            
            // Find all phrases (or return empty array if none found)
            if (!preg_match_all($pattern, $agent, $matches)) return array();
            // Since some UAs have more than one phrase (e.g Firefox has a Gecko phrase,
            // Opera 7,8 have a MSIE phrase), use the last one found (the right-most one
            // in the UA).  That's usually the most correct.
            $i = count($matches['browser'])-1;
            if (in_array("chrome", $matches['browser'])){
                $i = array_search('chrome', $matches['browser']);
            }
            $ret['browser'] = $matches['browser'][$i];
            $ret['version'] = $matches['version'][$i];
            return array( $ret );
        }
        function getBrowser(){
            static $browser;//No accident can arise from depending on an unset variable.
            if(!isset($browser)){
                $browser = get_browser($_SERVER['HTTP_USER_AGENT']);
            }
            return $browser;
        }
        public function destroy(){
            $session_data = $this->session->userdata('logged_in');
            $this->user_model->updateLastLogout($session_data['id']);
            //**Insert action to history
            $data[0]['field'] = 'action'; 
            $data[0]['value'] = 'loggedOut'; 
            $data[1]['field'] = 'userId'; 
            $data[1]['value'] = $session_data['id'];
            $data[2]['field'] = 'dateProcessed'; 
            $data[2]['value'] = $this->currentDateTime; 
            $this->user_model->insertHistory($data);
            $this->session->unset_userdata('logged_in');
            session_destroy(); 
            redirect('/users');
        }
}
