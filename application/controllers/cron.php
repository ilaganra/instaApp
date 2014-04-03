<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');
/**
Controller:     Instagram
Description:    Manages all api-related to instagram actions
*/
class cron extends CI_Controller {
	function __construct()
	{		
            parent::__construct();
            date_default_timezone_set('Asia/Manila');
            $this->load->helper('url');
            $this->load->model('Instagram_model');
            $this->load->model('user_model');
            $this->load->model('Web_model');
            $this->load->library('instagram_api');
            $this->base = $this->config->item('base_url');
            $this->gmt = 8*60*60;
            $this->currentDateTime = ((strtotime(date("Y-m-d H:i:s"))+$this->gmt) );
	}
        /**
        Function:       disableSubscription
        Description:    Disable selected subscription/tag
        */
        public function disableSubscription($subsId,$hashtag,$status)
        {
            $dateNow =((strtotime(date("Y-m-d H:i:s"))+$this->gmt) );//get current datetime
            $dateEnabled = $this->Instagram_model->getDateEnabled($hashtag);//get date the tag was enabled
            $dateDifference = $dateNow - $dateEnabled;//get date difference between today and the date the tag was enabled to check if in range for disabling 
            $subscriptionStatus = $this->Instagram_model->getSubscriptionStatus();//get label of subscription status
            //Delete subscription from the list of active tag subscriptions on instagram and change status to disabled in database
            $disableSubscription = json_decode($this->instagram_api->deleteSubscription($subsId),true);
            //If delete subscription on instagram succeeded proceed to update status and deleting of min and max tagid, else throw a status of danger
            if ((isset($disableSubscription['meta']['code'])) &&($disableSubscription['meta']['code'] == 200))
            {
                $newStatus = $status+1;
                $updateStatus = $this->Instagram_model->updateSubscriptionStatus($hashtag,$newStatus);
                //Delete min and max tag since this will be remake when enabled
                $deleteMinAndMaxTagId = $this->Instagram_model->deleteMinAndMaxTagId($subsId.'minId',$subsId.'maxId');
                //If update status and deleting min and max tag id in database succeeded throw success status to the user and set date for date disabled
                if ($updateStatus && $deleteMinAndMaxTagId)
                {
                    $result['status']="success";
                    $result['description'] = "Successfully disabled the tag '$hashtag'"; 
                    $result['statusDesc'] = $subscriptionStatus[$newStatus]->description;
                    $this->Instagram_model->updateTagAction('dateDisabled', $hashtag,strtotime(date("Y-m-d H:i:s"))+$this->gmt);
                }
                else
                {
                    $result['status']="danger";
                    $result['description'] =" Not successful on disabling tag '$hashtag'";     
                }                            
            }
            else
            {
                $result['status']="danger";
                $result['description'] =  $disableSubscription['meta']['code']." Not successful on disabling tag '$hashtag'";     
            }
            echo json_encode($result);     
        }
        /**
        Function:       getEntries
        Description:    Get entries that contains the tag desired starting from that day to the actual datetime
        */
        public function getEntries($tagName,$optionVar,$startDate,$endDate,$tagStatus)
        {     
            $max_id = false;
            $getResultCtr = 0;
            $resultNo=0;
            $addEntry =true;
            $breakStatus = 0;
            do
            {
                $getEntries = json_decode($this->instagram_api->getTag($tagName,false,false,$max_id),true);
                foreach ($getEntries['data'] as $entry)
		{
                    $timePosted = (($entry['created_time'])+$this->gmt);
                    if (($timePosted >= $startDate )&&($timePosted <= $endDate ))
                    {
                        if (isset($entry['caption']['text']))
                            $caption = $entry['caption']['text'];
                        else
                            $caption = "";
                        $mediaId = $entry['id'];
                        //$allEntries .= $entry['user']['username']." ". $caption." ".$entry['link']." ".$timePosted." ".$tagName." ".$mediaId." /r/n";
                        if ($tagStatus < 2)
                            $addEntry=$this->Instagram_model->addEntryWithValidation($entry['user']['username'], $entry['user']['id'],$caption,$entry['link'],$timePosted,$tagName,$mediaId); 
                        else
                            $addEntry=$this->Instagram_model->addEntryWithoutValidation($entry['user']['username'], $entry['user']['id'],$caption,$entry['link'],$timePosted,$tagName,$mediaId);    
                        if ($addEntry == false)
                        {
                            break;
                        }
                        $resultNo++;                                          
                    }
                    else
                    {
                        $breakStatus++;
                    }
                }
                if (($addEntry == false)||(($breakStatus >= 100)))
                {
                    break;
                }
                if ($getResultCtr==0)
                {
                    if (isset($getEntries['pagination']['min_tag_id'])) 
                    {
                        $min_id = $getEntries['pagination']['min_tag_id'];
                        $this->Instagram_model->updateMinId($optionVar,$min_id);
                    }
                }
                $getResultCtr++;
                if (isset($getEntries['pagination']['next_max_id'])) 
                {
                    $max_id = $getEntries['pagination']['next_max_id'];
                }
                else
                    break;
            }while(true);	
            return $addEntry;           
        }
        /**
        Function:       Email Report
        Description:    Send emails containing business report requirements
        */
        public function emailReport(){
            $this->load->config('email');
            $this->Instagram_model->addToCronLogs($this->currentDateTime,'emailReport');
            $subject = "PSRInstagram Daily E-mail Report";
            $mailDate = gmdate("M. d, Y g:i a",$this->currentDateTime);
            $body       ="<p><b>Dear Everyone,</b><br/><br/>Please see the daily report of PSR instagram. As of today, $mailDate, the summary of entries received is as follows: </p><br/>";
            $data['today'][0]=$this->Instagram_model->getEntriesForEmailReport('today','');
            $data['today'][1]=$this->Instagram_model->getEntriesForEmailReport('today',1);
            $data['today'][2]=$this->Instagram_model->getEntriesForEmailReport('today',2);
            $data['today'][3]= $data['today'][1]+$data['today'][2];
            $data['weekToDate'][0]=$this->Instagram_model->getEntriesForEmailReport('weekToDate','');
            $data['weekToDate'][1]=$this->Instagram_model->getEntriesForEmailReport('weekToDate',1);
            $data['weekToDate'][2]=$this->Instagram_model->getEntriesForEmailReport('weekToDate',2);
            $data['weekToDate'][3]=$data['weekToDate'][1]+$data['weekToDate'][2];
            $data['monthToDate'][0]=$this->Instagram_model->getEntriesForEmailReport('monthToDate','');
            $data['monthToDate'][1]=$this->Instagram_model->getEntriesForEmailReport('monthToDate',1);
            $data['monthToDate'][2]=$this->Instagram_model->getEntriesForEmailReport('monthToDate',2);
            $data['monthToDate'][3]=$data['monthToDate'][1]+$data['monthToDate'][2];
            $data['yearToDate'][0]=$this->Instagram_model->getEntriesForEmailReport('yearToDate','');
            $data['yearToDate'][1]=$this->Instagram_model->getEntriesForEmailReport('yearToDate',1);
            $data['yearToDate'][2]=$this->Instagram_model->getEntriesForEmailReport('yearToDate',2);
            $data['yearToDate'][3]=$data['yearToDate'][1]+$data['yearToDate'][2];
            $data['uniqueUsers'][0]=$this->Instagram_model->getUniqueUsers('today');
            $data['uniqueUsers'][1]=$this->Instagram_model->getUniqueUsers('weekToDate');
            $data['uniqueUsers'][2]=$this->Instagram_model->getUniqueUsers('monthToDate');
            $data['uniqueUsers'][3]=$this->Instagram_model->getUniqueUsers('yearToDate');
            $message = $body.$this->Web_model->emailReport($data);
            $this->Web_model->mail($this->config->item('cronEmailRecipient'),$subject,$message,$mailDate);
            //exit;
        }
        /**
        Function:       addEntryForTesting
        Description:    for testing purposes only
        */
        public function addEntryForTesting()
        {
			$hashtag = $_GET['hashtag'];
			$username =$_GET['username'];
		//	$entrylink=$_GET['entrylink'];
			$tin=$_GET['tin'];
			$orno=$_GET['orno'];
			$orno=$_GET['orno'];
			$amount=$_GET['amount'];
			$mediaid=$_GET['mediaid'];
			$datePosted=strtotime($_GET['datePosted'])+$this->gmt;
			$status=$_GET['status'];
			//$addEntry = $this->Instagram_model->getSubscriptions();
            
			//echo $hashtag." ".$username	." ".$tin." ".$orno." ".$amount." ".$mediaid." ".$datepickerFrom." ".$datepickerTo." ".$status;
			$addEntry = $this->Instagram_model->insertDummyEntry($hashtag,$username,$tin,$orno,$amount,$mediaid,$datePosted,$status);
                        if ($addEntry)
				echo 'insert ok';
			else
				echo 'insert not ok';
	}
        
