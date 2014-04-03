<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');
/**
Controller:     Instagram
Description:    Manages all api-related to instagram actions
*/
class instagram extends CI_Controller {
	function __construct()
	{		
            parent::__construct();
            $this->load->helper('url');
            $this->load->model('Instagram_model');
            $this->load->model('user_model');
            $this->load->model('Web_model');
            $this->load->library('instagram_api');
            $this->base = $this->config->item('base_url');
            $this->gmt = 8*60*60;
            date_default_timezone_set('Asia/Manila');
            $this->output->set_header("Cache-Control: no-store, no-cache, must-revalidate");
            if(!($this->session->userdata('logged_in')))//redirect user to login page if user not logged in
            {
                redirect('/users');
            }
            else{
                $this->session_data = $this->session->userdata('logged_in');
                $this->createdBy = $this->session_data['username']; 
                $this->id = $this->session_data['id']; 
                $this->currentDateTime = ((strtotime(date("Y-m-d H:i:s"))+$this->gmt) );
            }
	}
        public function checkSession($session){
            $session_data = $this->session->userdata('logged_in');
            if ($session_data[$session] != 1)
                redirect(site_url('users'));
        }
        /**************************************************************************************************************************
        * Subscriptions Management API 
        **************************************************************************************************************************/
        
        /**
        Function:       subscriptions (main page)
        Description:    Subscription/Tag Management: enabling/disabling,adding and deleting
        */
        public function subscriptions()
	{
            $this->checkSession('subscription'); //check if user have access right to the page
            //For web outline
            $data['header'] = $this->Web_model->getHeader('Subscription Mgt');
            $data['navBar'] = $this->Web_model->getNavBar();
            $data['footer'] = $this->Web_model->getFooter();
            $data['base'] = $this->base;
            
            $data['gmt'] = $this->gmt;//for date adjustment
            //Set starting date of input date fields to the next hour
            $data['dateToday'] =((strtotime(date("Y-m-d H:i:s"))+$this->gmt) );
            $plus_one_hour = $data['dateToday'] + 3600; 
            $next_hour = floor($plus_one_hour / 3600) * 3600; 
            $data['startDate'] = gmdate('Y-m-d H:i',$next_hour);
            $data['subscriptions'] = $this->Instagram_model->getSubscriptions();//get all enabled/disabled subscriptions
            $data['subscriptionStatus'] = $this->Instagram_model->getSubscriptionStatus();//get all label of subscription status
            $this->load->view('subscriptions',$data);
	}
        /**
        Function:       disableSubscription
        Description:    Disable selected subscription/tag
        */
        public function disableSubscription()
        {
            $this->checkSession('subscription'); //check if user have access right to the page
            $subsId = $_POST['subsId'];
            $hashtag = $_POST['hashtag'];
            $status = $_POST['status'];
            $dateNow =((strtotime(date("Y-m-d H:i:s"))+$this->gmt) );//get current datetime
            $dateEnabled = $this->Instagram_model->getDateEnabled($hashtag);//get date the tag was enabled
            $dateDifference = $dateNow - $dateEnabled;//get date difference between today and the date the tag was enabled to check if in range for disabling 
            $subscriptionStatus = $this->Instagram_model->getSubscriptionStatus();//get label of subscription status
            //If difference between date enabled and date today is within range, disable subscription
            if ($dateDifference >= $this->config->item('maxHourTagAction') ) 
            {
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
                        //Insert action to history
                        $histData[0]['field'] = 'action'; 
                        $histData[0]['value'] = 'disableSubscription'; 
                        $histData[1]['field'] = 'userId'; 
                        $histData[1]['value'] = $this->id;
                        $histData[2]['field'] = 'tagName'; 
                        $histData[2]['value'] = $hashtag; 
                        $histData[3]['field'] = 'tagStatus'; 
                        $histData[3]['value'] = $status; 
                        $histData[4]['field'] = 'dateProcessed'; 
                        $histData[4]['value'] = $dateNow; 
                        $this->user_model->insertHistory($histData);
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
            }
            else{
               $result['status']="warning";
               $result['description'] = "Subscriptions can be enabled/disabled after ".$this->config->item('maxHourTagAction')." seconds of action. <br/>".
                                        "This tag has just been enabled on ".gmdate("M. d, Y  g:i a",$dateEnabled).".<br/>".
                                        "You can disable this tag on ".gmdate("M. d, Y  g:i a",$dateEnabled + $this->config->item('maxHourTagAction')).".<br/>";
            }
            echo json_encode($result);   
        }
        /**
        Function:       enableSubscription
        Description:    Enable selected subscription/tag
        */
        public function enableSubscription()
        {
            $this->checkSession('subscription'); //check if user have access right to the page
            if (!((isset($_POST['hashtag'])) &&(isset($_POST['startDate']))))
                redirect('users');
            $hashtag = $_POST['hashtag'];
            $startDate = $_POST['startDate'];
            $endDate = $_POST['endDate'];
            $status = $_POST['status'];
            $dateNow =((strtotime(date("Y-m-d H:i:s"))+$this->gmt) );// get current datetime
            $dateDisabled = $this->Instagram_model->getDateDisabled($hashtag);// get the date the tag was disabled
            $dateDifference = $dateNow - $dateDisabled;// store date difference between datedisabled and current datetime to check if passed range
            //Create the preferred range for start date and end date of 
            $startDateValidFormat = (DateTime::createFromFormat("Y-m-d H:i", $startDate));
            $endDateValidFormat = (DateTime::createFromFormat("Y-m-d H:i", $endDate));
            
            if ($startDateValidFormat && $endDateValidFormat){
                $startDate = strtotime($startDate)+$this->gmt;
                $endDate = strtotime($endDate)+$this->gmt; 
            }
            if (($startDateValidFormat ==false) || ($endDateValidFormat==false))
            {
                $result['status']="warning";
                $result['description'] = "Please check your date format.";   
            }
            else if ($startDate >= $endDate){
                $result['status']="warning";
                $result['description'] = "Start Date must be less than End date.";   
            }
            else if($dateDifference < $this->config->item('maxHourTagAction') ){
                $result['status']="warning";
                $result['description'] = "Subscriptions can be enabled/disabled after ".$this->config->item('maxHourTagAction')." seconds of action. <br/>".
                                        "This tag has just been disabled  on ".gmdate("M. d, Y  g:i a",$dateDisabled).".<br/>".
                                        "You can enable this tag on ".gmdate("M. d, Y  g:i a",$dateDisabled + $this->config->item('maxHourTagAction')).".<br/>";
            }
            else{
                $updateSubsId = $this->Instagram_model->updateQueuedSubscription($hashtag,$status,$startDate,$endDate);
                if ($updateSubsId){
                    $result['status']="success";
                    $result['description'] = "Successfully enabled the tag '$hashtag' for queue";  
                    $result['statusDesc'] = '(Queue)';
                    $result['newStartDate'] = gmdate("M. d, Y  g:i a", ($startDate));
                    $result['newEndDate'] = gmdate("M. d, Y  g:i a", ($endDate));    
                    //Insert action to history
                    $histData[0]['field'] = 'action'; 
                    $histData[0]['value'] = 'enableSubscription'; 
                    $histData[1]['field'] = 'userId'; 
                    $histData[1]['value'] = $this->id;
                    $histData[2]['field'] = 'tagName'; 
                    $histData[2]['value'] = $hashtag; 
                    $histData[3]['field'] = 'sdate'; 
                    $histData[3]['value'] = $startDate;
                    $histData[4]['field'] = 'edate'; 
                    $histData[4]['value'] = $endDate; 
                    $histData[5]['field'] = 'tagStatus'; 
                    $histData[5]['value'] = $status; 
                    $histData[6]['field'] = 'dateProcessed'; 
                    $histData[6]['value'] = $dateNow; 
                    $this->user_model->insertHistory($histData);
                }
                else{
                    $result['status']="danger";
                    $result['description'] = "Unsuccessful enabling in queue the tag,'$hashtag'";   
                }
            }
            echo json_encode($result);
        }
        /**
        Function:       deleteSubscription
        Description:    Delete selected subscription/tags (only queued subscriptions),
        */
        public function deleteSubscription()
        {
            $this->checkSession('subscription'); //check if user have access right to the page
            if (!(isset($_POST['hashtag']))) 
                redirect('users');
            $hashtag = $_POST['hashtag'];
            //update status of subscription to 'deleted'
            $deleteSubscription = $this->Instagram_model->updateSubscriptionStatus($hashtag,'4');
            if ($deleteSubscription)
            {
                $result['status']="success";
                $result['description'] = "Successfully deleted the tag '$hashtag'.";      
                //Insert action to history
                $histData[0]['field'] = 'action'; 
                $histData[0]['value'] = 'deleteSubscription'; 
                $histData[1]['field'] = 'userId'; 
                $histData[1]['value'] = $this->id;
                $histData[2]['field'] = 'tagName'; 
                $histData[2]['value'] = $hashtag; 
                $histData[3]['field'] = 'dateProcessed'; 
                $histData[3]['value'] = $this->currentDateTime; 
                $this->user_model->insertHistory($histData);
            }
            else{
                $result['status']="danger";
                $result['description'] = "Not successfully deleted the tag '$hashtag'";  
            }
            echo json_encode($result);              
        }
        /**
        Function:       subscribe
        Description:    Subscribe to the input tag
        */
        public function subscribe()
        {
            $this->checkSession('subscription'); //check if user have access right to the page

            if (!(isset($_POST['tag']))) 
                redirect('users');

            //AJAX parameters
            $hashtag = $_POST['tag']; //tag selected
            $maxValidEntries = $_POST['maxValidEntries']; //tag selected
            $startDateTime = strtotime($_POST['dateFrom'])+$this->gmt; //start date of enabling tag
            $endDateTime =   strtotime($_POST['dateTo'])+$this->gmt; //end date of enabling tag
            $status = $_POST['status'];//current status of tag (search/validation)
            $subscriptionStatus = $this->Instagram_model->getSubscriptionStatus(); // get label of subscription status
            $getEnabledSubscriptions = $this->Instagram_model->getNoEnabledSubscriptions(); //get no of active/enabled tags
            $getQueuedSubscriptions = $this->Instagram_model->getNoQueuedSubscriptions(); // get no of queued subscriptions
            $searchResult= $this->Instagram_model->searchSubscription($hashtag); // check if subscription already exist with an active status
            
            if ($searchResult->num_rows() > 0) //check if tag already exists
            {
               $result['status']="warning";
               $result['description'] = "This tag is already on the list.";
            }
            else if ($startDateTime >= $endDateTime) // check if start date of enabling tag is lesser than end date
            {
               $result['status']="warning";
               $result['description'] = "Start Date must be less than End date.";
            }
            else if ($getQueuedSubscriptions->num_rows() >= $this->config->item('maxQueueSubscription')){ //check if reach max number of queued subscriptions         
               $result['status']="warning";
               $result['description'] = "You already reached maximum number of queued subscriptions. Please delete unnecessary tags.";
            }
            else   if($getEnabledSubscriptions->num_rows() > $this->config->item('maxEnabledSubscription') ){ //check if reach max number of enabled subscriptions          
               $result['status']="warning";
               $result['description'] = "You already reached maximum number of enabled subscriptions. Please disable unnecessary tags.";
            }
            else
            {
                //if passed all criteria, subsribe to tag and add to queued subscriptions
                $createdOn = strtotime(date("Y-m-d H:i"))+$this->gmt; //store currentDateTime
                $newStatus = $status+1;
                $addTagResults = $this->Instagram_model->subscribe($hashtag,"",$maxValidEntries,$newStatus,$startDateTime,$endDateTime,$createdOn,$this->createdBy);
                if ($addTagResults == true)
                {
                    $tagData['subscriptionId'] = $startDateTime;
                    $tagData['status'] = $newStatus;
                    if ($maxValidEntries == '0')
                        $tagData['maxValidEntries'] = 'None';
                    else
                        $tagData['maxValidEntries'] = $maxValidEntries;
                    $tagData['statusDesc'] = $subscriptionStatus[$newStatus]->description. '(Queue)';
                    $tagData['startDateTime'] = gmdate("M. d, Y  g:i a",$startDateTime);
                    $tagData['endDateTime'] = gmdate("M. d, Y  g:i a",$endDateTime);
                    $tagData['createdOn'] = gmdate("M. d, Y  g:i a",$createdOn);
                    $tagData['createdBy'] = $this->createdBy;
                    $allData = json_encode($tagData);
                    $result['status']="success";
                    $result['description'] = "Successfully added tag '$hashtag' in  queued subscriptions"; 
                    $result['tagData'] = $allData;
                    //Insert action to history
                    $histData[0]['field'] = 'action'; 
                    $histData[0]['value'] = 'addSubscription'; 
                    $histData[1]['field'] = 'userId'; 
                    $histData[1]['value'] = $this->id;
                    $histData[2]['field'] = 'tagName'; 
                    $histData[2]['value'] = $hashtag; 
                    $histData[3]['field'] = 'sdate'; 
                    $histData[3]['value'] = $startDateTime;
                    $histData[4]['field'] = 'edate'; 
                    $histData[4]['value'] = $endDateTime; 
                    $histData[5]['field'] = 'tagStatus'; 
                    $histData[5]['value'] = $newStatus; 
                    $histData[6]['field'] = 'dateProcessed'; 
                    $histData[6]['value'] = $this->currentDateTime; 
                    $this->user_model->insertHistory($histData);
                }
                else
                {
                    $result['status']="danger";
                    $result['description'] = "Something happened when adding the tag to database. Please try again later.";
                }     
            }
            echo json_encode($result);
        }
        /**************************************************************************************************************************
        * View Entries Page: API related to viewing/saving searched/validation entries for a specific tag
        **************************************************************************************************************************/
        /**
        Function:       viewEntries (main page)
        Description:    Lets the user view all entries from instagram
        */
        public function viewEntries()
        {
            $this->checkSession('viewEntries'); //check if user have access right to the page
            //Web outline
            $data['header'] = $this->Web_model->getHeader('Instagram Entries');
            $data['navBar'] = $this->Web_model->getNavBar();
            $data['footer'] = $this->Web_model->getFooter();
            $data['base'] = $this->base;
            
            $dateToday = (strtotime(date("Y-m-d"))+$this->gmt); //get current date
            $exactDateToday = (strtotime(date("Y-m-d H:i"))+$this->gmt); //get current datetime
            $data['startDate'] = gmdate('Y-m-d H:i',$dateToday); //store epoch date to different format
            $data['endDate'] = gmdate('Y-m-d H:i',$exactDateToday); //store end date to different format
            $data['subscriptions'] = $this->Instagram_model->getSubscriptions(); // get all subscriptions except for deleted
            $data['entries'] = $this->Instagram_model->getEntries(); // get all instagram entries
            $data['tagEntryStatus'] = json_encode($this->Instagram_model->getEntriesStatus()); // get label for entry status
            $this->load->view('searchTag',$data);
        }
        /**
        Function:       saveSearchedEntries
        Description:    Save selected searched entries and validate it
        */
        public function revoke()
        {
            $this->checkSession('viewEntries'); //check if user have access right to the page
            $id = $_POST['id'];
            $revokeEntry = $this->Instagram_model->revokeEntry($id);
            if ($revokeEntry){
                $data[0]['field'] = 'action'; 
                $data[0]['value'] = 'revoke'; 
                $data[1]['field'] = 'userId'; 
                $data[1]['value'] = $this->id;
                $data[2]['field'] = 'entryId'; 
                $data[2]['value'] = $id; 
                $data[3]['field'] = 'dateProcessed'; 
                $data[3]['value'] = $this->currentDateTime; 
                $this->user_model->insertHistory($data);                
                echo '0';
            }
            else{
                echo '1';
            }
        }
        /**
        Function:       saveSearchedEntries
        Description:    Save selected searched entries and validate it
        */
        public function saveSearchedEntries()
        {
            $this->checkSession('viewEntries'); //check if user have access right to the page
            if (!((isset($_POST['ids'])) &&(isset($_POST['tagName'])))) 
                redirect('users');
            //AJAX parameters
            $ids = $_POST['ids'];
            $tagName = $_POST['tagName']; 
            //initialize 
            $caption='';
            $result['description']='';
            $getEntries = $this->Instagram_model->getCaptions($ids); //get all captions to each corresponding id entry
            if ($getEntries){
                foreach($getEntries as $entry)//validate each corresponding entry to know new status
                {
                    if (isset($entry->caption))
                       $caption = rawurldecode($entry->caption);
                    else
                       $caption = '';
                    $updateEntryStatus = $this->Instagram_model->validateSearchedEntry($entry->id,$caption);
                    if (!$updateEntryStatus)
                           break;
                    
                }
                if (!$updateEntryStatus){
                        $result['status']="warning";
                        $result['description'] = 'Something went wrong while saving results please try searching the data again.';
                    }
                    else{
                        $result['status']="success";
                        $result['description'] = 'Successfully saved results.';
                        //Insert action to history
                        $histData[0]['field'] = 'action'; 
                        $histData[0]['value'] = 'saveEntries'; 
                        $histData[1]['field'] = 'userId'; 
                        $histData[1]['value'] = $this->id;
                        $histData[2]['field'] = 'tagName'; 
                        $histData[2]['value'] = $tagName; 
                        $histData[3]['field'] = 'dateProcessed'; 
                        $histData[3]['value'] = $this->currentDateTime; ; 
                        $this->user_model->insertHistory($histData);
                    }
            }
            else{
                $result['status']="warning";
                $result['description'] = 'Something went wrong while saving results please try searching the data again.';
            }
            echo json_encode($result);
        }
        /**
        Function:       search
        Description:    Search entries according to desired date and tag filter
        */
        public function search()
	{
            $this->checkSession('viewEntries'); //check if user have access right to the page
            if (!isset($_POST['tagName']))
                redirect('users');
            $tagName = $_POST['tagName'];
            $dateFrom = $_POST['dateFrom'];
            $dateTo = $_POST['dateTo'];
            $dateFilter = $_POST['dateFilter'];
            $entryStatus = $_POST['entryStatus'];
            $header = $this->Web_model->getSearchTableHeader();           
            $dateFrom = strtotime($dateFrom)+$this->gmt;
            $dateTo =   strtotime($dateTo)+$this->gmt;
            $invalidReplies= ($this->Instagram_model->getInvalidReplies()); // get label for entry status
            if ($dateFrom >= $dateTo)
            {
                $result['status']="warning";
                $result['description'] = "Date error.";
            }
            else{
                if (($entryStatus == "all"))
                    $query = $this->Instagram_model->getAllEntriesWhere($tagName,$dateFrom,$dateTo,$dateFilter);
                else
                    $query = $this->Instagram_model->getEntriesWhere($tagName,$dateFrom,$dateTo,$dateFilter,$entryStatus);
                if ($query->num_rows() == 0)
                {
                    $result['status']="empty";
                    $result['description'] = 'No results found.';//.$tagName.' '.$dateFrom.' '.$dateTo.' '.$dateFilter.' '.$entryStatus;
                }
                else{
                    $tbody = $this->Web_model->getSearchTableBody($invalidReplies,$query->result());
                    $footer = $this->Web_model->getSearchTableFooter();
                    $result['status']="success";
                    $result['description'] = ($header.$tbody.$footer);
                    //Insert action to history
                    $histData[0]['field'] = 'action'; 
                    $histData[0]['value'] = 'viewEntries'; 
                    $histData[1]['field'] = 'userId'; 
                    $histData[1]['value'] = $this->id;
                    $histData[2]['field'] = 'tagName'; 
                    $histData[2]['value'] = $tagName; 
                    $histData[3]['field'] = 'sdate'; 
                    $histData[3]['value'] = $dateFrom;
                    $histData[4]['field'] = 'edate'; 
                    $histData[4]['value'] = $dateTo; 
                    $histData[5]['field'] = 'filter'; 
                    $histData[5]['value'] = $dateFilter;
                    $histData[6]['field'] = 'dateProcessed'; 
                    $histData[6]['value'] = $this->currentDateTime; 
                    $this->user_model->insertHistory($histData);
                }             
            }
            echo json_encode($result); 
	}
        /**************************************************************************************************************************
        * Validation Page: API related to validating/invalidating entries
        **************************************************************************************************************************/
        /**
        Function:       validation (main page)
        Description:    Lets the user validate entries that passed business requirements
        */
        public function validation()
	{
            $this->checkSession('validation'); //check if user have access right to the page
            //*Web outline
            $data['header'] = $this->Web_model->getHeader('Instagram Entries Validation');
            $data['navBar']     = $this->Web_model->getNavBar();
            $data['footer']     = $this->Web_model->getFooter();
            $data['base']       = $this->base;
            $data['gmt']        = $this->gmt;
            $data['invalidReplies']= ($this->Instagram_model->getInvalidReplies()); // get replies for invalid entries
            $data['subscriptions'] = $this->Instagram_model->getSubscriptions(); // get all subscriptions
            $data['entries'] = $this->Instagram_model->getEntriesForValidation(); // get all instagram entries
            $this->load->view('validation',$data);
        }
        /**
        Function:       getNextEntries
        Description:    Get next entries for validation queue (view more button)
        */
        public function getNextEntries(){
            $this->checkSession('validation'); //check if user have access right to the page
            if (!(isset($_POST['id'])))
                redirect ('users');
            $id = $_POST['id'];
            $this->checkSession('validation'); //check if user have access right to the page
            $numOfentries = $this->Instagram_model->getNextEntries($id);
            echo $numOfentries;
        }
        /**
        Function:       validateEntry
        Description:    Valite entry according to business requirements
        */
        public function validateEntry(){
            $this->checkSession('validation'); //check if user have access right to the page
            if (!(isset($_POST['id'])))
                redirect ('users');
            $id = $_POST['id'];
            $remarks = $_POST['remarks'];
            $status = $_POST['status'];
            $username = $_POST['username'];
            $timePosted = $_POST['timePosted'];
            $tin = $_POST['tin'];
            $orNo = $_POST['orNo'];
            $amount = $_POST['amount'];
            $dateNow =((strtotime(date("Y-m-d H:i:s"))+$this->gmt));//get current dateTime
            $checkIfProcessed = $this->Instagram_model->checkIfProcessed($id); // check if entry is already processed
            $checkStatus = $status;
            $checkRemarks='';
            $checkProcessedBy='';
            $checkLink='';
            $invalidReplies = ($this->Instagram_model->getInvalidReplies()); // get replies for invalid entries
            foreach ($checkIfProcessed as $row){
                $checkStatus = $row->status;
                $checkRemarks = $row->remarks;
                $checkProcessedBy=$row->processedBy;
                $checkLink=$row->entryLink;
            }
            if ($checkStatus != 0){ // check if status is not for validation
                $remarks = 2;
                $result['status']="validated";
                $result['description'] = $invalidReplies[$remarks - 1]->description;
            }
            else{
                if ($status == 2)//invalidate entry
                {
                    //* if for invalidation, change status of entry to invalid and updated processor, date processed, reason for invalidating
                    $invalidateEntry = $this->Instagram_model->validateEntry($id,$remarks,$dateNow,$this->createdBy,$status,'');
                    if ($invalidateEntry)
                    {
                        //**Insert action to history
                        $data[0]['field'] = 'action'; 
                        $data[0]['value'] = 'invalidate'; 
                        $data[1]['field'] = 'userId'; 
                        $data[1]['value'] = $this->id;
                        $data[2]['field'] = 'entryId'; 
                        $data[2]['value'] = $id; 
                        $data[3]['field'] = 'dateProcessed'; 
                        $data[3]['value'] = $dateNow; 
                        $this->user_model->insertHistory($data);
                        
                        $result['status']="success";
                        $result['description'] = "Successfully invalidated entry."; 
                        $result['copy'] = rawurldecode($invalidReplies[$remarks - 1]->description);
                    }
                    else{
                        $result['status']="danger";
                        $result['description'] = "Unsuccessful on invalidating entry. Please try again later.";  
                    }    
                }
                else{
                    /**If For validation
                     * check if reached max entries per day
                     * check if entry is unique (tin,orno and amount must be unique)
                     */
                    $remarks = 3;
                    $midnight = gmdate("Y-m-d",$timePosted);
                    //$midnight = strtotime('midnight', $timePosted); //get 12noon of the date posted of the entry
                    $date = strtotime($midnight) +$this->gmt;
                    $checkDuplicateEntry = $this->Instagram_model->checkDuplicateEntry($id);
                    //echo "validating $timePosted $date ".($this->Instagram_model->checkIfReachedMaxEntriesPerDay($username,$date,$id))." <br/>";
                    if (($this->Instagram_model->checkIfReachedMaxEntriesPerDay($username,$date,$id))=='true')//if reach max entries per day
                    {
                        $status=2;
                        $remarks = 5;
                        $invalidateEntry = $this->Instagram_model->validateEntry($id,$remarks,$dateNow,$this->createdBy,$status,'');
                        if ($invalidateEntry){
                            //Insert action to history
                            $data[0]['field'] = 'action'; 
                            $data[0]['value'] = 'invalidate'; 
                            $data[1]['field'] = 'userId'; 
                            $data[1]['value'] = $this->id;
                            $data[2]['field'] = 'entryId'; 
                            $data[2]['value'] = $id; 
                            $data[3]['field'] = 'dateProcessed'; 
                            $data[3]['value'] = $dateNow; 
                            $this->user_model->insertHistory($data);
                        
                            $result['status']="invalid";
                            $result['description'] = "This user already  has reached maximum number of entries per day.";
                            $result['copy'] = $invalidReplies[$remarks - 1]->description;
                        }
                    }
                    else if($checkDuplicateEntry !=''){
                        $status=2;
                        $remarks = 4;
                        $invalidateEntry = $this->Instagram_model->validateEntry($id,$remarks,$dateNow,$this->createdBy,$status,'');
                        if ($invalidateEntry){
                            //Insert action to history
                            $data[0]['field'] = 'action'; 
                            $data[0]['value'] = 'invalidate'; 
                            $data[1]['field'] = 'userId'; 
                            $data[1]['value'] = $this->id;
                            $data[2]['field'] = 'entryId'; 
                            $data[2]['value'] = $id; 
                            $data[3]['field'] = 'dateProcessed'; 
                            $data[3]['value'] = $dateNow; 
                            $this->user_model->insertHistory($data);
                        
                            $result['status']="invalid";
                            $result['description'] = "This user has duplicate entry. <a href='$checkDuplicateEntry' target='_blank'>Link</a>";
                            $result['copy'] = $invalidReplies[$remarks - 1]->description;
                        }
                    }
                    else{
                        $remarks = 1;
                        $code =$this->getCode($tin,$orNo,$amount);
                        $status=1;
                        if ($code)
                        {
                            $validateEntry = $this->Instagram_model->validateEntry($id,$remarks,$dateNow,$this->createdBy,$status,$code);

                            if ($validateEntry)
                            {
                                //Insert action to history
                                $data[0]['field'] = 'action'; 
                                $data[0]['value'] = 'validate'; 
                                $data[1]['field'] = 'userId'; 
                                $data[1]['value'] = $this->id; 
                                $data[2]['field'] = 'entryId'; 
                                $data[2]['value'] = $id; 
                                $data[3]['field'] = 'dateProcessed'; 
                                $data[3]['value'] = $dateNow; 
                                $this->user_model->insertHistory($data);

                                $result['status']="success";
                                $result['description'] = "Successfully validated entry.";   
                                $result['copy'] =  sprintf($invalidReplies[$remarks - 1]->description,$code);
                            }
                            else{
                                $result['status']="danger";
                                $result['description'] = "Unsuccessful on validating entry. Please try again later. ";  
                            } 
                        }
                        else{
                            $result['status']="danger";
                            $result['description'] = "Unsuccessful on validating entry. Please try again later. ";
                        }
                    }
                }
            }
            echo json_encode($result);
        }
        public function getCode($TIN,$ORNo ,$ORAmount){
            $accessCode = $this->config->item('accessCode');
            $telcoCode = $this->config->item('telcoCode');
            $mobileNumber = $this->config->item('mobileNumber');
            $numOfEntries = '1';
            $submitEntry = $this->instagram_api->submitEntry($accessCode,$telcoCode,$mobileNumber,$TIN,$ORNo,$ORAmount,$numOfEntries);
            if ($submitEntry == false){
                return false;
            }
            else{
                libxml_use_internal_errors(true);
                $sxe = simplexml_load_string($submitEntry);
                if (!$sxe) {
                   return false;               
                }
                else{
                    if ($sxe->Id == 0){
                        $match = array();
                        preg_match('/RaffleID is ([a-zA-Z0-9]+)\./', $sxe->Message, $match);
                        $code = $match[1];
                        return $code;
                    }
                    else{
                        return false;
                    }
                }
             }
        }
        /**************************************************************************************************************************
        * Web Tool Report: API related to web tool report
        **************************************************************************************************************************/
        /**
        Function:       report (main page)
        Description:    Web Tool Report about tag entries status and statistics
        */
        public function report(){
            $this->checkSession('webToolReport'); //check if user have access right to the page
            $data['header'] = $this->Web_model->getHeader('Web Tool Report');
            $data['navBar'] = $this->Web_model->getNavBar();
            $data['footer'] = $this->Web_model->getFooter();
            $data['base'] = $this->base;
            $data['gmt'] = $this->gmt;
            $data['dateToday'] =((strtotime(date("Y-m-d H:i:s"))+$this->gmt) );
            $data['startDate'] = gmdate('Y-m-d',$data['dateToday']);
            $this->load->view('report',$data);
        }
        /**
        Function:       processReport
        Description:    For filling entries of web tool report
        */
        public function processReport(){
            $this->checkSession('webToolReport'); //check if user have access right to the page
            $dateFrom1 = $_POST['dateFrom'];
            $dateTo1 = $_POST['dateTo'];
            $tin = $_POST['tin'];
            $amount = $_POST['amount'];
            $dateFrom = strtotime($dateFrom1)+$this->gmt;
            $dateTo = strtotime($dateTo1)+$this->gmt;
            if ($dateFrom >= $dateTo){
                $result['status']="warning";
                $result['description'] = "Start Date must be less than End date.";   
            }
            else{
                //$tin = ltrim($tin, '0');
                $entries =$this->Instagram_model->searchEntriesForReport('',$dateFrom,$dateTo,$tin,$amount);
                
                $totEntries =$entries->num_rows();
                $totValid =($this->Instagram_model->searchEntriesForReport(1,$dateFrom,$dateTo,$tin,$amount));
                $totValidEntries = $totValid->num_rows();
                
                $totInvalid =$this->Instagram_model->searchEntriesForReport(2,$dateFrom,$dateTo,$tin,$amount);
                $totInvalidEntries = $totInvalid->num_rows();
                $totProcessed =$this->Instagram_model->searchTotProcessedForReport($dateFrom,$dateTo,$tin,$amount);
                $totProcessedEntries = $totValidEntries + $totInvalidEntries;
                $distinctEntries =$this->Instagram_model->searchDistinctEntriesForReport('',$dateFrom,$dateTo,$tin,$amount);
                $totUser =$distinctEntries->num_rows();
                $entriesStatus = $this->Instagram_model->getEntriesStatus();
                $table =  $this->Web_model->reportBody($entriesStatus,$entries->result(),$totEntries,$totValidEntries,$totInvalidEntries,$totProcessedEntries,$totUser);
                $result['status']="success";
                $result['description'] = $table;
                //**Insert action to history
                $histData[0]['field'] = 'action'; 
                $histData[0]['value'] = 'report'; 
                $histData[1]['field'] = 'userId'; 
                $histData[1]['value'] = $this->id;
                $histData[2]['field'] = 'sdate'; 
                $histData[2]['value'] = $dateFrom;
                $histData[3]['field'] = 'edate'; 
                $histData[3]['value'] = $dateTo; 
                $histData[4]['field'] = 'tin'; 
                $histData[4]['value'] = $tin; 
                $histData[5]['field'] = 'amount'; 
                $histData[5]['value'] = $amount;
                $histData[6]['field'] = 'dateProcessed'; 
                $histData[6]['value'] = $this->currentDateTime; 
                $this->user_model->insertHistory($histData);
            }
            echo json_encode($result); 
        }

