<?
/**
 * Class for verifying Yubico One-Time-Passcodes
 *
 * @category    Auth
 * @package     Auth_Yubico
 * @author      Simon Josefsson <simon@yubico.com>, Olov Danielson <olov@yubico.com>, Alex Amiryan <alex@amiryan.org>
 * @copyright   2007, 2008, 2009, 2010 Yubico AB, 2011
 * @license     http://opensource.org/licenses/bsd-license.php New BSD License
 * @version     2.0
 * @link        http://www.yubico.com/
 */

class Yubikey{
	/**#@+
	 * @access private
	 */
	
	/**
	 * Yubico client ID
	 * @var string
	 */
	protected $_id;
	
	/**
	 * Yubico client key
	 * @var string
	 */
	protected $_key;
	
	/**
	 * URL part of validation server
	 * @var string
	 */
	protected $_url;
	
	/**
	 * List with URL part of validation servers
	 * @var array
	 */
	protected $_url_list;
	
	/**
	 * index to _url_list
	 * @var int
	 */
	protected $_url_index;
	
	/**
	 * Last query to server
	 * @var string
	 */
	protected $_lastquery;
	
	/**
	 * Response from server
	 * @var string
	 */
	protected $_response;
	
	/**
	 * Flag whether to use https or not.
	 * @var boolean
	 */
	protected $_https;
	
	/**
	 * Flag whether to verify HTTPS server certificates or not.
	 * @var boolean
	 */
	protected $_httpsverify;
	
	/**
	 * Constructor
	 *
	 * Sets up the object
	 * @param    string  $id     The client identity
	 * @param    string  $key    The client MAC key (optional)
	 * @param    boolean $https  Flag whether to use https (optional)
	 * @param    boolean $httpsverify  Flag whether to use verify HTTPS
	 * server certificates (optional,
	 * default true)
	 * @access public
	 */
	public function __construct($id, $key = '', $https = true, $httpsverify = true){
		if(empty($id)){
			throw new InvalidArgumentException("Invalid API ID specified!");
		}
		$this->_id = $id;
		$this->_key = base64_decode($key);
		$this->_https = $https;
		$this->_httpsverify = $httpsverify;
	}
	
	/**
	 * Specify to use a different URL part for verification.
	 * The default is "api.yubico.com/wsapi/verify".
	 *
	 * @param  string $url  New server URL part to use
	 * @access public
	 */
	public function setURLpart($url){
		if(empty($url)){
			throw new InvalidArgumentException("Empty \$url specified!");
		}
		
		$this->_url = $url;
	}
	
	/**
	 * Get URL part to use for validation.
	 *
	 * @return string  Server URL part
	 * @access public
	 */
	public function getURLpart(){
		if($this->_url){
			return $this->_url;
		}
		else{
			return "api.yubico.com/wsapi/verify";
		}
	}
	
	/**
	 * Get next URL part from list to use for validation.
	 *
	 * @return mixed string with URL part of false if no more URLs in list
	 * @access public
	 */
	public function getNextURLpart(){
		if($this->_url_list){
			$url_list = $this->_url_list;
		}
		else{
			$url_list = array(
					'api.yubico.com/wsapi/2.0/verify', 
					'api2.yubico.com/wsapi/2.0/verify', 
					'api3.yubico.com/wsapi/2.0/verify', 
					'api4.yubico.com/wsapi/2.0/verify', 
					'api5.yubico.com/wsapi/2.0/verify');
		}
		
		if($this->_url_index >= count($url_list)){
			return false;
		}
		else{
			return $url_list[$this->_url_index++];
		}
	}
	
	/**
	 * Resets index to URL list
	 *
	 * @access public
	 */
	public function URLreset(){
		$this->_url_index = 0;
	}
	
	/**
	 * Add another URLpart.
	 *
	 * @access public
	 */
	public function addURLpart($URLpart){
		if(empty($URLpart)){
			throw new InvalidArgumentException("Empty \$URLpart specified!");
		}
		
		$this->_url_list[] = $URLpart;
	}
	
	/**
	 * Return the last query sent to the server, if any.
	 *
	 * @return string  Request to server
	 * @access public
	 */
	public function getLastQuery(){
		return $this->_lastquery;
	}
	
	/**
	 * Return the last data received from the server, if any.
	 *
	 * @return string  Output from server
	 * @access public
	 */
	public function getLastResponse(){
		return $this->_response;
	}
	
