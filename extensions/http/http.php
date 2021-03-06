<?php

	/**
	 * Http Extension
	 *
	 * @package Scabbia
	 * @subpackage http
	 * @version 1.0.2
	 *
	 * @scabbia-fwversion 1.0
	 * @scabbia-fwdepends string
	 * @scabbia-phpversion 5.2.0
	 * @scabbia-phpdepends
	 */
	class http {
		/**
		 * @ignore
		 */
		public static $platform = null;
		/**
		 * @ignore
		 */
		public static $crawler = null;
		/**
		 * @ignore
		 */
		public static $crawlerType = null;
		/**
		 * @ignore
		 */
		public static $isSecure = false;
		/**
		 * @ignore
		 */
		public static $isAjax = false;
		/**
		 * @ignore
		 */
		public static $queryString;
		/**
		 * @ignore
		 */
		public static $method;
		/**
		 * @ignore
		 */
		public static $methodext;
		/**
		 * @ignore
		 */
		public static $isBrowser = false;
		/**
		 * @ignore
		 */
		public static $isRobot = false;
		/**
		 * @ignore
		 */
		public static $isMobile = false;
		/**
		 * @ignore
		 */
		public static $languages = array();
		/**
		 * @ignore
		 */
		public static $contentTypes = array();
		/**
		 * @ignore
		 */
		public static $callbacks = array();

		/**
		 * @ignore
		 */
		public static function extension_load() {
			// session trans sid
			ini_set('session.use_trans_sid', '0');

			// required for IE in iframe facebook environments if sessions are to work.
			// header('P3P:CP="IDC DSP COR ADM DEVi TAIi PSA PSD IVAi IVDi CONi HIS OUR IND CNT"');

			// replace missing environment variables
/*
			static $aEnvNames = array(
				'HTTP_ACCEPT',
				'HTTP_ACCEPT_LANGUAGE',
				'HTTP_HOST',
				'HTTP_USER_AGENT',
				'HTTP_REFERER',
				'SCRIPT_FILENAME',
				'PHP_SELF',
				'QUERY_STRING',
				'REQUEST_URI',
				'SERVER_ADDR',
				'SERVER_NAME',
				'SERVER_PORT',
				'SERVER_PROTOCOL',
				'HTTPS'
			);

			foreach($aEnvNames as &$tEnv) {
				if(isset($_SERVER[$tEnv])) { // && strlen($_SERVER[$tEnv]) > 0
					continue;
				}

				$_SERVER[$tEnv] = getenv($tEnv) or $_SERVER[$tEnv] = '';
			}
*/

			if(isset($_SERVER['HTTP_CLIENT_IP'])) {
				$_SERVER['REMOTE_ADDR'] = $_SERVER['HTTP_CLIENT_IP'];
			}
			else {
				if(!isset($_SERVER['REMOTE_ADDR']) && isset($_SERVER['HTTP_X_FORWARDED_FOR'])) {
					$_SERVER['REMOTE_ADDR'] = $_SERVER['HTTP_X_FORWARDED_FOR'];
				}
				else {
					$_SERVER['REMOTE_ADDR'] = getenv('REMOTE_ADDR') or $_SERVER['REMOTE_ADDR'] = '0.0.0.0';
				}
			}

			// request handling
			// $tRestOfRequest = substr($_SERVER['REQUEST_URI'], strlen($_SERVER['SCRIPT_NAME']));

			if(isset($_SERVER['HTTPS']) && ($_SERVER['HTTPS'] == '1' || $_SERVER['HTTPS'] == 'on')) {
				self::$isSecure = true;
			}

			if(!isset($_SERVER['SERVER_PROTOCOL']) || $_SERVER['SERVER_PROTOCOL'] != 'HTTP/1.1') {
				$_SERVER['SERVER_PROTOCOL'] = 'HTTP/1.0';
			}

			if(!isset($_SERVER['HTTP_HOST']) || strlen($_SERVER['HTTP_HOST']) == 0) {
				$_SERVER['HTTP_HOST'] = isset($_SERVER['SERVER_NAME']) ? $_SERVER['SERVER_NAME'] : $_SERVER['SERVER_ADDR'];

				if(isset($_SERVER['SERVER_PORT']) && $_SERVER['SERVER_PORT'] != '80') {
					$_SERVER['HTTP_HOST'] .= $_SERVER['SERVER_PORT'];
				}
			}

			self::$method = strtolower($_SERVER['REQUEST_METHOD']);
			self::$methodext = self::$method;

			if(isset($_SERVER['HTTP_X_REQUESTED_WITH']) && $_SERVER['HTTP_X_REQUESTED_WITH'] == 'XMLHttpRequest') {
				self::$isAjax = true;
				self::$methodext .= 'ajax';
			}

			if(config::get('/http/userAgents/autoCheck', '1') == '1') {
				self::checkUserAgent();
			}

			// self::$browser = get_browser(null, true);
			self::$languages = self::parseHeaderString($_SERVER['HTTP_ACCEPT_LANGUAGE'], true);
			self::$contentTypes = self::parseHeaderString($_SERVER['HTTP_ACCEPT'], true);

			self::$queryString = $_SERVER['QUERY_STRING'];
			foreach(config::get('/http/rewriteList', array()) as $tRewriteList) {
				if(self::rewrite($tRewriteList['match'], $tRewriteList['forward'], (isset($tRewriteList['limitMethods']) ? array_keys($tRewriteList['limitMethods']) : null))) {
					break;
				}
			}

			$_GET = self::parseGet(self::$queryString);

			$_REQUEST = array_merge($_GET, $_POST, $_COOKIE); // GPC Order w/o session vars.

			events::register('run', 'http::run');
			events::register('output', 'http::output');
		}

		/**
		 * @ignore
		 */
		public static function run($uParms) {
			$tAutoRun = intval(config::get('/http/autorun', '1'));
			if(!$tAutoRun) {
				return;
			}

			if(extensions::isLoaded('profiler')) {
				profiler::start('http', array('action' => 'routing'));
			}

			$tParms = array(
						  'queryString' => &self::$queryString,
						  'get' => &$_GET
					);

			foreach(self::$callbacks as &$tCallback) {
				if(!is_null($tCallback[2]) && !in_array(self::$methodext, $tCallback[2])) {
					continue;
				}

				if(preg_match('|^' . ltrim($tCallback[0], '/') . '$|', self::$queryString, $tMatches)) {
					$tCallbackToCall = $tCallback[1];
					break;
				}
			}

			if(isset($tCallbackToCall)) {
				array_shift($tMatches);
				call_user_func_array($tCallbackToCall, $tMatches);
			}
			else {
				events::invoke('http_route', $tParms);
			}

			if(extensions::isLoaded('profiler')) {
				profiler::stop();
			}
		}

		/**
		 * @ignore
		 */
		public static function addCallback($uMatch, $uCallback) {
			if(!is_array($uMatch)) {
				$uMatch = array($uMatch);
			}

			foreach($uMatch as $tMatch) {
				$tParts = explode(' ', $tMatch, 2);

				$tLimitMethods = ((count($tParts) > 1) ? explode(',', strtolower(array_shift($tParts))) : null);

				self::$callbacks[] = array($tParts[0], $uCallback, $tLimitMethods);
			}
		}

		/**
		 * @ignore
		 */
		public static function rewrite($uMatch, $uForward, $uLimitMethods = null) {
			if(!is_null($uLimitMethods) && !in_array(self::$methodext, $uLimitMethods)) {
				return false;
			}

			$tReturn = preg_replace('|^' . $uMatch . '$|', $uForward, self::$queryString, -1, $tCount);
			if($tCount > 0) {
				self::$queryString = $tReturn;
				return true;
			}

			return false;
		}

		/**
		 * @ignore
		 */
		public static function url($uPath) {
			$tParms = array(
				'siteroot' => framework::$siteroot,
				'device' => self::$crawlerType,
				'path' => $uPath
			);

			events::invoke('http_url', $tParms);

			return string::format(config::get('/http/link', '{@siteroot}/{@path}'), $tParms);
		}

		/**
		 * @ignore
		 */
		public static function output($uParms) {
			if(self::$isAjax) {
				$tLastContentType = http::sentHeaderValue('Content-Type');
				$tContent = '{ "isSuccess": ' . (($uParms['error'][0] > 0) ? 'false' : 'true')
					. ', "errorMessage": ' . (is_null($uParms['error']) ? 'null' : string::dquote($uParms['error'][1], true));

				if($tLastContentType == false) {
					self::sendHeader('Content-Type', 'application/json', true);

					$tContent .= ', "object": ' . json_encode($uParms['content']);
				}
				else {
					$tContent .= ', "object": ' . $uParms['content'];
				}

				$tContent .= ' }';

				$uParms['content'] = $tContent;
			}
		}

		/**
		 * @ignore
		 */
		public static function checkUserAgent() {
			foreach(config::get('/http/userAgents/platformList', array()) as $tPlatformList) {
				if(preg_match('/' . $tPlatformList['match'] . '/i', $_SERVER['HTTP_USER_AGENT'])) {
					self::$platform = $tPlatformList['name'];
					break;
				}
			}

			foreach(config::get('/http/userAgents/crawlerList', array()) as $tCrawlerList) {
				if(preg_match('/' . $tCrawlerList['match'] . '/i', $_SERVER['HTTP_USER_AGENT'])) {
					self::$crawler = $tCrawlerList['name'];
					self::$crawlerType = $tCrawlerList['type'];

					switch($tCrawlerList['type']) {
					case 'bot':
						self::$isRobot = true;
						break;
					case 'mobile':
						self::$isMobile = true;
						break;
					case 'browser':
					default:
						self::$isBrowser = true;
						break;
					}

					break;
				}
			}
		}

		/**
		 * @ignore
		 */
		public static function checkLanguage($uLanguage = null) {
			if(is_null($uLanguage)) {
				return self::$languages;
			}

			return in_array(strtolower($uLanguage), self::$languages);
		}

		/**
		 * @ignore
		 */
		public static function checkContentType($uContentType = null) {
			if(is_null($uContentType)) {
				return self::$contentTypes;
			}

			return in_array(strtolower($uContentType), self::$contentTypes);
		}

//		public static function is($uType) {
//			$tType = 'is' . ucfirst($uType);
//			return self::${$tType};
//		}
//
//		public static function __callStatic($uMethod, $uArgs) {
//			return self::${$uMethod};
//		}

		/**
		 * @ignore
		 */
		public static function &xss(&$uString) {
			if(is_string($uString)) {
				$tString = str_replace(array('<', '>', '"', '\'', '$', '(', ')', '%28', '%29'), array('&#60;', '&#62;', '&#34;', '&#39;', '&#36;', '&#40;', '&#41;', '&#40;', '&#41;'), $uString); // '&' => '&#38;'
				return $tString;
			}

			return $uString;
		}

		/**
		 * @ignore
		 */
		public static function encode($uString) {
			return urlencode($uString);
		}

		/**
		 * @ignore
		 */
		public static function decode($uString) {
			return urldecode($uString);
		}

		/**
		 * @ignore
		 */
		public static function encodeArray($uArray) {
			$tReturn = array();

			foreach($uArray as $tKey => &$tValue) {
				$tReturn[] = urlencode($tKey) . '=' . urlencode($tValue);
			}

			return implode('&', $tReturn);
		}

		/**
		 * @ignore
		 */
		public static function copyStream($tFilename) {
			$tInput = fopen('php://input', 'rb');
			$tOutput = fopen($tFilename, 'wb');
			stream_copy_to_stream($tInput, $tOutput);
			fclose($tOutput);
			fclose($tInput);
		}

		/**
		 * @ignore
		 */
		public static function baseUrl() {
			if(http::$isSecure) {
				return 'https://' . $_SERVER['HTTP_HOST'];
			}

			return 'http://' . $_SERVER['HTTP_HOST'] . framework::$siteroot;
		}

		/**
		 * @ignore
		 */
		public static function secureUrl($uUrl) {
//			if(http::$isSecure && substr($uUrl, 0, 7) == 'http://') {
//				return 'https://' . substr($uUrl, 7);
//			}

			return $uUrl;
		}

		/**
		 * @ignore
		 */
		public static function sendStatus($uStatusCode) {
			$tStatus = $_SERVER['SERVER_PROTOCOL'] . ' ';

			switch($uStatusCode) {
			case 100:
				$tStatus .= '100 Continue';
				break;
			case 101:
				$tStatus .= '101 Switching Protocols';
				break;
			case 200:
				$tStatus .= '200 OK';
				break;
			case 201:
				$tStatus .= '201 Created';
				break;
			case 202:
				$tStatus .= '202 Accepted';
				break;
			case 203:
				$tStatus .= '203 Non-Authoritative Information';
				break;
			case 204:
				$tStatus .= '204 No Content';
				break;
			case 205:
				$tStatus .= '205 Reset Content';
				break;
			case 206:
				$tStatus .= '206 Partial Content';
				break;
			case 300:
				$tStatus .= '300 Multiple Choices';
				break;
			case 301:
				$tStatus .= '301 Moved Permanently';
				break;
			case 302:
				$tStatus .= '302 Found';
				break;
			case 303:
				$tStatus .= '303 See Other';
				break;
			case 304:
				$tStatus .= '304 Not Modified';
				break;
			case 305:
				$tStatus .= '305 Use Proxy';
				break;
			case 307:
				$tStatus .= '307 Temporary Redirect';
				break;
			case 400:
				$tStatus .= '400 Bad Request';
				break;
			case 401:
				$tStatus .= '401 Unauthorized';
				break;
			case 402:
				$tStatus .= '402 Payment Required';
				break;
			case 403:
				$tStatus .= '403 Forbidden';
				break;
			case 404:
				$tStatus .= '404 Not Found';
				break;
			case 405:
				$tStatus .= '405 Method Not Allowed';
				break;
			case 406:
				$tStatus .= '406 Not Acceptable';
				break;
			case 407:
				$tStatus .= '407 Proxy Authentication Required';
				break;
			case 408:
				$tStatus .= '408 Request Timeout';
				break;
			case 409:
				$tStatus .= '409 Conflict';
				break;
			case 410:
				$tStatus .= '410 Gone';
				break;
			case 411:
				$tStatus .= '411 Length Required';
				break;
			case 412:
				$tStatus .= '412 Precondition Failed';
				break;
			case 413:
				$tStatus .= '413 Request Entity Too Large';
				break;
			case 414:
				$tStatus .= '414 Request-URI Too Long';
				break;
			case 415:
				$tStatus .= '415 Unsupported Media Type';
				break;
			case 416:
				$tStatus .= '416 Requested Range Not Satisfiable';
				break;
			case 417:
				$tStatus .= '417 Expectation Failed';
				break;
			case 500:
				$tStatus .= '500 Internal Server Error';
				break;
			case 501:
				$tStatus .= '501 Not Implemented';
				break;
			case 502:
				$tStatus .= '502 Bad Gateway';
				break;
			case 503:
				$tStatus .= '503 Service Unavailable';
				break;
			case 504:
				$tStatus .= '504 Gateway Timeout';
				break;
			case 505:
				$tStatus .= '505 HTTP Version Not Supported';
				break;
			default:
				return;
			}

			header($tStatus, true, $uStatusCode);
		}

		/**
		 * @ignore
		 */
		public static function sendHeader($uHeader, $uValue = null, $uReplace = false) {
			if(isset($uValue)) {
				header($uHeader . ': ' . $uValue, $uReplace);
			}
			else {
				header($uHeader, $uReplace);
			}
		}

		/**
		 * @ignore
		 */
		public static function sentHeaderValue($uKey) {
			foreach(headers_list() as $tHeaderRow) {
				$tHeader = explode(': ', $tHeaderRow, 2);

				if(count($tHeader) < 2) {
					continue;
				}

				if(strcasecmp($tHeader[0], $uKey) == 0) {
					return $tHeader[1];
				}
			}

			return false;
		}

		/**
		 * @ignore
		 */
		public static function sendFile($uFilePath, $uAttachment = false, $uFindMimeType = true) {
			$tExtension = pathinfo($uFilePath, PATHINFO_EXTENSION);

			if($uFindMimeType && extensions::isLoaded('io')) {
				$tType = io::getMimeType($tExtension);
			}
			else {
				$tType = 'application/octet-stream';
			}

			self::sendHeaderCache(-1);
			self::sendHeader('Accept-Ranges', 'bytes', true);
			self::sendHeader('Content-Type', $tType, true);
			if($uAttachment) {
				self::sendHeader('Content-Disposition', 'attachment; filename=' . pathinfo($uFilePath, PATHINFO_BASENAME) . ';', true);
			}
			self::sendHeader('Content-Transfer-Encoding', 'binary', true);
			//! filesize problem
			// self::sendHeader('Content-Length', filesize($uFilePath), true);
			self::sendHeaderETag(md5_file($uFilePath));
			readfile($uFilePath, false);
			framework::end(0);
		}

		/**
		 * @ignore
		 */
		public static function sendHeaderLastModified($uTime, $uNotModified = false) {
			self::sendHeader('Last-Modified', gmdate('D, d M Y H:i:s', $uTime) . ' GMT', true);

			if($uNotModified) {
				self::sendStatus(304);
			}
		}

		/**
		 * @ignore
		 */
		public static function sendRedirect($uLocation, $uTerminate = true) {
			self::sendHeader('Location', $uLocation, true);

			if($uTerminate) {
				framework::end(0);
			}
		}

		/**
		 * @ignore
		 */
		public static function sendRedirectPermanent($uLocation, $uTerminate = true) {
			self::sendStatus(301);
			self::sendHeader('Location', $uLocation, true);

			if($uTerminate) {
				framework::end(0);
			}
		}

		/**
		 * @ignore
		 */
		public static function sendHeaderETag($uHash) {
			self::sendHeader('ETag', '"' . $uHash . '"', true);
		}

		/**
		 * @ignore
		 */
		public static function sendHeaderCache($uTtl = -1, $uPublic = true, $uMustRevalidate = false) {
			if($uTtl < 0) {
				if($_SERVER['SERVER_PROTOCOL'] != 'HTTP/1.1') { // http/1.0 only
					self::sendHeader('Pragma', 'no-cache', true);
					self::sendHeader('Expires', 'Thu, 01 Jan 1970 00:00:00 GMT', true);
					return;
				}

				self::sendHeader('Cache-Control', (($uMustRevalidate) ? 'no-store, no-cache, must-revalidate' : 'no-store, no-cache'), true);
				return;
			}

			if($uPublic) {
				$tPublicity = 'public';
			}
			else {
				$tPublicity = 'private';
			}

			if($_SERVER['SERVER_PROTOCOL'] != 'HTTP/1.1') { // http/1.0 only
				self::sendHeader('Pragma', $tPublicity, true);
				self::sendHeader('Expires', gmdate('D, d M Y H:i:s', time() + $uTtl) . ' GMT', true);
				return;
			}

			if($uMustRevalidate) {
				$tPublicity .= ', must-revalidate';
			}

			self::sendHeader('Cache-Control', 'max-age=' . $uTtl . ', ' . $tPublicity, true);
		}

		/**
		 * @ignore
		 */
		public static function sendCookie($uCookie, $uValue, $uExpire = 0) {
			setrawcookie($uCookie, self::encode($uValue), $uExpire);
		}

		/**
		 * @ignore
		 */
		public static function removeCookie($uCookie) {
			setrawcookie($uCookie, '', time() - 3600);
		}

		/**
		 * @ignore
		 */
		public static function &parseGet($uQueryString) {
			$tParsingType = config::get('/http/request/parsingType', '0');

			if($tParsingType == '0') {
				$uArray = array();
				parse_str($uQueryString, $uArray);
				return $uArray;
			}

			$tDefaultParameter = config::get('/http/request/getParameters', '?&');
			$tDefaultKey = config::get('/http/request/getKeys', '=');
			$tDefaultSeperator = config::get('/http/request/getSeperator', '/');

			if($tParsingType == '1') {
				return string::parseQueryString($uQueryString, $tDefaultParameter, $tDefaultKey);
			}

			if($tParsingType == '2') {
				return string::parseQueryString($uQueryString, $tDefaultParameter, $tDefaultKey, $tDefaultSeperator);
			}

			throw new Exception('request parsing error.');
		}

		/**
		 * @ignore
		 */
		public static function parseHeaderString($uString, $uLowerAll = false) {
			$tResult = array();

			foreach(explode(',', $uString) as $tPiece) {
				// pull out the language, place languages into array of full and primary
				// string structure:
				$tPiece = trim($tPiece);
				if($uLowerAll) {
					$tResult[] = strtolower(substr($tPiece, 0, strcspn($tPiece, ';')));
				}
				else {
					$tResult[] = substr($tPiece, 0, strcspn($tPiece, ';'));
				}
			}

			return $tResult;
		}

		/**
		 * @ignore
		 */
		public static function buildQueryString($uArray) {
			//! $tDefaultKey = config::get('/http/request/getKeys', '=');
			/*
			if(isset($uArray['_segments'])) {
				$tString = '/' . implode('/', $uArray['_segments']) . '?';
			}
			else {
				$tString = '?';
			}
			*/
			$tString = '?';

			foreach($uArray as $tKey => &$tItem) {
				if($tKey == '_segments' || $tKey == '_hash') {
					continue;
				}

				if(is_null($tItem)) {
					$tString .= $tKey . '&';
					continue;
				}

				$tString .= $tKey . '=' . $tItem . '&';
			}

			return substr($tString, 0, -1);
		}

		/**
		 * @ignore
		 */
		public static function get($uKey, $uDefault = null, $uFilter = null) {
			if(!array_key_exists($uKey, $_GET)) {
				return $uDefault;
			}

			if($uFilter === false) {
				return $_GET[$uKey];
			}

			if(!is_null($uFilter)) {
				$tArgs = array_slice(func_get_args(), 2);
				array_unshift($tArgs, $_GET[$uKey]);

				return call_user_func_array('string::filter', $tArgs);
			}

			return self::xss($_GET[$uKey]);
		}

		/**
		 * @ignore
		 */
		public static function post($uKey, $uDefault = null, $uFilter = null) {
			if(!array_key_exists($uKey, $_POST)) {
				return $uDefault;
			}

			if($uFilter === false) {
				return $_POST[$uKey];
			}

			if(!is_null($uFilter)) {
				$tArgs = array_slice(func_get_args(), 2);
				array_unshift($tArgs, $_POST[$uKey]);

				return call_user_func_array('string::filter', $tArgs);
			}

			return self::xss($_POST[$uKey]);
		}

		/**
		 * @ignore
		 */
		public static function cookie($uKey, $uDefault = null, $uFilter = null) {
			if(!array_key_exists($uKey, $_COOKIE)) {
				return $uDefault;
			}

			if($uFilter === false) {
				return $_COOKIE[$uKey];
			}

			if(!is_null($uFilter)) {
				$tArgs = array_slice(func_get_args(), 2);
				array_unshift($tArgs, $_COOKIE[$uKey]);

				return call_user_func_array('string::filter', $tArgs);
			}

			return self::xss($_COOKIE[$uKey]);
		}


		/**
		 * @ignore
		 */
		public static function &getArray($uKeys, $uFilter = null) {
			$tValues = array();
			if(!is_null($uFilter)) {
				$tArgs = array_slice(func_get_args(), 2);
			}

			foreach($uKeys as &$tKey) {
				if(!array_key_exists($tKey, $_GET)) {
					continue;
				}

				if($uFilter === false) {
					$tValues[$tKey] = $_GET[$tKey];
					continue;
				}

				if(isset($tArgs)) {
					$tNewArgs = $tArgs;
					array_unshift($tNewArgs, $_GET[$tKey]);

					$tValues[$tKey] = call_user_func_array('string::filter', $tNewArgs);
					continue;
				}

				$tValues[$tKey] = self::xss($_GET[$tKey]);
			}

			return $tValues;
		}

		/**
		 * @ignore
		 */
		public static function postArray($uKeys, $uFilter = null) {
			$tValues = array();
			if(!is_null($uFilter)) {
				$tArgs = array_slice(func_get_args(), 2);
			}

			foreach($uKeys as &$tKey) {
				if(!array_key_exists($tKey, $_POST)) {
					continue;
				}

				if($uFilter === false) {
					$tValues[$tKey] = $_POST[$tKey];
					continue;
				}

				if(isset($tArgs)) {
					$tNewArgs = $tArgs;
					array_unshift($tNewArgs, $_POST[$tKey]);

					$tValues[$tKey] = call_user_func_array('string::filter', $tNewArgs);
					continue;
				}

				$tValues[$tKey] = self::xss($_POST[$tKey]);
			}

			return $tValues;
		}

		/**
		 * @ignore
		 */
		public static function cookieArray($uKeys, $uFilter = null) {
			$tValues = array();
			if(!is_null($uFilter)) {
				$tArgs = array_slice(func_get_args(), 2);
			}

			foreach($uKeys as &$tKey) {
				if(!array_key_exists($tKey, $_COOKIE)) {
					continue;
				}

				if($uFilter === false) {
					$tValues[$tKey] = $_COOKIE[$tKey];
					continue;
				}

				if(isset($tArgs)) {
					$tNewArgs = $tArgs;
					array_unshift($tNewArgs, $_COOKIE[$tKey]);

					$tValues[$tKey] = call_user_func_array('string::filter', $tNewArgs);
					continue;
				}

				$tValues[$tKey] = self::xss($_COOKIE[$tKey]);
			}

			return $tValues;
		}
	}

?>