<?php

/*
|--------------------------------------------------------------------------
| Instagram
|--------------------------------------------------------------------------
|
| Instagram client details
|
*/

$config['instagram_client_name']	= '';
$config['instagram_client_id']		= '';
$config['access_token']                 = '';
$config['instagram_client_secret']	= '';
$config['instagram_callback_url']	= '';
$config['instagram_website']		= '';
$config['instagram_description']	= 'instagram web tool';
$config['maxEnabledSubscription']       = 5;
$config['maxQueueSubscription']         = 2;
$config['maxHourTagAction']             = 60*60;
$config['maxEntriesPerDay']             = 3;
$config['accessCode']                   = '0';
$config['telcoCode']                    = '123A';
$config['mobileNumber']                 = '639266239562';
$config['entryFormat']                  = "/^[\s]*@(accountName)[\s,]+(#[A-z0-9]+)[\s,]+(PSR)[\s,]+([0-9]{9,12})[\s,]+([0-9]+)[\s,]+(([0-9]+)|(([0-9]+)[.]([0-9]{1,4})))[\s,]*$/";
        
// There was issues with some servers not being able to retrieve the data through https
// If you have this problem set the following to FALSE 
// See https://github.com/ianckc/CodeIgniter-Instagram-Library/issues/5 for a discussion on this
$config['instagram_ssl_verify']		= FALSE;