	/**
	 * Parse input string into password, yubikey prefix,
	 * ciphertext, and OTP.
	 *
	 * @param  string    Input string to parse
	 * @param  string    Optional delimiter re-class, default is '[:]'
	 * @return array     Keyed array with fields
	 * @access public
	 */
	public function parsePasswordOTP($str, $delim = '[:]'){
		if(!preg_match("/^((.*)" . $delim . ")?" . "(([cbdefghijklnrtuvCBDEFGHIJKLNRTUV]{0,16})" . "([cbdefghijklnrtuvCBDEFGHIJKLNRTUV]{32}))$/", $str, $matches)){
			return false;
		}
		$ret['password'] = $matches[2];
		$ret['otp'] = $matches[3];
		$ret['prefix'] = $matches[4];
		$ret['ciphertext'] = $matches[5];
		return $ret;
	}
	
	/**
	 * Parse parameters from last response
	 *
	 * example: getParameters("timestamp", "sessioncounter", "sessionuse");
	 *
	 * @param  array @parameters  Array with strings representing
	 * parameters to parse
	 * @return array  parameter array from last response
	 * @access public
	 */
	public function getParameters($parameters){
		if($parameters == null){
			$parameters = array(
					'timestamp', 
					'sessioncounter', 
					'sessionuse');
		}
		$param_array = array();
		foreach($parameters as $param){
			if(!preg_match("/" . $param . "=([0-9]+)/", $this->_response, $out)){
				throw new YubikeyException('Could not parse parameter ' . $param . ' from response');
			}
			$param_array[$param] = $out[1];
		}
		return $param_array;
	}
	