        /**
        Function:       checkSubscriptionsStatus
        Description:    Check if subscription must be enabled/disabled
        */
        public function checkSubscriptionsStatus()//per hour
        {
             $this->Instagram_model->addToCronLogs($this->currentDateTime,'checkTagStat');
             $subscriptions = $this->Instagram_model->getSubscriptions();
             $dateToday =  strtotime(date("Y-m-d"))+$this->gmt;
             $exactDateToday = strtotime(date("Y-m-d H:i"))+$this->gmt;
             foreach($subscriptions as $subscription)
             {
                 $id = $subscription->id;
                 $tagName = $subscription->tagName;
                 $subscriptionId = $subscription->subscriptionId;
                 $status = $subscription->status;
                 $startDateTime = $subscription->startDateTime;
                 $endDateTime = $subscription->endDateTime;
                 $result='';
                 $roundSdate = floor($startDateTime/3600)*3600;
                 $roundEdate = floor($endDateTime/3600)*3600;
                 $roundExactDate =  floor($exactDateToday/3600)*3600;
                 echo $tagName. " ".$roundSdate." ".$roundEdate." ".$roundExactDate." <br/>";
                 if ($status % 2 == 0)//if active,disable
                 {
                    echo "edate = $roundEdate and exactdate is $roundExactDate </br>";
                    if (($roundEdate == $roundExactDate)|| (($roundExactDate - 3600) == $roundEdate))
                    {
                        echo  'for disabling</br>';
                        $this->disableSubscription($subscriptionId,$tagName,$status);
                    }
                 }
                 else{//if not active, check startDateTime to enable
                    if ($roundSdate == $roundExactDate)
                    {
                        echo $tagName.'will be activated</br>';
                        $addSubsResult = json_decode($this->instagram_api->addSubscription($tagName),true);
                        print_r($addSubsResult);
                        if ((isset($addSubsResult['meta']['code'])) &&($addSubsResult['meta']['code'] == 200))
                        {
                            $newStatus = $status - 1;
                            $subscriptionId = $addSubsResult['data']['id'];
                            $updateSubsId = $this->Instagram_model->updateSubscriptionId($tagName,$subscriptionId);
                            $updateStatus = $this->Instagram_model->updateSubscriptionStatus($tagName,$newStatus);
                            $updateExpiry = $this->Instagram_model->updateTagExpiration($tagName, $startDateTime,$endDateTime);
                            $updateDateEnabled = $this->Instagram_model->updateTagAction('dateEnabled', $tagName,strtotime(date("Y-m-d H:i:s"))+$this->gmt);
                            $createSubscriptionMinIdMaxId = $this->Instagram_model->createSubscriptionMinIdMaxId($subscriptionId."minId",$subscriptionId."maxId");
                            if (($updateStatus)&&($updateExpiry)&&($updateSubsId)&&($updateDateEnabled)&&($createSubscriptionMinIdMaxId))
                            {
                                $dateToday = strtotime(date("Y-m-d"))+$this->gmt;
                                $maxDate = $this->Instagram_model->getMaxDate($tagName);
                                $sDate = ($maxDate > $dateToday )? $maxDate:$dateToday; 
                                $eDate = strtotime(date("Y-m-d H:i"))+$this->gmt; 

                                $getEntries = $this->getEntries($tagName,$subscriptionId."minId",$sDate,$eDate,$newStatus);
                                if ($getEntries == true)
                                {
                                    $subscriptionStatus = $this->Instagram_model->getSubscriptionStatus();
                                    $result['status']="success";
                                    $result['description'] = "Successfully enabled the tag '$tagName'";   
                                    $result['subsId'] = $subscriptionId;
                                    $result['statusDesc'] = $subscriptionStatus[$newStatus]->description;
                                    $result['newStartDate'] = gmdate("M. d, Y  g:i a", ($startDateTime));
                                    $result['newEndDate'] = gmdate("M. d, Y  g:i a", ($endDateTime));
                                }
                                else{
                                    $result['status']="danger";
                                    $result['description'] = "Something happened when gathering entries";    
                                }
                            }
                            else
                            {
                                $result['status']="danger";
                                $result['description'] = "Something happened when adding the tag '$tagName' to database";
                            }
                        } 
                    }             
                }
            }echo json_encode($result);
        }
        
