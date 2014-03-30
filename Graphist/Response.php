<?php namespace Graphist;

use Graphist\View as View;

class Response {

	private static $_root = DOCUMENT_ROOT;

	public $statusTexts = array(
        100 => 'Continue',
        101 => 'Switching Protocols',
        200 => 'OK',
        201 => 'Created',
        202 => 'Accepted',
        203 => 'Non-Authoritative Information',
        204 => 'No Content',
        205 => 'Reset Content',
        206 => 'Partial Content',
        300 => 'Multiple Choices',
        301 => 'Moved Permanently',
        302 => 'Found',
        303 => 'See Other',
        304 => 'Not Modified',
        305 => 'Use Proxy',
        307 => 'Temporary Redirect',
        400 => 'Bad Request',
        401 => 'Not Authorized',
        402 => 'Payment Required',
        403 => 'Forbidden',
        404 => 'Not Found',
        405 => 'Method Not Allowed',
        406 => 'Not Acceptable',
        407 => 'Proxy Authentication Required',
        408 => 'Request Timeout',
        409 => 'Conflict',
        410 => 'Gone',
        411 => 'Length Required',
        412 => 'Precondition Failed',
        413 => 'Request Entity Too Large',
        414 => 'Request-URI Too Long',
        415 => 'Unsupported Media Type',
        416 => 'Requested Range Not Satisfiable',
        417 => 'Expectation Failed',
        418 => 'I\'m a teapot',
        500 => 'Internal Server Error',
        501 => 'Not Implemented',
        502 => 'Bad Gateway',
        503 => 'Service Unavailable',
        504 => 'Gateway Timeout',
        505 => 'HTTP Version Not Supported',
    );

	private $data;

	public function __construct($data)
	{
		$this->data = $data;
	}

	public static function create($data = array())
	{
		return new Response($data);
	}

	public function addHeader($key, $value)
	{
		header($key, $value);
		return $this;
	}

	public function addHeaders(array $headers)
	{
		foreach ($headers as $key => $value) {
			$this->addHeader($key, $value);
		}

		return $this;
	}

	public function addParameter($key, $value)
	{
		$this->data[$key] = $value;
		return $this;
	}

	public function setStatusCode($statusCode)
	{
		header(sprintf("HTTP/1.1 %s %s", $statusCode, $this->statusTexts[$statusCode]));
		return $this;
	}

	public function setError($error, $message, $code = 200)
	{
		if (!array_key_exists("errors", $this->data)) {
			$this->data["errors"] = array();
		}

		$this->data["errors"][] = array("error" => $error, "message" => $message, "code" => $code);
		$this->setStatusCode($code);

		return $this;
	}

	public function toJSON()
	{
		$this->addHeader("Content-Type", "application/json");
		$this->data = json_encode($this->data);

		return $this;
	}

	public function send()
	{
		print $this->data;
	}

	public static function json($data)
	{
		header("Content-Type", "application/json");
		print json_encode($data);
	}

	public static function error($code) 
	{
		View::render("error.404");
	}

	public static function to_route($uri) 
	{
		header("Location: {$uri}");
	}
}