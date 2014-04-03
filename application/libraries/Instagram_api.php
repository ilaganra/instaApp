<?php
if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Instagram_api {

	/*
	 * Variable to hold an insatance of CodeIgniter so we can access CodeIgniter features
	 */
    protected $codeigniter_instance;

	/*
	 * Create an array of the urls to be used in api calls
	 * The urls contain conversion specifications that will be replaced by sprintf in the functions
	 * @var string
	 */
    protected $api_urls = array(
    	'user'						=> 'https://api.instagram.com/v1/users/%s/?access_token=%s',
        'user_feed'					=> 'https://api.instagram.com/v1/users/self/feed?access_token=%s&max_id=%s&min_id=%s',
        'user_recent'                                   => 'https://api.instagram.com/v1/users/%s/media/recent/?access_token=%s&count=%s&max_id=%s&min_id=%s&max_timestamp=%s&min_timestamp=%s',
        'user_search'                                   => 'https://api.instagram.com/v1/users/search?q=%s&access_token=%s',
        'user_follows'                                  => 'https://api.instagram.com/v1/users/%s/follows?access_token=%s',
        'user_followed_by'                              => 'https://api.instagram.com/v1/users/%s/followed-by?access_token=%s',
        'user_requested_by'                             => 'https://api.instagram.com/v1/users/self/requested-by?access_token=%s',
        'user_relationship'                             => 'https://api.instagram.com/v1/users/%s/relationship?access_token=%s',
        'modify_user_relationship'                      => 'https://api.instagram.com/v1/users/%s/relationship?access_token=%s',
        'media'						=> 'https://api.instagram.com/v1/media/%s?access_token=%s',
        'media_search'                                  => 'https://api.instagram.com/v1/media/search?lat=%s&lng=%s&max_timestamp=%s&min_timestamp=%s&distance=%s&access_token=%s',
        'media_popular'                                 => 'https://api.instagram.com/v1/media/popular?access_token=%s',
        'media_comments'                                => 'https://api.instagram.com/v1/media/%s/comments?access_token=%s',
        'post_media_comment'                            => 'https://api.instagram.com/v1/media/%s/comments?access_token=%s',
        'delete_media_comment'                          => 'https://api.instagram.com/v1/media/%s/comments?comment_id=%s&access_token=%s',
        'likes'						=> 'https://api.instagram.com/v1/media/%s/likes?access_token=%s',
    	'post_like'					=> 'https://api.instagram.com/v1/media/%s/likes?access_token=%s',
        'remove_like'                                   => 'https://api.instagram.com/v1/media/%s/likes?access_token=%s',
        'getSubscriptions'				=> 'https://api.instagram.com/v1/subscriptions?client_secret=%s&client_id=%s',
        'addsubscription'				=> 'https://api.instagram.com/v1/subscriptions/',
        'delsubscription'				=> 'https://api.instagram.com/v1/subscriptions?access_token=%s&client_secret=%s&id=%s&client_id=%s',
        'tags'						=> 'https://api.instagram.com/v1/tags/%s?access_token=%s',
        'tags_recent'                                   => 'https://api.instagram.com/v1/tags/%s/media/recent?max_id=%s&min_id=%s&max_tag_id=%s&access_token=%s',
        'tags_search'                                   => 'https://api.instagram.com/v1/tags/search?q=%s&access_token=%s',
        'locations'					=> 'https://api.instagram.com/v1/locations/%d?access_token=%s',
        'locations_recent'                              => 'https://api.instagram.com/v1/locations/%d/media/recent/?max_id=%s&min_id=%s&max_timestamp=%s&min_timestamp=%s&access_token=%s',
        'locations_search'                              => 'https://api.instagram.com/v1/locations/search?lat=%s&lng=%s&foursquare_id=%s&distance=%s&access_token=%s'
    );
    protected $submitEntryUrl = 'http://172.16.102.82/E37F4EE631348C0795CF4235ADD9807A6FB4B1DE/publicmethods.asmx/SubmitEntry?accessCode=%s&telcoCode=%s&mobileNumber=%s&TIN=%s&ORNo=%s&ORAmount=%s&numOfEntries=%s';
    //http://172.16.102.82/E37F4EE631348C0795CF4235ADD9807A6FB4B1DE/publicmethods.asmx/SubmitEntry?accessCode=1234&telcoCode=2345&mobileNumber=3456&TIN=3434&ORNo=3434&ORAmount=343&numOfEntries=1
    /*
     * Construct function
     * Sets the codeigniter instance variable and loads the lang file
     */
    function __construct() {
    	// Set the CodeIgniter instance variable
    	$this->codeigniter_instance =& get_instance();
    	// Load the Instagram API language file
    	$this->codeigniter_instance->load->config('Instagram_api');
    } 
    public $access_token = FALSE;
    function submitEntry($accessCode,$telcoCode,$mobileNumber,$TIN,$ORNo,$ORAmount,$numOfEntries){
        $getUrl = sprintf($this->submitEntryUrl,$accessCode,$telcoCode,$mobileNumber,$TIN,$ORNo,$ORAmount,$numOfEntries);   	
        return($this->apiCall($getUrl,false,false));
        //return $getUrl;
    }
    function getTag($tag,$minId,$maxId,$maxTagId){
        $getUrl = sprintf($this->api_urls['tags_recent'],$tag,$minId,$maxId,$maxTagId,$this->codeigniter_instance->config->item('access_token'));   	
        return($this->apiCall($getUrl,false,false));   
    }
    function getSubscriptions(){
        $getUrl = sprintf($this->api_urls['getSubscriptions'],$this->codeigniter_instance->config->item('instagram_client_secret'),$this->codeigniter_instance->config->item('instagram_client_id'));   	
        return($this->apiCall($getUrl,false,false));   
    }
    function addSubscription($parameters) {

            $attachment =  array(
                'client_id'         => $this->codeigniter_instance->config->item('instagram_client_id'),
                'client_secret'     => $this->codeigniter_instance->config->item('instagram_client_secret'),
                'object'            => 'tag',
                'object_id'         => $parameters,
                'aspect'            => 'media',
                'verify_token'      => $this->codeigniter_instance->config->item('access_token'),
                'callback_url'      => $this->codeigniter_instance->config->item('instagram_callback_url')
            );
            return($this->apiCall($this->api_urls['addsubscription'],$attachment,false));   
    }
    function deleteSubscription($id) {
        $deleteUrl = sprintf($this->api_urls['delsubscription'],$this->access_token,$this->codeigniter_instance->config->item('instagram_client_secret'),$id,$this->codeigniter_instance->config->item('instagram_client_id'));
    	return($this->apiCall($deleteUrl,false,'DELETE'));   
    }
    function apiCall($url, $post_parameters, $customrequest) {
    
        
    	// Initialize the cURL session
        $curl_session = curl_init();
	    	
        // Set the URL of api call
        curl_setopt($curl_session, CURLOPT_URL, $url);
		    
        // If there are post fields add them to the call
        if($post_parameters !== FALSE) {
           $params = http_build_query($post_parameters);
            curl_setopt($curl_session, CURLOPT_POST, true);
            curl_setopt ($curl_session, CURLOPT_POSTFIELDS, $params);
        }
        if($customrequest !== FALSE) {
            curl_setopt ($curl_session, CURLOPT_CUSTOMREQUEST, "DELETE");
        }
        // Return the curl results to a variable
        curl_setopt($curl_session, CURLOPT_RETURNTRANSFER, true);
	curl_setopt($curl_session, CURLOPT_SSL_VERIFYPEER, false);
	// Execute the cURL session
        $contents = curl_exec ($curl_session);
        if(!curl_exec($curl_session)){
            die('Error: "' . curl_error($curl_session) . '" - Code: ' . curl_errno($curl_session));
        }    
	// Close cURL session
	curl_close ($curl_session);
        //print_r($contents);
        return $contents;
    }
}

?>