	/**
	 * Verify Yubico OTP against multiple URLs
	 * Protocol specification 2.0 is used to construct validation requests
	 *
	 * @param string $token        Yubico OTP
	 * @param int $use_timestamp   1=>send request with &timestamp=1 to
	 * get timestamp and session information
	 * in the response
	 * @param boolean $wait_for_all  If true, wait until all
	 * servers responds (for debugging)
	 * @param string $sl           Sync level in percentage between 0
	 * and 100 or "fast" or "secure".
	 * @param int $timeout         Max number of seconds to wait
	 * for responses
	 * @return mixed               PEAR error on error, true otherwise
	 * @access public
	 */
	public function verify($token, $use_timestamp = null, $wait_for_all = False, $sl = null, $timeout = null){
		/* Construct parameters string */
		$ret = $this->parsePasswordOTP($token);
		if(!$ret){
			throw new YubikeyException('Could not parse Yubikey OTP');
		}
		$params = array(
				'id' => $this->_id, 
				'otp' => $ret['otp'], 
				'nonce' => md5(uniqid(rand())));
		/* Take care of protocol version 2 parameters */
		if($use_timestamp){
			$params['timestamp'] = 1;
		}
		if($sl){
			$params['sl'] = $sl;
		}
		if($timeout){
			$params['timeout'] = $timeout;
		}
		ksort($params);
		$parameters = '';
		foreach($params as $p => $v){
			$parameters .= "&" . $p . "=" . $v;
		}
		$parameters = ltrim($parameters, "&");
		
		/* Generate signature. */
		if($this->_key != ""){
			$signature = base64_encode(hash_hmac('sha1', $parameters, $this->_key, true));
			$signature = preg_replace('/\+/', '%2B', $signature);
			$parameters .= '&h=' . $signature;
		}
		
		/* Generate and prepare request. */
		$this->_lastquery = null;
		$this->URLreset();
		$mh = curl_multi_init();
		$ch = array();
		while(($URLpart = $this->getNextURLpart()) != false){
			/* Support https. */
			if($this->_https){
				$query = "https://";
			}
			else{
				$query = "http://";
			}
			$query .= $URLpart . "?" . $parameters;
			
			if($this->_lastquery){
				$this->_lastquery .= " ";
			}
			$this->_lastquery .= $query;
			
			$handle = curl_init($query);
			curl_setopt($handle, CURLOPT_USERAGENT, "PEAR Auth_Yubico");
			curl_setopt($handle, CURLOPT_RETURNTRANSFER, 1);
			if(!$this->_httpsverify){
				curl_setopt($handle, CURLOPT_SSL_VERIFYPEER, 0);
			}
			curl_setopt($handle, CURLOPT_FAILONERROR, true);
			
			/* If timeout is set, we better apply it here as well
	         	in case the validation server fails to follow it. 
			*/
			if($timeout) curl_setopt($handle, CURLOPT_TIMEOUT, $timeout);
			curl_multi_add_handle($mh, $handle);
			
			$ch[$handle] = $handle;
		}
		
		/* Execute and read request. */
		$this->_response = null;
		$replay = False;
		$valid = False;
		do{
			/* Let curl do its work. */
			while(($mrc = curl_multi_exec($mh, $active)) == CURLM_CALL_MULTI_PERFORM)
				;
			
			while(($info = curl_multi_info_read($mh)) != false){
				if($info['result'] == CURLE_OK){
					
					/* We have a complete response from one server. */
					
					$str = curl_multi_getcontent($info['handle']);
					$cinfo = curl_getinfo($info['handle']);
					
					if($wait_for_all){ # Better debug info
						$this->_response .= 'URL=' . $cinfo['url'] . "\n" . $str . "\n";
					}
					
					if(preg_match("/status=([a-zA-Z0-9_]+)/", $str, $out)){
						$status = $out[1];
						
						/* 
						* There are 3 cases.
						*
						* 1. OTP or Nonce values doesn't match - ignore
						* response.
						*
						* 2. We have a HMAC key.  If signature is invalid -
						* ignore response.  Return if status=OK or
						* status=REPLAYED_OTP.
						*
						* 3. Return if status=OK or status=REPLAYED_OTP.
						*/
						if(!preg_match("/otp=" . $params['otp'] . "/", $str) || !preg_match("/nonce=" . $params['nonce'] . "/", $str)){
							/* Case 1. Ignore response. */
						}
						elseif($this->_key != ""){
							/* Case 2. Verify signature first */
							$rows = explode("\r\n", $str);
							$response = array();
							while((list($key, $val) = each($rows)) != false){
								/* = is also used in BASE64 encoding so we only replace the first = by # which is not used in BASE64 */
								$val = preg_replace('/=/', '#', $val, 1);
								$row = explode("#", $val);
								$response[$row[0]] = $row[1];
							}
							
							$parameters = array(
									'nonce', 
									'otp', 
									'sessioncounter', 
									'sessionuse', 
									'sl', 
									'status', 
									't', 
									'timeout', 
									'timestamp');
							sort($parameters);
							$check = Null;
							foreach($parameters as $param){
								if($response[$param] != null){
									if($check) $check = $check . '&';
									$check = $check . $param . '=' . $response[$param];
								}
							}
							
							$checksignature = base64_encode(hash_hmac('sha1', utf8_encode($check), $this->_key, true));
							
							if($response[h] == $checksignature){
								if($status == 'REPLAYED_OTP'){
									if(!$wait_for_all){
										$this->_response = $str;
									}
									$replay = True;
								}
								if($status == 'OK'){
									if(!$wait_for_all){
										$this->_response = $str;
									}
									$valid = True;
								}
							}
						}
						else{
							/* Case 3. We check the status directly */
							if($status == 'REPLAYED_OTP'){
								if(!$wait_for_all){
									$this->_response = $str;
								}
								$replay = True;
							}
							if($status == 'OK'){
								if(!$wait_for_all){
									$this->_response = $str;
								}
								$valid = True;
							}
						}
					}
					if(!$wait_for_all && ($valid || $replay)){
						/* We have status=OK or status=REPLAYED_OTP, return. */
						foreach($ch as $h){
							curl_multi_remove_handle($mh, $h);
							curl_close($h);
						}
						curl_multi_close($mh);
						if($replay){
							throw new YubikeyException('REPLAYED_OTP');
						}
						if($valid){
							return true;
						}
						throw new YubikeyException($status);
					}
					
					curl_multi_remove_handle($mh, $info['handle']);
					curl_close($info['handle']);
					unset($ch[$info['handle']]);
				}
				curl_multi_select($mh);
			}
		}
		while($active);
		
		/* Typically this is only reached for wait_for_all=true or
	   * when the timeout is reached and there is no
	   * OK/REPLAYED_REQUEST answer (think firewall).
	   */
		
		foreach($ch as $h){
			curl_multi_remove_handle($mh, $h);
			curl_close($h);
		}
		curl_multi_close($mh);
		
		if($replay){
			throw new YubikeyException('REPLAYED_OTP');
		}
		if($valid){
			return true;
		}
		throw new YubikeyException('NO_VALID_ANSWER');
	}
}
?>