        public function checkIfActiveTagRunning(){
            $this->Instagram_model->addToCronLogs($this->currentDateTime,'checkTagRun');
            $getSubscriptions = $this->Instagram_model->getNoEnabledSubscriptions();
            $subscriptions = $getSubscriptions->result();
            $activeSubscriptions = json_decode($this->instagram_api->getSubscriptions(),true);
            $dateToday =  strtotime(date("Y-m-d"))+$this->gmt;
            foreach($subscriptions as $subscription)
            {
                $tagName = $subscription->tagName;
                $subscriptionId = $subscription->subscriptionId;
                $status = $subscription->status;
                $notFound = true;
                //print_r($activeSubscriptions);
                foreach ($activeSubscriptions['data'] as $sub_array){
                    if (in_array($tagName, $sub_array)){
                        $notFound = false;
                        break;
                    }
                }
                if ($notFound){
                        $addSubsResult = json_decode($this->instagram_api->addSubscription($tagName),true);
                        if ((isset($addSubsResult['meta']['code'])) &&($addSubsResult['meta']['code'] == 200))
                        {
                            $maxDate = $this->Instagram_model->getMaxDate($tagName);
                            $sDate = ($maxDate > $dateToday )? $maxDate:$dateToday; 
                            $eDate = strtotime(date("Y-m-d H:i"))+$this->gmt; 
                            $getEntries = $this->getEntries($tagName,$subscriptionId."minId",$sDate,$eDate,$status);
                        }
                    }
            }
        }
}
