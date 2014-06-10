<?php
/**
 * @class  SyndicationPing
 * @author sol (ngleader@gmail.com)
 * @brief  Syndication Ping Request 클래스
 **/
class SyndicationPing
{
	// Ping Server
	var $ping_host = 'syndication.openapi.naver.com';
	var $client;
	var $type = 'article';
	var $id;
	var $start_time;
	var $end_time;
	var $max_entry;
	var $page;

	function setId($id)
	{
		$this->id = $id;
	}

	function setType($type)
	{
		$this->type = $type;	
	}

	function setStartTime($start_time)
	{
		if($start_time)
		{
			$this->start_time = $this->_convertTime($start_time);
		}
	}

	function setEndTime($end_time)
	{
		if($end_time)
		{
			$this->end_time = $this->_convertTime($end_time);
		}
	}

	function _convertTime($time)
	{
		return  str_replace('+','%2b',$time);
	}

	function setMaxEntry($max_entry)
	{
		if($max_entry > 0 && $max_entry <= 10000)
		{
			$this->max_entry = $max_entry;
		}
	}

	function setPage($page)
	{
		if($page > 0 && $page <= 10000)
		{
			$this->page = $page;
		}
	}

	function getBody()
	{		
		$str = sprintf( '%s?id=%s&type=%s', $GLOBALS['syndi_echo_url'], $this->id, $this->type );
		
		if($this->start_time && $this->end_time)
		{
			$params['start_time'] = $this->start_time;
			$params['end_time'] = $this->end_time;
		}
		if($this->max_entry) $params['max_entry'] = $this->max_entry;
		if($this->page) $params['page'] = $this->page;
		
		if( count($params) )
			$str .= '?' . http_build_query( $params );

		return 'link=' . urlencode( $str );
	}

	function request()
	{
		$body = $this->getBody();
		if(!$body) return false;
		
		
		/*
		$arg = array(
			'method' => 'POST',
			'body' => $this->getBody(),
			'header' => array(
					'User-Agent' => 'request',
					'Content-Type' => 'application/x-www-form-urlencoded',
					'Content-Length' => strlen($body),
			)
		);
		
		if ( class_exists( 'WP_Http_Fsockopen' ) ){
			$result = WP_Http_Fsockopen::request($this->ping_host, $arg);
			//echo $body;
			//var_dump($result);
		}
		

		$response = wp_remote_post($this->ping_host, array(
			'method' => 'POST',
			'timeout' => 5,
			'httpversion' => '1.0',
			'headers' => array(
				'user-agent' => 'request',
				'content-type' => 'application/x-www-form-urlencoded',
				'content-length' => strlen($body),
			),
			'body' => $body,
		));
		
		if ( is_wp_error( $response ) ) {
		   $error_message = $response->get_error_message();
		   echo "Something went wrong: $error_message";
		} else {
			//echo $body;
			return true;
		   	echo 'Response:<pre>';
		   	print_r( $response );
		   	echo '</pre>';
		}
		*/

		

		$header = "POST /ping/ HTTP/1.0\r\n".
				"User-Agent: request\r\n".
				"Host: " . $this->ping_host . "\r\n".
				"Content-Type: application/x-www-form-urlencoded\r\n".
				"Content-Length: ". strlen($body) ."\r\n".
				"\r\n".
				$body;

		$fp = fsockopen($this->ping_host, '80', $errno, $errstr, 5); 
		if(!$fp) return false;

		fputs($fp, $header, strlen($header));
		/*
	    while (!feof($fp)) {
	        $response .= fgets($fp, 100);
	    }
	    echo "<xmp>$response</xmp>";   //display response SOAP
	    echo $body;
	    */
	    
	    //echo urldecode($body);
	   	
		fclose($fp);

		return true;

	}
}

?>
