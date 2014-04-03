<?php
/**
 * ALL YOUR IMPORTANT API INFO
 * EDIT THE CODES BELOW
 */
$client_id = 'c1a61fc930bc4a0cbd94cd98b16fdaf7';
$client_secret = '332cb53bd1cf41a4a99cbffb6aecc8c8';
$object = 'tag';
$object_id = 'psresibo';
$aspect = 'media';
$verify_token='518527560.c1a61fc.a1c60d3f5a514057b2325525b32fc7e1';
$callback_url = 'http://202.44.102.29/callback/callback.php';


/**
 * SETTING UP THE CURL SETTINGS
 * DO NOT EDIT BELOW
 */
$attachment =  array(
'client_id' => $client_id,
'client_secret' => $client_secret,
'object' => $object,
'object_id' => $object_id,
'aspect' => $aspect,
'verify_token' => $verify_token,
'callback_url'=>$callback_url
);

// URL TO THE INSTAGRAM API FUNCTION
$url = "https://api.instagram.com/v1/subscriptions/";

$ch = curl_init();

// EXECUTE THE CURL...
curl_setopt($ch, CURLOPT_URL,$url);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 2);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, $attachment);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);  //to suppress the curl output 
$result = curl_exec($ch);
curl_close ($ch);

// PRINT THE RESULTS OF THE SUBSCRIPTION, IF ALL GOES WELL YOU'LL SEE A 200
print_r($result);


?>