<?php
    $con=mysqli_connect("localhost","root","","socialdb");
    $time_start = microtime(true); 

   
    $callback_contents = file_get_contents('php://input');
    date_default_timezone_set('Asia/Manila');
    $gmt = 8*60*60;
    $minTagId = '';
    $forsha1='';
    $dateToday = strtotime(date("Y-m-d H:i:s"))+$gmt;
    if (isset($_GET['hub_challenge']))
    {
          // 'hub challenge:'.$_GET['hub_challenge'];
      echo  $_GET['hub_challenge'];
    }
    
    else if (isset($_GET['code']))
    {      
        $attachment =  array(
                'client_id'         => 'c1a61fc930bc4a0cbd94cd98b16fdaf7',
                'client_secret'     => '332cb53bd1cf41a4a99cbffb6aecc8c8',
                'grant_type'        => 'authorization_code',
                'redirect_uri'      => 'http://202.44.102.29/instagram/callback/callback.php',
                'code'              => $_GET['code']
            );
        $curl_session = curl_init();
	    	
        // Set the URL of api call
        curl_setopt($curl_session, CURLOPT_URL, 'https://api.instagram.com/oauth/access_token');
		    
        // If there are post fields add them to the call
        if($attachment !== FALSE) {
            curl_setopt($curl_session, CURLOPT_POST, true);
            curl_setopt ($curl_session, CURLOPT_POSTFIELDS, $attachment);
        }

        // Return the curl results to a variable
        curl_setopt($curl_session, CURLOPT_RETURNTRANSFER, true);
	curl_setopt($curl_session, CURLOPT_SSL_VERIFYPEER, false);
	// Execute the cURL session
        $contents = curl_exec ($curl_session);
        if(!curl_exec($curl_session)){
            die('Error: "' . curl_error($curl_session) . '" - Code: ' . curl_errno($curl_session));
        }    
        print_r($contents);
	// Close cURL session
	curl_close ($curl_session);
    }
    else
    {
        $result = json_decode($callback_contents,true);
        $subscriptionId = $result[0]['subscription_id'];
        $tagName = $result[0]['object_id'];
        $optionVar = $subscriptionId."minId";
        $optionVal = '';
        //get min tag id
        $sql = "SELECT * FROM `instagram.globaloption` WHERE `optionVar`='$optionVar'";
        $getMinId = mysqli_query($con,$sql );
        while($row = mysqli_fetch_array($getMinId))
        {
            $optionVal = $row['optionVal'];                 
        }    
        $minTagId = $optionVal;
        
        
        //get status
        $sql = "SELECT `status`,`startDateTime` FROM `instagram.subscriptions` WHERE `subscriptionId`='$subscriptionId'";
        $getStatus = mysqli_query($con,$sql );
        while($row = mysqli_fetch_array($getStatus))
        {
            $status = $row['status']; 
            $startDateTime = $row['startDateTime']; 
        }    
        if(!(($minTagId == 0 )|| ($startDateTime>$dateToday)))
        {
            //initialize instagram config
            if ($minTagId == 0) 
                $minTagId = false;
            $tagRecentUrl = "https://api.instagram.com/v1/tags/%s/media/recent?min_tag_id=%s&access_token=%s";
            $getTagsUrl = sprintf($tagRecentUrl,$tagName,$minTagId,'518527560.c1a61fc.a1c60d3f5a514057b2325525b32fc7e1');   
            //get tag entries through curl
            $curl_session = curl_init();
            curl_setopt($curl_session, CURLOPT_URL, $getTagsUrl);
            curl_setopt($curl_session, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($curl_session, CURLOPT_SSL_VERIFYPEER, false);
            $curlContents = curl_exec ($curl_session);
            if(!curl_exec($curl_session)){
                die('Error: "' . curl_error($curl_session) . '" - Code: ' . curl_errno($curl_session));
            }    
            curl_close ($curl_session);

            $getEntries = json_decode($curlContents,true);
            //update minID
            $newMinTagId = $getEntries['pagination']['min_tag_id'];
            $sql = "UPDATE `instagram.globaloption` SET `optionVal`='$newMinTagId' WHERE `optionVar`='$optionVar'";
            mysqli_query($con,$sql );

            foreach ($getEntries['data'] as $entry)
            {
                $timeCreated = (($entry['created_time'])+$gmt);
                if (isset($entry['caption']['text']))
                    $caption = $entry['caption']['text'];
                else
                    $caption = "";        
                $username = $entry['user']['username'];
                $userId = $entry['user']['id'];
                $entryLink = $entry['link'];
                $mediaId = $entry['id'];
                $remarks = '';
                $strregex ="/^[\s]*@(psrtrial)[\s,]+(#[A-z0-9]+)[\s,]+(PSR)[\s,]+([A-z0-9]{9,12})[\s,]+([0-9]+)[\s,]+(([0-9]+)|(([0-9]+)[.]([0-9]{1,4})))[\s,]*$/";
                if ($status < 2)
                {
                    if (preg_match($strregex, $caption)) {
                        $parts = preg_split('/\s+/', trim($caption));
                        $tin = $parts[3];//ltrim($parts[3], '0');
                        $orNo = $parts[4];
                        $amount = $parts[5];
                        $str = trim(mb_strtolower($tin)).trim($orNo).trim($amount);
                        $str = preg_replace( '/\s+/', ' ', $str );
                        $sha1 =  sha1($str);
                        
                        //$callback_contents .= " ".$username." ".$caption." ".$entryLink." ".$timeCreated." ".$tin." ".$orNo." ".$amount." ".$tagName." ".$mediaId;
                        $sql = "INSERT IGNORE INTO `instagram.tagentries`(`username`,  `userId`, `caption`,`entryLink`,`timePosted`,`status`,`tin`,`orNo`,`amount`,`tagName`,`mediaId`,`sha1`,`remarks`)
                                VALUES('$username','$userId','$caption','$entryLink','$timeCreated','0','$tin','$orNo','$amount','$tagName','$mediaId','$sha1','3')";
                        //$callback_contents .= $sql;
                        $query1 = mysqli_query($con,$sql );
                    } else {
                        //$callback_contents .= " ".$username." ".$caption." ".$entryLink." ".$timeCreated." ".$dateToday." ".$tagName." ".$mediaId;               
                        $sql = "INSERT IGNORE INTO `instagram.tagentries`(`username`, `userId`, `caption`,`entryLink`,`timePosted`,`timeProcessed`,`processedBy`,`remarks`,`status`,`tagName`,`mediaId`) 
                                VALUES('$username','$userId','$caption','$entryLink','$timeCreated','$dateToday','WebTool','3','2','$tagName','$mediaId')";
                        //$callback_contents .= $sql;
                        $query1 = mysqli_query($con,$sql );
                    }
                }
                else{
                    $sql = "INSERT IGNORE INTO `instagram.tagentries`(`username`, `userId`, `caption`,`entryLink`,`timePosted`,`timeProcessed`,`processedBy`,`remarks`,`status`,`tagName`,`mediaId`) 
                                VALUES('$username','$userId','$caption','$entryLink','$timeCreated','$dateToday','WebTool','3','3','$tagName','$mediaId')";
                        //$callback_contents .= $sql;
                    $query1 = mysqli_query($con,$sql );
                }
            }
        }
    }
    $time_end = microtime(true);
    $execution_time = ($time_end - $time_start);
    //output data to file 
    if(!(($minTagId == 0 )|| ($startDateTime>$dateToday)))
        $ALL = $dateToday." ".$callback_contents." exec time ".$execution_time." "."\r\n";
    else
        $ALL = 'Still inactive '." ".$dateToday." ".$callback_contents." exec time ".$execution_time."\r\n";
    file_put_contents('activity.log', $ALL, FILE_APPEND);    
?>
