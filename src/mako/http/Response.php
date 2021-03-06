<?php

namespace mako\http;

use \Closure;
use \mako\core\Config;
use \mako\security\MAC;
use \mako\http\Request;
use \mako\http\responses\File;
use \mako\http\responses\Redirect;
use \mako\http\responses\Stream;
use \mako\http\responses\ResponseContainerInterface;

/**
 * HTTP response.
 *
 * @author     Frederic G. Østby
 * @copyright  (c) 2008-2013 Frederic G. Østby
 * @license    http://www.makoframework.com/license
 */

class Response
{
	//---------------------------------------------
	// Class properties
	//---------------------------------------------

	/**
	 * Request instance.
	 * 
	 * @var \mako\http\Request
	 */

	protected $request;
	
	/**
	 * Response body.
	 *
	 * @var mixed
	 */
	
	protected $body = '';

	/**
	 * Response content type.
	 * 
	 * @var string
	 */

	protected $contentType = 'text/html';

	/**
	 * Response charset.
	 * 
	 * @var string
	 */

	protected $charset = MAKO_CHARSET;

	/**
	 * Status code.
	 * 
	 * @var int
	 */

	protected $statusCode = 200;

	/**
	 * Response headers.
	 * 
	 * @var array
	 */

	protected $headers = [];

	/**
	 * Cookies.
	 * 
	 * @var array
	 */

	protected $cookies = [];

	/**
	 * Compress output?
	 * 
	 * @var boolean
	 */

	protected $outputCompression;

	/**
	 * Enable response cache?
	 * 
	 * @var boolean
	 */

	protected $responseCache;

	/**
	 * Output filters.
	 *
	 * @var array
	 */
	
	protected $outputFilters = [];
	
	/**
	 * HTTP status codes.
	 *
	 * @var array
	 */
	
	protected $statusCodes = 
	[
		// 1xx Informational
		
		'100' => 'Continue',
		'101' => 'Switching Protocols',
		'102' => 'Processing',
		
		// 2xx Success
		
		'200' => 'OK',
		'201' => 'Created',
		'202' => 'Accepted',
		'203' => 'Non-Authoritative Information',
		'204' => 'No Content',
		'205' => 'Reset Content',
		'206' => 'Partial Content',
		'207' => 'Multi-Status',
		
		// 3xx Redirection
		
		'300' => 'Multiple Choices',
		'301' => 'Moved Permanently',
		'302' => 'Found',
		'303' => 'See Other',
		'304' => 'Not Modified',
		'305' => 'Use Proxy',
		//'306' => 'Switch Proxy',
		'307' => 'Temporary Redirect',
		
		// 4xx Client Error
		
		'400' => 'Bad Request',
		'401' => 'Unauthorized',
		'402' => 'Payment Required',
		'403' => 'Forbidden',
		'404' => 'Not Found',
		'405' => 'Method Not Allowed',
		'406' => 'Not Acceptable',
		'407' => 'Proxy Authentication Required',
		'408' => 'Request Timeout',
		'409' => 'Conflict',
		'410' => 'Gone',
		'411' => 'Length Required',
		'412' => 'Precondition Failed',
		'413' => 'Request Entity Too Large',
		'414' => 'Request-URI Too Long',
		'415' => 'Unsupported Media Type',
		'416' => 'Requested Range Not Satisfiable',
		'417' => 'Expectation Failed',
		'418' => 'I\'m a teapot',
		'421' => 'There are too many connections from your internet address',
		'422' => 'Unprocessable Entity',
		'423' => 'Locked',
		'424' => 'Failed Dependency',
		'425' => 'Unordered Collection',
		'426' => 'Upgrade Required',
		'449' => 'Retry With',
		'450' => 'Blocked by Windows Parental Controls',
		
		// 5xx Server Error
		
		'500' => 'Internal Server Error',
		'501' => 'Not Implemented',
		'502' => 'Bad Gateway',
		'503' => 'Service Unavailable',
		'504' => 'Gateway Timeout',
		'505' => 'HTTP Version Not Supported',
		'506' => 'Variant Also Negotiates',
		'507' => 'Insufficient Storage',
		'509' => 'Bandwidth Limit Exceeded',
		'510' => 'Not Extended',
		'530' => 'User access denied',
	];
	
	//---------------------------------------------
	// Class constructor, destructor etc ...
	//---------------------------------------------
	
	/**
	 * Constructor.
	 *
	 * @access  public
	 * @param   \mako\http\Request  $request  Request instance
	 * @param   string              $body     (optional) Response body
	 */
	
	public function __construct(Request $request, $body = null)
	{
		$this->request = $request;

		$this->body($body);

		$config = Config::get('application');

		$this->outputCompression = $config['compress_output'];
		$this->responseCache     = $config['response_cache'];
	}
	
	//---------------------------------------------
	// Class methods
	//---------------------------------------------

	/**
	 * Sets the response body.
	 *
	 * @access  public
	 * @param   mixed                $body  Response body
	 * @return  \mako\http\Response
	 */

