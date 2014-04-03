<?php
class Instagram_model extends CI_Model
{

    function __construct()
    {
        // Call the Model constructor
        parent::__construct();
        date_default_timezone_set('Asia/Manila');
        $this->gmt = 8*60*60;
    }
    function addToCronLogs($date,$action)
    {
        $sql = "INSERT INTO `instagram.cronlogs`(`date`,`action`) VALUES ('$date','$action')";
        $this->db->query($sql); 
    }
    function searchSubscription($tagName)
    {
        $sql = "SELECT * from `instagram.subscriptions` WHERE `tagName` ='$tagName' AND `status`!= '4'";
        $query = $this->db->query($sql);
	return $query;
    }
    function getEntriesForEmailReport($date,$entryStatus){
        $fromDate = '';
        $toDate = (strtotime(date("Y-m-d H:i"))+$this->gmt);
        if ($date =="today"){
            $fromDate = (strtotime(date("Y-m-d"))+$this->gmt);
        }
        else if ($date =="weekToDate"){
            $fromDate = strtotime('last Sunday', $toDate)+$this->gmt;
        }
        else if ($date =="monthToDate"){
            $fromDate = (strtotime(date("Y-m-1"))+$this->gmt);
        }
        else{
            $fromDate = (strtotime(date("Y-1-1"))+$this->gmt);
        }
        $status = '';
        if ($entryStatus == '1'){
            $status = " AND `status`='1'";
        }
        if ($entryStatus == '2'){
            $status = " AND `status`='2'";
        }
        $sql = "SELECT * from `instagram.tagentries` WHERE `timePosted` >='$fromDate' AND `timePosted` <='$toDate' $status";
        $query = $this->db->query($sql);
	return $query->num_rows();
    }
    function getUniqueUsers($date){
        $fromDate = '';
        $toDate = (strtotime(date("Y-m-d H:i"))+$this->gmt);
        if ($date =="today"){
            $fromDate = (strtotime(date("Y-m-d"))+$this->gmt);
        }
        else if ($date =="weekToDate"){
            $fromDate = strtotime('last Sunday', $toDate)+$this->gmt;
        }
        else if ($date =="monthToDate"){
            $fromDate = (strtotime(date("Y-m-1"))+$this->gmt);
        }
        else{
            $fromDate = (strtotime(date("Y-1-1"))+$this->gmt);
        }
        $sql = "SELECT DISTINCT(`userId`) from `instagram.tagentries` WHERE `timePosted` >='$fromDate' AND `timePosted` <='$toDate' ";
        $query = $this->db->query($sql);
	return $query->num_rows();    
    }
    function getCaptions($ids){
        $id_array = (explode("-",$ids));
        $ctr=0;
        $str='';
        foreach($id_array as $id)
        {
            if ($ctr == 0)
                $str= $str." `id` ='$id' ";
            else
                $str= $str." OR `id` ='$id' ";
            $ctr++;
        }
        $sql = "SELECT `id`,`caption` from `instagram.tagentries` WHERE $str";
        $query = $this->db->query($sql);
	return $query->result();
    }
    function getNextEntries($id){
        $sql = "SELECT * from `instagram.tagentries` WHERE `id` >'$id' AND `status`=0";
        $query = $this->db->query($sql);
        return $query->num_rows();
    }
    function searchEntriesForReport($status,$dateFrom,$dateTo,$tin,$amount)
    {
        if ($amount == 0){
            $amount = 'AND `amount` >= 1  AND `amount` <=100';
        }
        else if ($amount == 1){
            $amount = 'AND `amount` >= 101 AND `amount` <=500';
        }
        else if ($amount == 2){
            $amount = 'AND `amount` >= 501 AND `amount` <=1000';
        }
        else if ($amount == 3){
            $amount = 'AND `amount` >= 1001';
        }
        else{
            $amount = '';
        }
        if ($tin == 'all'){
            $tinsql = " ";
        }else{
            $tinsql = " AND `tin` ='$tin' ";
        }
        if ($status == '')
            $sql = "SELECT * from `instagram.tagentries` WHERE `timePosted` >='$dateFrom' AND `timePosted` <='$dateTo' $tinsql  $amount AND `status`!= '4'";
        else 
            $sql = "SELECT * from `instagram.tagentries` WHERE `timePosted` >='$dateFrom' AND `timePosted` <='$dateTo' $tinsql  $amount AND `status`!= '4' AND `status` = $status";
        $query = $this->db->query($sql);
	return $query;
    }
    function searchTotProcessedForReport($dateFrom,$dateTo,$tin,$amount)
    {
        if ($amount == 0){
            $amount = 'AND `amount` >= 1  AND `amount` <=100';
        }
        else if ($amount == 1){
            $amount = 'AND `amount` >= 101 AND `amount` <=500';
        }
        else if ($amount == 2){
            $amount = 'AND `amount` >= 501 AND `amount` <=1000';
        }
        else if ($amount == 3){
            $amount = 'AND `amount` >= 1001';
        }
        else{
            $amount = '';
        }
        if ($tin == 'all'){
            $tinsql = " ";
        }else{
            $tinsql = " AND `tin` ='$tin' ";
        }
        $sql = "SELECT * from `instagram.tagentries` WHERE `timePosted` >='$dateFrom' AND `timePosted` <='$dateTo' $tinsql  $amount AND `status`> '0' OR `status` < 3";
        $query = $this->db->query($sql);
	return $query;
    }
    function searchDistinctEntriesForReport($status,$dateFrom,$dateTo,$tin,$amount)
    {
        if ($amount == 0){
            $amount = 'AND `amount` >= 1  AND `amount` <=100';
        }
        else if ($amount == 1){
            $amount = 'AND `amount` >= 101 AND `amount` <=500';
        }
        else if ($amount == 2){
            $amount = 'AND `amount` >= 501 AND `amount` <=1000';
        }
        else if ($amount == 3){
            $amount = 'AND `amount` >= 1001';
        }
        else{
            $amount = '';
        }
        if ($tin == 'all'){
            $tinsql = " ";
        }else{
            $tinsql = " AND `tin` ='$tin' ";
        }
        if ($status == '')
            $sql = "SELECT DISTINCT `userId` from `instagram.tagentries` WHERE `timePosted` >='$dateFrom' AND `timePosted` <='$dateTo' $tinsql  $amount AND `status`!= '4'";
        else 
            $sql = "SELECT DISTINCT `userId` from `instagram.tagentries` WHERE `timePosted` >='$dateFrom' AND `timePosted` <='$dateTo' $tinsql  $amount AND `status`!= '4' AND `status` = $status";
        $query = $this->db->query($sql);
	return $query;
    }
    function searchMinAndMaxId($minId,$maxId)
    {
        $sql = "SELECT * from `instagram.globaloption` WHERE `optionVar` ='$minId'";
        $query1 = $this->db->query($sql);
        $sql = "SELECT * from `instagram.globaloption` WHERE `optionVar` ='$maxId'";
        $query2 = $this->db->query($sql);
        if($query1 && $query2)
            return true;
        else {
            return false;
        }
    }
    function checkDuplicateEntry($id){
        
        $sql = "SELECT `sha1`, `timePosted`  FROM  `instagram.tagentries` WHERE `id`='$id'";
        $query = $this->db->query($sql);
        $result = $query->result();
        $sha1 = '';
        foreach($result as $row)
        {
            $sha1 = $row->sha1;
            $timePosted = $row->timePosted;
        }
        $sql = "SELECT `id`,`entryLink`  FROM  `instagram.tagentries` WHERE `sha1`='$sha1'  AND  `timePosted` < '$timePosted'";
        $query = $this->db->query($sql);
        $result = $query->result();
        $entryLink = '';
        foreach($result as $row)
        {
            if ($id != $row->id)
            $entryLink = $row->entryLink;
        }
        return $entryLink;
    }
    function checkIfProcessed($id){
        $sql = "SELECT * from `instagram.tagentries` WHERE `id`='$id'";
        $query = $this->db->query($sql);
	return $query->result();
    }
    function checkIfReachedMaxEntriesPerDay($username,$dateToday,$id){
        $ret = 'true';
        $sql = "SELECT a.tagName AS  `tagName` , b.maxValidEntries AS  `maxValidEntries` FROM  `instagram.tagentries` a,  `instagram.subscriptions` b WHERE a.id ='$id' AND a.tagName = b.tagName AND b.status !=4";
        $query = $this->db->query($sql);
        if ($query){
            $result = $query->result();
            $tagName = '';
            $maxValidEntries = 0;
            foreach($result as $row)
            {
                $tagName = $row->tagName;
                $maxValidEntries = $row->maxValidEntries;
            }
            if (($maxValidEntries == 0)||($maxValidEntries == '0')){              
                $ret = 'false';              
            }
            else{               
                $sql = "SELECT * from `instagram.tagentries` WHERE `username` ='$username' AND `timePosted` >='$dateToday' AND `status`=1 AND `tagName`='$tagName'";
                $query = $this->db->query($sql);
                if ($query->num_rows()<$maxValidEntries)
                    $ret = 'false';
            }
        }
	return $ret;
    }
    function getNoEnabledSubscriptions()
    {
        $sql = "SELECT * from `instagram.subscriptions` WHERE `status` ='0' OR `status` ='2'";
        $query = $this->db->query($sql);
	return $query;
    }
    function getNoQueuedSubscriptions()
    {
        $dateNow=((strtotime(date("Y-m-d H:i:s"))+$this->gmt) );// get current datetime
        $flrDate = floor($dateNow/3600) * 3600;
        //$sql = "SELECT * from `instagram.subscriptions` WHERE `status`!= '4' AND MOD(  `status` , 2 ) != 0 AND (startDateTime` > '$dateNow' OR FLOOR(  `startDateTime` /3600 ) *3600 = $flrDate)";
        $sql = "SELECT * FROM  `instagram.subscriptions` WHERE  `status` !=  '4' AND MOD(  `status` , 2 ) !=0 AND (`startDateTime` >  '$dateNow' OR FLOOR(  `startDateTime` /3600 ) *3600 ='$flrDate')";
        //$sql = "SELECT * FROM  `instagram.subscriptions` WHERE  `startDateTime` -  `startDateTime` <0";
        $query = $this->db->query($sql);
	//return $sql;
        return $query;
    }
    function getNoSubscriptionWhereStatus($status)
    {
        $sql = "SELECT * from `instagram.subscriptions` WHERE `status` ='$status' AND `status`!= '4'";
        $query = $this->db->query($sql);
	return $query;
    }
    function getEntriesStatus()
    {
        $sql = "SELECT * from `instagram.tagentrystatus`";
        $query = $this->db->query($sql);
	return $query->result();
    }
    function getInvalidReplies()
    {
        $sql = "SELECT * from `instagram.invalidreplies`";
        $query = $this->db->query($sql);
	return $query->result();
    }
    function getSubscriptions()
    {
        $sql = "SELECT * from `instagram.subscriptions` WHERE  `status`!= '4'";
        $query = $this->db->query($sql);
	return $query->result();
    }
    function getSubscriptionStatus()
    {
        $sql = "SELECT * from `instagram.subscriptionstatus` WHERE `status`!= '4'";
        $query = $this->db->query($sql);
	return $query->result();
    }
    function deleteSubscription($field, $id)
    {
        $sql = "DELETE from `instagram.subscriptions` WHERE `$field` ='$id'";
        $query = $this->db->query($sql);
	return $query;
    }
    function getEntries()
    {
        $sql = "SELECT * from `instagram.tagentries`";
        $query = $this->db->query($sql);
	return $query->result();
    }
    function getEntriesForValidation()
    {
        $sql = "SELECT * from `instagram.tagentries` WHERE `status`='0' ORDER BY `timePosted` ASC";
        $query = $this->db->query($sql);
	return $query->result();
    }	
    function validateEntry($id,$remarks,$date,$processedBy,$status,$code)
    {
        $sql = "UPDATE `instagram.tagentries` SET  `code`='$code',`timeProcessed`='$date' ,`processedBy`='$processedBy' ,`status`='$status' ,`remarks`='$remarks'  WHERE `id`='$id'";
        $query = $this->db->query($sql);
	return $query;
    }
    function insertDummyEntry($hashtag,$username,$tin,$orno,$amount,$mediaid,$timePosted,$status){
        $caption = '#'.$hashtag.' '.$tin.' '.$orno.' '.$amount;
	$sql = "INSERT IGNORE INTO `instagram.tagentries`(`username`,  `caption`,`timePosted`,`status`,`tin`,`orNo`,`amount`,`tagName`,`mediaId`)
		VALUES('$username','$caption','$timePosted','$status','$tin','$orno','$amount','$hashtag','$mediaid')";
            
        $query2 = $this->db->query($sql);
        if($query2)
            return true;
        else {
            return false;
        }
    }
    function getEntriesWhere($tagName,$dateFrom,$dateTo,$dateFilter,$entryStatus)
    { 
        $tagnamequery = '';
        if ($tagName != 'All')
           $tagnamequery = " AND a.tagName = '$tagName'";
        $field = 'a.'.$dateFilter;
        $sql = "SELECT											
                a.id,
                a.username,
                a.caption,
                a.entryLink,
                a.timePosted,
                a.timeProcessed,
                a.processedBy,
                b.description AS `desc`,
                a.status,
                a.remarks,
                a.tin,
                a.orNo,
                a.amount,
                a.code,
                a.tagName,
                c.description
                FROM  `instagram.tagentries` a,  `instagram.tagentrystatus` b,`instagram.invalidreplies` c
                WHERE a.status = b.status AND $field >= '$dateFrom' AND $field <= '$dateTo' AND a.remarks = c.tid
                $tagnamequery
                AND a.status = '$entryStatus'
                ";
        $query = $this->db->query($sql);
	return $query;
    }
    function getAllEntriesWhere($tagName,$dateFrom,$dateTo,$dateFilter)
    {
        //$sql = "SELECT * from `instagram.tagentries` WHERE ";
        $tagnamequery = '';
        if ($tagName != 'All')
           $tagnamequery = " AND a.tagName = '$tagName'";
        $field = 'a.'.$dateFilter;
        $sql = "SELECT											
                a.id,
                a.username,
                a.caption,
                a.entryLink,
                a.timePosted,
                a.timeProcessed,
                a.processedBy,
                b.description AS `desc`,
                a.status,
                a.remarks,
                a.tin,
                a.orNo,
                a.amount,
                a.code,
                a.tagName,
                c.description
                FROM  `instagram.tagentries` a,  `instagram.tagentrystatus` b ,`instagram.invalidreplies` c
                WHERE a.status = b.status AND $field >= '$dateFrom' AND $field <= '$dateTo' $tagnamequery  AND a.remarks = c.tid";
        $query = $this->db->query($sql);
	return $query;
    }
                  
