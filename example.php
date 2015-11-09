<?php
require_once(dirname().'/class_curl.php');

class Feeds_Download{
	const API_TIMEOUT = 30;
	
	function __construct($args = array()){

	}
	
	public function download_feed($url = '', $fields = array(), $filename = '', $method = 'GET'){
		try{
			$optin = array('CURLOPT_USERAGENT'       => 'Unknow Agent',
							'CURLOPT_RETURNTRANSFER' => 1,		//TRUE to return the transfer as a string of the return value of curl_exec() instead of outputting it out directly.
							'CURLOPT_CONNECTTIMEOUT' => 5,		//The number of seconds to wait while trying to connect. Use 0 to wait indefinitely.
							'CURLOPT_FRESH_CONNECT'  => TRUE,	// Force the use of a new connection instead of a cached one
							'CURLOPT_FORBID_REUSE'   => TRUE, 	// Force the connection to explicitly close when it has finished processing, and not be pooled for reuse
							'CURLOPT_AUTOREFERER'    => TRUE,	// Automatically set the "Referer:" field in request
							'CURLOPT_FOLLOWLOCATION' => TRUE,	// Automatically follow any "Location: " header that the server sends as part of the HTTP header
							'CURLOPT_MAXREDIRS'		 => 3,		// Maximum amount of HTTP redirections to follow.
							'CURLOPT_TIMEOUT'        => self::API_TIMEOUT,	// Maximum number of seconds to allow cURL functions to execute
							//'CURLOPT_VERBOSE'        => TRUE	// Output verbose information
			);
		
			$curl = new dcai\curl($optin);
			
			if ($method == 'GET'){
				$response = $curl->get($url, $fields);
			}
			else if ($method == 'POST'){
				$response = $curl->post($url, $fields);
			}
			else if ($method == 'PUT'){
				$response = $curl->put($url, $fields);
			}
			else{
				error_log("Error: unsupport METHOD !");
				return FALSE;
			}
			
			//testing
			//error_log(__METHOD__." Finished to download feeds via url=$url and the responses status_code = ".$response->status_code);
			
			if (!isset($response->status_code) || ($response->status_code == 0)){
				error_log(__METHOD__ .' TIMEOUT: api call to ' . $url . ' took more than ' . self::API_TIMEOUT . 's to return' );
			}
			else if ($response->status_code == 200){
				if (!empty($response->text)){
					if ($this->write_cache_file($filename, $response->text)){
						return TRUE;
					}
					
				}
				else{
					error_log(__METHOD__. ' No response data return');
					return FALSE;
				}
			}
			else if ($response->status_code == 401){
				error_log(__METHOD__. ' Unauthorized API request to ' . $url);
			}
			else if ($response->status_code == 404){
				error_log(__METHOD__. ' File not found at: ' . $url);
			}
			else{
				error_log(__METHOD__. ' Unknow error at ' . $url ." Http code:" .$response->status_code);
			}
			
			return FALSE;
			
		} catch ( Exception $e ) {
			error_log('General Exception Error: '.$e->getMessage());
		}
		
		return FALSE;
		
	}
	
	private function write_cache_file($filename = '', $data = ''){
		if (!empty($data)){
			if (file_exists($filename)){
				unlink($filename);
			}
			
			try{
				file_put_contents($filename, $data);
				if (file_exists($filename)){
					return TRUE;
				}
			} catch ( Exception $e){
				error_log("Failed to write download cache to local file $filename Error: ". $e->getMessage() );
			}
			
		}
		return FALSE;
		
	}
	
}

$feeds_download = new Feeds_Download();

$feed_url = 'http://www.example.com/example.xml';
$download_tmp_file = '/tmp/download/example.xml';
$method   = 'GET';

if ($feeds_download->download_feed($feed_url, array(), $download_tmp_file, $method) != FALSE){
	echo "Success";
}




