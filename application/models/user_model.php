<?php
class user_model extends CI_Model
{

    function __construct()
    {
        // Call the Model constructor
        parent::__construct();
        date_default_timezone_set('Asia/Manila');
        $this->gmt = 8*60*60;
        $this->dateNow =((strtotime(date("Y-m-d H:i:s"))+$this->gmt) );
            
    }
    public function insertHistory($data){
        $fields = '';
        $values = ''; 
        $totalData = count($data);
        $ctr = 0;
        foreach($data as $param){
            if ($ctr != $totalData - 1){
                $fields = $fields.'`'.$param['field'].'`,';
                $values = $values."'".$param['value']."',";
            }
            else{
                $fields = $fields.'`'.$param['field'].'`';
                $values = $values."'".$param['value']."'";
            }
            $ctr++;
        }
        $sql = "INSERT INTO `history`($fields) VALUES($values)";
        $query = $this->db->query($sql);
        return $query;
    }
    public function getHistory(){
        //$sql = "SELECT * from `history` a, `instagram.tagentries` b  WHERE a.userId = b.id OR a.processedUserId = b.id ORDER BY a.id DESC";
        $sql = "SELECT a.id, a.userId, b.username AS username, a.action, a.tagName, a.processedUserId, c.username AS processedUser, a.processedUsername, a.entryId, d.entryLink,a.ipAddr, a.browser,a.version,a.sdate, a.edate, a.tin, a.amount, a.filter, a.tagStatus, a.dateProcessed
FROM  `history` a
LEFT JOIN  `users` c ON a.processedUserId = c.id
LEFT JOIN  `users` b ON a.userId = b.id
LEFT JOIN  `instagram.tagentries` d ON a.entryId = d.id
ORDER BY a.id DESC";
        $query = $this->db->query($sql);
        return $query; 
    }
    public function getHistoryUser($field,$value){
        //$sql = "SELECT * from `history` a, `instagram.tagentries` b  WHERE a.userId = b.id OR a.processedUserId = b.id ORDER BY a.id DESC";
        $sql = "SELECT a.id, a.userId, b.username AS username, a.action, a.tagName, a.processedUserId, c.username AS processedUser, a.processedUsername, a.entryId, d.entryLink,a.ipAddr, a.browser,a.version,a.sdate, a.edate, a.tin, a.amount, a.filter, a.tagStatus, a.dateProcessed
FROM  `history` a
LEFT JOIN  `users` c ON a.processedUserId = c.id
LEFT JOIN  `users` b ON a.userId = b.id
LEFT JOIN  `instagram.tagentries` d ON a.entryId = d.id WHERE a.userid='$value'
ORDER BY a.id DESC";
        $query = $this->db->query($sql);
        return $query; 
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
    public function checkIfUserExist($username){
        $sql = "SELECT * from `users` WHERE `username` ='$username' AND `status` !=0";
        $query = $this->db->query($sql);
        return $query;
    }
    public function checkIfRightPwd($username,$pwd){
        $sql = "SELECT * from `users` WHERE `username` ='$username' AND `status`=1";
        $result = $this->db->query($sql);
        if ($result->num_rows() == 1)
        {
            $hashedPwd = '';
            foreach( $result->result() as $row){
                $hashedPwd = $row->password;
            }
            if ($this->verify($pwd,$hashedPwd))
            {
                return true;
            }
            else{
                return false;
            }
        }
        else{
            return false;
        }
    }
    public function getDistinctUsers(){
        $sql = "SELECT DISTINCT  `username` FROM  `users` WHERE  `status` =1";
        $query = $this->db->query($sql);
        return $query->result();
        
    }
    public function checkDupUsername($username){
        $sql = "SELECT * from `users` WHERE `username` ='$username' AND `status` !='0'";
        $query = $this->db->query($sql);
        return $query;
    }
    public function checkDupEmail($eadd){
        $sql = "SELECT * from `users` WHERE `emailAddress` ='$eadd' AND `status` !='0'";
        $query = $this->db->query($sql);
        return $query;
    }
    public function getAllUsers(){
        $sql = "SELECT  * FROM  `users` WHERE `status`!='0'";
        $query = $this->db->query($sql);
	return $query->result();
        
    }
    public function addUser($username,$password,$accessRights,$lname,$fname,$eadd)
    {
        $accessRights_array = (explode("-",$accessRights));
        $sql = "INSERT INTO `users`(`username`, `password`, `subscription`, `viewEntries`, `validation`, `webToolReport`, `userAccess`, `history`,`emailAddress`,`firstName`,`lastName`,`status`) VALUES ('$username','".$this->generateHash($password)."','$accessRights_array[0]','$accessRights_array[1]','$accessRights_array[2]','$accessRights_array[3]','$accessRights_array[4]','$accessRights_array[5]','$eadd','$fname','$lname','1')";
        $query = $this->db->query($sql);
	return $query;
        
    }
    public function updateUser($username,$accessRights)
    {
        $username=trim($username);
        $accessRights_array = (explode("-",$accessRights));
        $sql = "UPDATE `users` SET `subscription`='$accessRights_array[0]',`viewEntries`='$accessRights_array[1]',`validation`='$accessRights_array[2]',`webToolReport`='$accessRights_array[3]',`userAccess`='$accessRights_array[4]',`history`='$accessRights_array[5]' WHERE `username`='$username' AND `status` !='0'";
        $query = $this->db->query($sql);
	return $query;
    }
    public function deleteUser($field,$value)
    {
        $sql = "UPDATE `users` SET `status`='0' WHERE `$field`='$value' AND `status` !='0'";
        $query = $this->db->query($sql);
	return $query;
    }
    public function resetPassword($pwd,$field,$value)
    {
        $sql = "UPDATE `users` SET `password`='".$this->generateHash($pwd)."' WHERE `$field`='$value' AND `status` !='0'";
        $query = $this->db->query($sql);
	return $query;
    }
    public function activateUser($field,$value)
    {
        $sql = "UPDATE `users` SET `status`='1' ,`dateActivated`='$this->dateNow' WHERE `$field`='$value' AND `status` !='0'";
        $query = $this->db->query($sql);
	return $query;
    }
    public function deactivateUser($field,$value)
    {
        $sql = "UPDATE `users` SET `status`='2',`dateDeactivated`='$this->dateNow' WHERE `$field`='$value' AND `status` !='0'";
        $query = $this->db->query($sql);
	return $query;
    }
    public function updateLastLogin($id)
    {
        $sql = "UPDATE `users` SET `lastLogin`='$this->dateNow ' WHERE `id`='$id'";
        $query = $this->db->query($sql);
	return $query;
        
    }
    public function updateLastLogout($id)
    {
        
        $sql = "UPDATE `users` SET `lastLogout`='$this->dateNow ' WHERE `id`='$id'";
        $query = $this->db->query($sql);
	return $query;
        
    }
    
}
?>