    function addEntryWithValidation($username,$userId, $caption,$entryLink,$timePosted,$tagName,$mediaId)
    {
        $dateToday = strtotime(date("Y-m-d H:i:s"))+$this->gmt;
        $result = true;
        $strregex = $this->config->item('entryFormat');
        if (preg_match($strregex, $caption)) {
            $parts = preg_split('/\s+/', trim($caption));
            $tin = $parts[3];//ltrim($parts[3], '0');
            $orNo = $parts[4];
            $amount = $parts[5];
            $str = trim(mb_strtolower($tin)).trim($orNo);
            $str = preg_replace( '/\s+/', ' ', $str );
            $sha1 =  sha1($str);
            $sql = "INSERT IGNORE INTO `instagram.tagentries`(`username`, `userId`, `caption`,`entryLink`,`timePosted`,`status`,`tin`,`orNo`,`amount`,`tagName`,`mediaId`,`sha1`,`remarks`) VALUES('$username','$userId','$caption','$entryLink','$timePosted','0','$tin','$orNo','$amount','$tagName','$mediaId','$sha1','3')";
            $query1 =$this->db->query($sql);
        } else {
            $sql = "INSERT IGNORE INTO `instagram.tagentries`(`username`,   `userId`,`caption`,`entryLink`,`timePosted`,`timeProcessed`,`processedBy`,`remarks`,`status`,`tagName`,`mediaId`) VALUES('$username','$userId','$caption','$entryLink','$timePosted','$dateToday','WebTool','3','2','$tagName','$mediaId')";
            $query1 = $this->db->query($sql);
        }
        if (!$query1)
        {
            $result = false;
        }
        return $result;
    }
    function revokeEntry($id)
    {
        $sql = "UPDATE `instagram.tagentries` SET `status`=0,`timeProcessed`='',`processedBy`='',`remarks`=3 WHERE `id`='$id'";
        $query = $this->db->query($sql);
        return $query;
    }
    function validateSearchedEntry($id,$caption)
    {
        $dateToday = strtotime(date("Y-m-d H:i:s"))+$this->gmt;
        $result = true;
        $strregex = $this->config->item('entryFormat');
        if (preg_match($strregex, $caption)) {
            $parts = preg_split('/\s+/', trim($caption));
            $tin = $parts[3];//ltrim($parts[3], '0');
            $orNo = $parts[4];
            $amount = $parts[5];
            $str = trim(mb_strtolower($tin)).trim($orNo);
            $str = preg_replace( '/\s+/', ' ', $str );
            $sha1 =  sha1($str);
            $sql = "UPDATE `instagram.tagentries` SET `sha1`='$sha1', `tin`='$tin',`status`= '0',`orNo`='$orNo',`amount`='$amount' WHERE `id`='$id'";
            $query1 =$this->db->query($sql);
        } else {
            $sql =  "UPDATE `instagram.tagentries` SET  `status`= '2' WHERE `id`='$id'";
            $query1 = $this->db->query($sql);
        }
        if (!$query1)
        {
            $result = false;
        }
        return $result;
    }
    function addEntryWithoutValidation($username, $userId,$caption,$entryLink,$timePosted,$tagName,$mediaId)
    {
        $dateToday = strtotime(date("Y-m-d H:i:s"))+$this->gmt;
        $result = true;
        $sql = "INSERT IGNORE INTO `instagram.tagentries`(`username`,  `userId`, `caption`,`entryLink`,`timePosted`,`timeProcessed`,`processedBy`,`remarks`,`status`,`tagName`,`mediaId`) VALUES('$username','$userId','$caption','$entryLink','$timePosted','$dateToday','WebTool','3','3','$tagName','$mediaId')";
        $query1 = $this->db->query($sql);
        if (!$query1)
        {
            $result = false;
        }
        return $result;
    }
    function updateSubscriptionStatus($hashtag,$status)
    {
        $sql = "UPDATE `instagram.subscriptions` SET `status`='$status' WHERE `tagName`='$hashtag' AND `status`!= '4'";
        return $this->db->query($sql);
    }
    function updateSubscriptionId($hashtag,$subsId)
    {
        $sql = "UPDATE `instagram.subscriptions` SET `subscriptionId`='$subsId' WHERE `tagName`='$hashtag' AND `status`!= '4'";
        return $this->db->query($sql);
    }
    function updateSubscription($id,$status,$subscriptionId)
    {
        $sql = "UPDATE `instagram.subscriptions` SET `status`='$status',`subscriptionId`='$subscriptionId' WHERE `id`='$id'";
        return $this->db->query($sql);
    }
    function updateTagAction($field, $tag,$dateEnabled)
    {
        $sql = "UPDATE `instagram.subscriptions` SET `$field`='$dateEnabled' WHERE `tagName`='$tag' AND `status`!= '4'";
        return $this->db->query($sql);
    }
    function updateTagExpiration( $tag,$startDateTime,$endDateTime)
    {
        $sql = "UPDATE `instagram.subscriptions` SET `startDateTime`='$startDateTime',`endDateTime`='$endDateTime' WHERE `tagName`='$tag' AND `status`!= '4'";
        return $this->db->query($sql);
    }
    function getMaxDate($hashtag)
    {
        $sql = "SELECT MAX(  `timePosted` ) AS maxDate FROM  `instagram.tagentries` WHERE `tagName`='$hashtag'";
        $query = $this->db->query($sql);
        $result = $query->result();
        $maxDate = 0;
        foreach($result as $row)
        {
            if (isset($row->maxDate))
            $maxDate = $row->maxDate;
        }
        return $maxDate;
    }
    function getDateEnabled($hashtag)
    {
        $sql = "SELECT `dateEnabled` FROM  `instagram.subscriptions` WHERE `tagName`='$hashtag' AND `status`!= '4'";
        $query = $this->db->query($sql);
        $result = $query->result();
        foreach($result as $row)
        {
            $dateEnabled = $row->dateEnabled;
        }
        return $dateEnabled;
    }
    function getDateDisabled($hashtag)
    {
        $sql = "SELECT `dateDisabled` FROM  `instagram.subscriptions` WHERE `tagName`='$hashtag' AND `status`!= '4'";
        $query = $this->db->query($sql);
        $result = $query->result();
        foreach($result as $row)
        {
            $dateDisabled= $row->dateDisabled;
        }
        return $dateDisabled;
    }
    