        /**
        Function:       addEntryForTesting
        Description:    for testing purposes only
        */
        public function addEntryForTesting()
        {
			$hashtag = $_GET['hashtag'];
			$username =$_GET['username'];
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
        Function:       getTagEntriesForTesting
        Description:    get entries for testing purposes
        */
        public function getTagEntriesForTesting($tagName,$startDate,$endDate)
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
                    if (isset($entry['caption']['text']))
                            $caption = $entry['caption']['text'];
                        else
                            $caption = "";
                    if (($timePosted >= $startDate )&&($timePosted <= $endDate ))
                    {
                        
                        $mediaId = $entry['id'];
                        //$allEntries .= $entry['user']['username']." ". $caption." ".$entry['link']." ".$timePosted." ".$tagName." ".$mediaId." /r/n";
                        echo "<p> ok".$entry['user']['username']." ".$caption." <a href='".$entry['link']."'>".gmdate("M. d, Y  g:i a",$timePosted)."</a>";
                        $resultNo++;                                          
                    }
                    else{
                        echo "<p> not ok".$entry['user']['username']." ".$caption." <a href='".$entry['link']."'>".gmdate("M. d, Y  g:i a",$timePosted)."</a>";
                        $breakStatus++;
                    }
                }
                 if ((($breakStatus >= 100)))
                {
                    break;
                }
                $getResultCtr++;
                if (isset($getEntries['pagination']['next_max_id'])) 
                {
                    $max_id = $getEntries['pagination']['next_max_id'];
                }
                else
                    break;
            }while(true);	
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
        Function:       searchTag
        Description:    Subscribe for searching from the input tag
        */
        public function searchTag()
        {
           $dateFrom = strtotime($_GET['dateFrom'])+$this->gmt;
           $dateTo =   strtotime($_GET['dateTo'])+$this->gmt;
           $hashtag = preg_replace("/[^a-zA-Z0-9]+/", "", $_GET['tag']);    
           $breakAll = false;
           if (($dateTo > $dateFrom))
           {    
                $result['status']="warning";
                $result['description'] = $dateTo - $dateFrom;
                $searchTag= $this->Instagram_model->searchSubscription($hashtag);
                if ($searchTag->num_rows() > 0) 
                {
                    $result['status']="warning";
                    $result['description'] = "This tag is already existing in subscriptions";                  
                }
                else
                {
                    $max_id = false;
                    $getResultCtr = 0;
                    $resultNo=0;
                    $allEntries ="";
                    $tbody = "";
                    $maxbreak = 500;
                    do
                    {
                        if ($breakAll) break;
                        $getEntries = json_decode($this->instagram_api->getTag($hashtag,false,false,$max_id),true);
                        $breakStat =0;
                        if (isset($getEntries['data'] ))
                        {
                            foreach ($getEntries['data'] as $entry)
                            {
                                $timePosted = (($entry['created_time'])+$this->gmt);
                                if (($timePosted >= $dateFrom )&&($timePosted <= $dateTo ))
                                {
                                    if (isset($entry['caption']['text']))
                                    {
                                        if (strlen($entry['caption']['text'])>100)
                                            $caption =mb_substr($entry['caption']['text'],0,100)."...";
                                        else
                                            $caption = $entry['caption']['text'];
                                    }     
                                    else
                                        $caption = "";
                                    $tbody .="<tr><td>".$entry['user']['username']."</td><td>".$breakStat."</td><td><a href='".$entry['link']."' target='blank'>".gmdate("M. d, Y  g:i a",$timePosted)."</a></td></tr>";          
                                    $allEntries[$resultNo] = ($entry);
                                    $resultNo++;                                          
                                }
                            }
                            $getResultCtr++;
                            if (isset($getEntries['pagination']['next_max_id'])) 
                            {
                                $max_id = $getEntries['pagination']['next_max_id'];
                            }
                            else
                            {
                                $breakAll = true;
                                break;
                            }
                        }
                        
                        else
                            break;
                    }while(true);	
                    $result['status']="table";
                    $result['allEntries']=json_encode($allEntries);
                    $result['description'] ="
                    <br/><div class='well well-sm'>
                    <div class='alert alert-success'><p>Entry Results for the tag '".$hashtag."' from ".gmdate("M. d, Y  g:i a",$dateFrom)." to ".gmdate("M. d, Y  g:i a",$dateTo)."</p></div>
                    <table cellpadding='0' cellspacing='0' border='0' class='display' id='example'>
                        <thead>
                                <tr>
                                        <th>Username</th>
                                        <th>Caption</th>
                                        <th>Date Posted</th>
                                </tr>
                        </thead>
                        <tbody> 
                           ".$tbody."
                        </tbody>
                        <tfoot>
                                <tr>
                                        <th></th>
                                        <th></th>
                                        <th></th>
                                </tr>
                        </tfoot>
                </table>
                
                <button id='saveEntries' class='btn btn-xs btn-primary'>Save Results for Validation</button>
                </div>";
                }
           }
           else
           {
               $result['status']="warning";
               $result['description'] = "Date Error";
           }
           echo json_encode($result);
        }        
        function generateHash($password) {
            if (defined("CRYPT_BLOWFISH") && CRYPT_BLOWFISH) {
                $salt = '$2y$11$' . substr(md5(uniqid(rand(), true)), 0, 22);
                return crypt($password, $salt);
            }
        }
        function verify($password, $hashedPassword) {
            return crypt($password, $hashedPassword);
        }
        public function test($password)
        {
            echo $password.'<br/>';
            if (defined("CRYPT_BLOWFISH") && CRYPT_BLOWFISH) {
                $md5pwd =  substr(md5(uniqid(rand(), true)), 0, 22);
                echo $md5pwd.'<br/>';
                $salt = '$2y$11$' .$md5pwd;
                echo $salt.'<br/>';
                echo crypt($password, $salt);
            }
        }

}