	public function body($body)
	{
		if($body instanceof $this)
		{
			$this->body = $body->getBody();
		}
		else
		{
			$this->body = $body;
		}

		return $this;
	}

	/**
	 * Returns the response body.
	 * 
	 * @access  public
	 * @return  mixed
	 */

	public function getBody()
	{
		return $this->body;
	}

	/**
	 * Sets the response content type.
	 * 
	 * @access  public
	 * @param   string                $contentType  Content type
	 * @param   string                $charset      (optional) Charset
	 * @return  \mako\http\Response
	 */

	public function type($contentType, $charset = null)
	{
		$this->contentType = $contentType;

		if($charset !== null)
		{
			$this->charset = $charset;
		}

		return $this;
	}

	/**
	 * Returns the response content type.
	 * 
	 * @access  public
	 * @return  string
	 */

	public function getType()
	{
		return $this->contentType;
	}

	/**
	 * Sets the response charset.
	 * 
	 * @access  public
	 * @param   string               $charset  Charset
	 * @return  \mako\http\Response
	 */

	public function charset($charset)
	{
		$this->charset = $charset;

		return $this;
	}

	/**
	 * Returns the response charset.
	 * 
	 * @access  public
	 * @return  string
	 */

	public function getCharset()
	{
		return $this->charset;
	}

	/**
	 * Sets the HTTP status code.
	 *
	 * @access  public
	 * @param   int                  $statusCode  HTTP status code
	 * @return  \mako\http\Response
	 */
	
	public function status($statusCode)
	{
		if(isset($this->statusCodes[$statusCode]))
		{
			$this->statusCode = $statusCode;
		}

		return $this;
	}

	/**
	 * Returns the HTTP status code.
	 * 
	 * @access  public
	 * @return  int
	 */

	public function getStatus()
	{
		return $this->statusCode;
	}
	
	/**
	 * Adds output filter that all output will be passed through before being sent.
	 *
	 * @access  public
	 * @param   \Closure             $filter  Closure used to filter output
	 * @return  \mako\http\Response
	 */
	
	public function filter(Closure $filter)
	{
		$this->outputFilters[] = $filter;

		return $this;
	}

	/**
	 * Returns the response filters.
	 * 
	 * @access  public
	 * @return  array
	 */

	public function getFilters()
	{
		return $this->outputFilters;
	}

	/**
	 * Clears all output filters.
	 * 
	 * @access  public
	 * @return  \mako\http\Response
	 */

	public function clearFilters()
	{
		$this->outputFilters = [];

		return $this;
	}

	/**
	 * Sets a response header.
	 * 
	 * @access  public
	 * @param   string               $name   Header name
	 * @param   string               $value  Header value
	 * @return  \mako\http\Response
	 */

	public function header($name, $value)
	{
		$this->headers[strtolower($name)] = $value;

		return $this;
	}

	/**
	 * Returns the response headers.
	 * 
	 * @access  public
	 * @return  array
	 */

	public function getHeaders()
	{
		return $this->heades;
	}

	/**
	 * Clear the response headers.
	 * 
	 * @access  public
	 * @return  \mako\http\Response
	 */

	public function clearHeaders()
	{
		$this->headers = [];

		return $this;
	}

	/**
	 * Sets an unsigned cookie.
	 *
	 * @access  public
	 * @param   string               $name     Cookie name
	 * @param   string               $value    Cookie value
	 * @param   int                  $ttl      (optional) Time to live - if omitted or set to 0 the cookie will expire when the browser closes
	 * @param   array                $options  (optional) Cookie options
	 * @return  \mako\http\Response
	 */

	public function unsignedCookie($name, $value, $ttl = 0, array $options = [])
	{
		$ttl = ($ttl > 0) ? (time() + $ttl) : 0;

		$defaults = ['path' => '/', 'domain' => '', 'secure' => false, 'httponly' => false];

		$this->cookies[] = ['name' => $name, 'value' => $value, 'ttl' => $ttl] + $options + $defaults;

		return $this;
	}

	/**
	 * Sets a signed cookie.
	 *
	 * @access  public
	 * @param   string               $name     Cookie name
	 * @param   string               $value    Cookie value
	 * @param   int                  $ttl      (optional) Time to live - if omitted or set to 0 the cookie will expire when the browser closes
	 * @param   array                $options  (optional) Cookie options
	 * @return  \mako\http\Response
	 */

	public function cookie($name, $value, $ttl = 0, array $options = [])
	{
		return $this->unsignedCookie($name, MAC::sign($value), $ttl, $options);
	}

	/**
	 * Deletes a cookie.
	 *
	 * @access  public
	 * @param   string               $name     Cookie name
	 * @param   array                $options  (optional) Cookie options
	 * @return  \mako\http\Response
	 */

	public function deleteCookie($name, array $options = [])
	{
		return $this->unsignedCookie($name, '', time() - 3600, $options);
	}

	/**
	 * Returns the response cookies.
	 * 
	 * @access  public
	 * @return array
	 */

	public function getCookies()
	{
		return $this->cookies;
	}

	/**
	 * Clear cookies.
	 * 
	 * @access  public
	 * @return  \mako\http\Response
	 */