    function deleteMinAndMaxTagId($minId,$maxId){
        $sql = "DELETE FROM `instagram.globaloption` WHERE `optionVar` =  '$minId'";  
        $query1 = $this->db->query($sql);
        $sql = "DELETE FROM `instagram.globaloption` WHERE `optionVar` =  '$maxId'";  
        $query2 = $this->db->query($sql);
        if ($query1 &&$query2){
            return true;
        }
        else
            return false;
         
    }
    
    //SELECT MAX(  `timePosted` ) AS maxDate FROM  `instagram.tagEntries`
    function subscribe($tagName,$subscriptionId,$maxValidEntries,$status,$startDateTime,$endDateTime,$createdOn,$createdBy)
    {
        $sql = "INSERT IGNORE INTO `instagram.subscriptions`(`tagName`,  `subscriptionId`,`status`,`startDateTime`,`endDateTime`,`maxValidEntries`,`createdBy`,`createdOn`) VALUES     ('$tagName','$subscriptionId','$status','$startDateTime','$endDateTime','$maxValidEntries','$createdBy','$createdOn')";  
        $query1 = $this->db->query($sql);
        if ($query1)
        { 
           return true;
        }      
    }
    function updateQueuedSubscription($tagName,$status,$startDateTime,$endDateTime){
        $sql = "UPDATE `instagram.subscriptions` SET    `status`= '$status',`startDateTime`= '$startDateTime', `endDateTime`='$endDateTime' WHERE `tagName`='$tagName' AND `status`!= '4'";  
        return $this->db->query($sql);
     
    }
    function createSubscriptionMinIdMaxId($minId,$maxId)
    {
        $sql = "INSERT IGNORE INTO `instagram.globaloption`(`optionVar`,`optionVal`) VALUES ('$minId','0')";
        $query2 = $this->db->query($sql);
        $sql = "INSERT  IGNORE INTO `instagram.globaloption`(`optionVar`,`optionVal`) VALUES ('$maxId','0')";
        $query3 = $this->db->query($sql);   
        if (($query2)&&($query3))
        {
            return true;
        }
        else
            return false;
    }

    function getMinId($tagId)
    {
        $sql = "SELECT * from instagram.tags WHERE optionVar ='$tagId'";
        $query = $this->db->query($sql);
	return $query->result();
    }
    function updateMinId($optionVar,$minId)
    {
        $sql = "UPDATE `instagram.globaloption` SET `optionVal`='$minId' WHERE `optionVar`='$optionVar'";
        $query = $this->db->query($sql);
    }

}
?>
