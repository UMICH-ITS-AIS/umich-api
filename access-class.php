<?php

class UMapis {
	//config
	//fill in the consumer key and secret from the UM API site
	private $consumer_key = 'YOUR_COMSUMER_KEY';
	private $consumer_secret = 'YOUR_CONSUMER_SECRET';
	
	public $access_token;
	public $refresh_token;
	public $access_token_expiration_time;
	
	public function __construct($authenticate_now = true) {
		$this->access_token_expiration_time = time();
		if ($authenticate_now) {
			$this->authenticate();
		}
	}
	
	public function authenticate() {
		/* authenticate to the oauth2 API directory
		- Check for valid, unexpired access token
		- If not, get one
		- If valid, unexpired access token, refresh token */
		if (isset($this->access_token)) {
			if (time() > $this->access_token_expiration_time) {
				//token is expired, refresh it
				$this->refresh_the_access_token();
			}
		} else {
			$this->get_access_token();
		}
	}
		
	//generate the access tokens
	public function get_access_token() {
		/*
		* You can generate and renew your access token in the API Directory under Subscriptions. However, this method is only suggested for when you are testing the features of the API as the access token is only valid for a short period. When you want to embed an API from the API Directory in your application, you want to have your code generate and renew the tokens. To do this, you want to use the token service. This service allows you to programmatically retrieve your access token by supplying your consumer key and secret. Follow the steps below.
		* Obtain your consumer key and consumer secret from the API Directory. These are generated on the Subscriptions page after an application is successfully subscribed an API.
		* Combine the consumer key and consumer secret keys in the format: consumer-key:consumer-secret.  Encode the combined string using base64. Most programming languages have a method to base64 encode a string. For an example of encoding to base64.  Visit the base64encode site for more information.
		* Execute a GET call to the token API to get an access token.
		curl -k -d "grant_type=client_credentials&scope=PRODUCTION" -H "Authorization :Basic NnY2UGRoX0s0dW5oanZzSkh1SUlkcUtwRXJBYTpQUTBTRmVNT3pvbVFzU1hTQjBRenpGc213dW9h, Content-Type: application/x-www-form-urlencoded" https://api-km.it.umich.edu/token
		* Below is a sample response to the token API call:
		{"token_type":"bearer","expires_in":3600,"refresh_token":"4f1f65f83ccc7543e2bbb0c819d76295","access_token":"a1121ed47f2b29029923799782d9"}
		* Use the access token returned above in the Authentication header to call APIs.
		*/

		$combined_key = $this->consumer_key.':'.$this->consumer_secret;
		$encoded_key = base64_encode ( $combined_key );
		$encoded_fields = 'grant_type=client_credentials&scope=PRODUCTION';
		$apiurl = 'https://api-km.it.umich.edu/token';
		$headers = array(
			'Authorization :Basic '.$encoded_key,
			'Content-Type: application/x-www-form-urlencoded'
		);

		$ch = curl_init($apiurl);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false); // -k
		curl_setopt($ch, CURLOPT_POSTFIELDS, $encoded_fields); // -d
		curl_setopt($ch, CURLOPT_HTTPHEADER, $headers); // -H
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true); //put result in a variable

		$json_response = curl_exec($ch);
		curl_close($ch);

		$authObj = json_decode($json_response);
		//echo '<pre>'; print_r($authObj); echo '</pre>'; exit();

		if (isset($authObj->refresh_token)){
			//refresh token only granted on first authorization for offline access
			//you can store this value permanently somewhere and just use this to access rather than refreshing the access token
			$this->refresh_token = $authObj->refresh_token;
		}
		
		$this->access_token = $authObj->access_token;
		$this->access_token_expiration_time = time() + $authObj->expires_in;
		return $this->access_token;
	}

	//refresh an access token
	public function refresh_the_access_token() {
		/*
		After an access token has expired, you can refresh your token by issuing the request below to the token service:
		curl -k -d "grant_type=refresh_token&refresh_token=4f1f65f83ccc7543e2bbb0c819d76295&scope=PRODUCTION" -H "Authorization :Basic SVpzSWk2SERiQjVlOFZLZFpBblVpX2ZaM2Y4YTpHbTBiSjZvV1Y4ZkM1T1FMTGxDNmpzbEFDVzhh, Content-Type: application/x-www-form-urlencoded" https://api-km.it.umich.edu/token
		translated to php as:
		-k =>  CURLOPT_SSL_VERIFYPEER, false
		-d =>  CURLOPT_POSTFIELDS
		-H =>  CURLOPT_HTTPHEADER

		A successful response will allow you to continue to use your existing Access Token.  If you want to generate a brand new Access Token rather than refreshing the existing token, you can do the call in step 3 at any time.
		*/
		$combined_key = $this->consumer_key.':'.$this->consumer_secret;
		$encoded_key = base64_encode ( $combined_key );
		$encoded_fields = 'grant_type=refresh_token&refresh_token='.$this->refresh_token.'&scope=PRODUCTION';
		$apiurl = 'https://api-km.it.umich.edu/token';
		$headers = array(
			'Authorization :Basic '.$encoded_key,
			'Content-Type: application/x-www-form-urlencoded'
		);

		$ch = curl_init($apiurl);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false); // -k
		curl_setopt($ch, CURLOPT_POSTFIELDS, $encoded_fields); // -d
		curl_setopt($ch, CURLOPT_HTTPHEADER, $headers); // -H
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true); //put result in a variable

		$json_response = curl_exec($ch);
		curl_close($ch);

		$authObj = json_decode($json_response);
		
		$this->access_token = $authObj->access_token;
		$this->access_token_expiration_time = time() + $authObj->expires_in;
		return $this->access_token;
	}
	
	private function call_api($url) {
		$this->authenticate();  //check if access token exists and is not expired
		$headers = array(
			"Authorization: Bearer $this->access_token",
			'Content-Type: application/x-www-form-urlencoded'
		);
		$ch = curl_init($url);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false); // -k
		curl_setopt($ch, CURLOPT_VERBOSE, true); // -v
		curl_setopt($ch, CURLOPT_HTTPHEADER, $headers); // -H
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true); //put result in a variable

		$json_response = curl_exec($ch);
		curl_close($ch);
		return $json_response;
	}

	//api calls
	//Mcommunity People
	/* Returns public directory information about a person with given uniqname. */
	public function get_person($uniq) {
		$apiurl = 'https://api-gw.it.umich.edu/Mcommunity/People/v1/people/'.$uniq;
		$result = $this->call_api($apiurl);
		return $result;
	}
	
	/* Returns public directory information for a search string. */
	public function search_people($query) {
		$apiurl = 'https://api-gw.it.umich.edu/Mcommunity/People/v1/people/minisearch/'.urlencode($query);
		$result = $this->call_api($apiurl);
		return $result;
	}
	
	/* Returns a smaller subset of public directory information for a search string. */
	public function search_people_compact($query) {
		$apiurl = 'https://api-gw.it.umich.edu/Mcommunity/People/v1/people/compact/search/'.urlencode($query);
		$result = $this->call_api($apiurl);
		return $result;
	}
	
	//Buildings
	/* Returns a list of campuses. */
	public function get_campuses() {
		$apiurl = 'https://api-gw.it.umich.edu/Facilities/Buildings/v1/Campuses/';
		$result = $this->call_api($apiurl);
		return $result;
	}
	
	/* Returns a list of buildings. */
	public function get_buildings($campus_code = 100) {
		$apiurl = 'https://api-gw.it.umich.edu/Facilities/Buildings/v1/Buildings?CampusCd='.$campus_code;
		$result = $this->call_api($apiurl);
		return $result;
	}
	
	/* Returns details about a specified building (campus, street address, latitude/longitude). */
	public function get_building($building_id) {
		$apiurl = 'https://api-gw.it.umich.edu/Facilities/Buildings/v1/Buildings/'.$building_id;
		$result = $this->call_api($apiurl);
		return $result;
	}
	
	/* Returns a list of buildings near a location (latitude, longitude). */
	public function get_buildings_nearby($lat = 42.2788184, $lon = -83.7361553) {
		$apiurl = 'https://api-gw.it.umich.edu/Facilities/Buildings/v1/Buildings/Nearby?Latitude='.$lat.'&Longitude='.$lon;
		$result = $this->call_api($apiurl);
		return $result;
	}
	
	
	//Schedule of Classes
	/* Returns a list of the terms that are currently active for or available for backpack and registration activities. */
	public function get_terms() {
		$apiurl = 'https://api-gw.it.umich.edu/Curriculum/SOC/v1/Terms/';
		$result = $this->call_api($apiurl);
		return $result;
	}
	
	/* Flexible searching of the course catalog. */
	public function search_classes($term_code, $query) {
		$apiurl = 'https://api-gw.it.umich.edu/Curriculum/SOC/v1/Terms/'.$term_code.'/Classes/Search/'.urlencode($query);
		$result = $this->call_api($apiurl);
		return $result;
	}
	
	/* Flexible searching of the course catalog. */
	public function get_class($term_code, $class_number) {
		$apiurl = 'https://api-gw.it.umich.edu/Curriculum/SOC/v1/Terms/'.$term_code.'/Classes/'.$class_number;
		$result = $this->call_api($apiurl);
		return $result;
	}
	
	/* Returns a list of schools/colleges for the Ann Arbor campus. */
	public function get_schools($term_code) {
		$apiurl = 'https://api-gw.it.umich.edu/Curriculum/SOC/v1/Terms/'.$term_code.'/Schools/';
		$result = $this->call_api($apiurl);
		return $result;
	}
	
	/* Returns a list of subjects for a particular term and school/college. */
	public function get_subjects($term_code, $school_code) {
		$apiurl = 'https://api-gw.it.umich.edu/Curriculum/SOC/v1/Terms/'.$term_code.'/Schools/'.$school_code.'/Subjects/';
		$result = $this->call_api($apiurl);
		return $result;
	}
	
	/* Returns a list of catalog numbers, returns the catalog number and course description. */
	public function get_catalogs($term_code, $school_code, $subject_code) {
		$apiurl = 'https://api-gw.it.umich.edu/Curriculum/SOC/v1/Terms/'.$term_code.'/Schools/'.$school_code.'/Subjects/'.$subject_code.'/CatalogNbrs/';
		$result = $this->call_api($apiurl);
		return $result;
	}
	
	/* Returns the title and description of course by term, school, subject, catalog. */
	public function get_course_description($term_code, $school_code, $subject_code, $catalog_number) {
		$apiurl = 'https://api-gw.it.umich.edu/Curriculum/SOC/v1/Terms/'.$term_code.'/Schools/'.$school_code.'/Subjects/'.$subject_code.'/CatalogNbrs/'.$catalog_number;
		$result = $this->call_api($apiurl);
		return $result;
	}
	
	/* Returns a list of class sections for a course by term, school, subject, catalog. */
	public function get_sections($term_code, $school_code, $subject_code, $catalog_number) {
		$apiurl = 'https://api-gw.it.umich.edu/Curriculum/SOC/v1/Terms/'.$term_code.'/Schools/'.$school_code.'/Subjects/'.$subject_code.'/CatalogNbrs/'.$catalog_number.'/Sections/';
		$result = $this->call_api($apiurl);
		return $result;
	}
	
	/* Returns details about a single class section for a course by term, school, subject, catalog, section. */
	public function get_course_section($term_code, $school_code, $subject_code, $catalog_number, $section_number) {
		$apiurl = 'https://api-gw.it.umich.edu/Curriculum/SOC/v1/Terms/'.$term_code.'/Schools/'.$school_code.'/Subjects/'.$subject_code.'/CatalogNbrs/'.$catalog_number.'/Sections/'.$section_number;
		$result = $this->call_api($apiurl);
		return $result;
	}
	
	/* Returns details about a single class section for a course by term, school, subject, catalog, section. */
	public function get_course_meetings($term_code, $school_code, $subject_code, $catalog_number, $section_number) {
		$apiurl = 'https://api-gw.it.umich.edu/Curriculum/SOC/v1/Terms/'.$term_code.'/Schools/'.$school_code.'/Subjects/'.$subject_code.'/CatalogNbrs/'.$catalog_number.'/Sections/'.$section_number.'/Meetings/';
		$result = $this->call_api($apiurl);
		return $result;
	}
	
	/* Returns details about a single class section for a course by term, school, subject, catalog, section. */
	public function get_course_instructors($term_code, $school_code, $subject_code, $catalog_number, $section_number) {
		$apiurl = 'https://api-gw.it.umich.edu/Curriculum/SOC/v1/Terms/'.$term_code.'/Schools/'.$school_code.'/Subjects/'.$subject_code.'/CatalogNbrs/'.$catalog_number.'/Sections/'.$section_number.'/Instructors/';
		$result = $this->call_api($apiurl);
		return $result;
	}
	
	/* Returns details about a single class section for a course by term, school, subject, catalog, section. */
	public function get_course_textbooks($term_code, $school_code, $subject_code, $catalog_number, $section_number) {
		$apiurl = 'https://api-gw.it.umich.edu/Curriculum/SOC/v1/Terms/'.$term_code.'/Schools/'.$school_code.'/Subjects/'.$subject_code.'/CatalogNbrs/'.$catalog_number.'/Sections/'.$section_number.'/Textbooks/';
		$result = $this->call_api($apiurl);
		return $result;
	}

} //end class

?>