	public function clearCookies()
	{
		$this->cookies = [];

		return $this;
	}

	/**
	 * Sends response headers.
	 * 
	 * @access  protected
	 */

	public function sendHeaders()
	{
		// Send status header

		if($this->request->server('FCGI_SERVER_VERSION', false) !== false)
		{
			$protocol = 'Status:';
		}
		else
		{
			$protocol = $this->request->server('SERVER_PROTOCOL', 'HTTP/1.1');
		}

		header($protocol . ' ' . $this->statusCode . ' ' . $this->statusCodes[$this->statusCode]);

		// Send content type header

		$contentType = $this->contentType;

		if(stripos($contentType, 'text/') === 0 || in_array($contentType, ['application/json', 'application/xml']))
		{
			$contentType .= '; charset=' . $this->charset;
		}

		header('Content-Type: ' . $contentType);

		// Send other headers

		foreach($this->headers as $name => $value)
		{
			header($name . ': ' . $value);
		}

		// Send cookie headers

		foreach($this->cookies as $cookie)
		{
			setcookie($cookie['name'], $cookie['value'], $cookie['ttl'], $cookie['path'], $cookie['domain'], $cookie['secure'], $cookie['httponly']);
		}
	}

	/**
	 * Enables ETag response cache.
	 *
	 * @access  public
	 * @return  \mako\http\Response
	 */

	public function cache()
	{
		$this->responseCache = true;

		return $this;
	}

	/**
	 * Disables ETag response cache.
	 *
	 * @access  public
	 * @return  \mako\http\Response
	 */

	public function disableCaching()
	{
		$this->responseCache = false;

		return $this;
	}

	/**
	 * Enables output compression.
	 * 
	 * @access  public
	 * @return  \mako\http\Response
	 */

	public function compress()
	{
		$this->outputCompression = true;

		return $this;
	}

	/**
	 * Disables output compression.
	 * 
	 * @access  public
	 * @return  \mako\http\Response
	 */

	public function disableCompression()
	{
		$this->outputCompression = false;

		return $this;
	}

	/**
	 * Returns a file container.
	 * 
	 * @access  public
	 * @param   string                    $file     File path
	 * @param   array                     $options  Options
	 * @return  \mako\http\response\File
	 */

	public function file($file, array $options = [])
	{
		return new File($file, $options);
	}

	/**
	 * Returns a stream container.
	 * 
	 * @access  public
	 * @param   \Closure                    $stream  Stream
	 * @return  \mako\http\response\Stream
	 */

	public function stream(Closure $stream)
	{
		return new Stream($stream);
	}

	/**
	 * Redirects to another location.
	 *
	 * @access  public
	 * @param   string                        $location     Location
	 * @param   array                         $routeParams  (optional) Route parameters
	 * @param   array                         $queryParams  (optional) Query parameters
	 * @return  \mako\http\response\Redirect
	 */
	
	public function redirect($location, array $routeParams = [], array $queryParams = [])
	{
		return new Redirect($location, $routeParams, $queryParams);
	}

	/**
	 * Redirects the user back to the previous page.
	 * 
	 * @access  public
	 * @param   int     $statusCode  (optional) HTTP status code
	 */

	public function back($statusCode = 302)
	{
		return $this->redirect($this->request->referer())->status($statusCode);
	}
	
	/**
	 * Send output to browser.
	 *
	 * @access  public
	 */
	
	public function send()
	{
		if($this->body instanceof ResponseContainerInterface)
		{
			// This is a response container so we'll just pass it the 
			// request and response instances and let it handle the rest itself

			$this->body->send($this->request, $this);
		}
		else
		{
			$sendBody = true;

			// Make sure that output buffering is enabled

			if(ob_get_level() === 0)
			{
				ob_start();
			}

			// Cast to body to string so that everything is rendered 
			// before running through response filters

			$this->body = (string) $this->body;

			// Run body through the response filters

			foreach($this->outputFilters as $outputFilter)
			{
				$this->body = $outputFilter($this->body);
			}

			// Check ETag if response cache is enabled

			if($this->responseCache === true)
			{
				$hash = '"' . sha1($this->body) . '"';

				$this->header('ETag', $hash);

				if($this->request->header('if-none-match') === $hash)
				{
					$this->status(304);

					$sendBody = false;
				}
			}

			if($sendBody && !in_array($this->statusCode, [100, 101, 102, 204, 304]))
			{
				// Start compressed output buffering if output compression is enabled

				if($this->outputCompression)
				{
					ob_start('ob_gzhandler');
				}

				echo $this->body;

				// If output compression is enabled then we'll have to flush the compressed buffer
				// so that we can get the compressed content length when setting the content-length header

				if($this->outputCompression)
				{
					ob_end_flush();
				}

				// Add the content-length header

				if(!array_key_exists('transfer-encoding', $this->headers))
				{
					$this->header('content-length', ob_get_length());
				}
			}

			// Send the headers and flush the output buffer

			$this->sendHeaders();

			ob_end_flush();
		}
	}
}

/** -------------------- End of file -------------------- **/