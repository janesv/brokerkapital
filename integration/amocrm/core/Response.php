<?php
/**
 * @author Samigullin Kamil <feedback@kamilsk.com>
 * @link http://www.kamilsk.com/
 */
namespace OctoLab\amoCRM\core;

use OctoLab\amoCRM\core\interfaces\iResponse;

/**
 * @package OctoLab\amoCRM\core
 */
class Response implements iResponse
{
	const SUCCESS_200 = 200;
	const SUCCESS_204 = 204;

	const ERROR_301 = 301;
	const ERROR_400 = 400;
	const ERROR_401 = 401;
	const ERROR_403 = 403;
	const ERROR_404 = 404;
	const ERROR_500 = 500;
	const ERROR_502 = 502;
	const ERROR_503 = 503;

	/**
	 * @return array
	 */
	static public function getErrorMessages()
	{
		return array(
			self::ERROR_301 => 'HTTP/1.0 301 Moved Permanently',
			self::ERROR_400 => 'HTTP/1.0 400 Bad Request',
			self::ERROR_401 => 'HTTP/1.0 401 Unauthorized',
			self::ERROR_403 => 'HTTP/1.0 403 Forbidden',
			self::ERROR_404 => 'HTTP/1.0 404 Not Found',
			self::ERROR_500 => 'HTTP/1.0 500 Internal Server Error',
			self::ERROR_502 => 'HTTP/1.0 502 Bad Gateway',
			self::ERROR_503 => 'HTTP/1.0 503 Service Unavailable',
		);
	}

	/**
	 * @return array
	 */
	static public function getSuccessMessages()
	{
		return array(
			self::SUCCESS_200 => 'HTTP/1.0 200 OK',
			self::SUCCESS_204 => 'HTTP/1.0 204 No Content',
		);
	}

	/**
	 * @param int $code
	 *
	 * @return bool
	 */
	static public function repeat($code)
	{
		return in_array($code, array(
				self::ERROR_500,
				self::ERROR_502,
				self::ERROR_503,
			));
	}

	/** @var mixed */
	private $_code;
	/** @var mixed */
	private $_header;
	/** @var mixed */
	private $_body;

	/**
	 * @return mixed
	 */
	public function getCode()
	{
		return $this->_code;
	}

	/**
	 * @param mixed $code
	 *
	 * @return self
	 */
	public function setCode($code)
	{
		$this->_code = $code;
		return $this;
	}

	/**
	 * @return mixed
	 */
	public function getHeader()
	{
		return $this->_header;
	}

	/**
	 * @param mixed $header
	 *
	 * @return self
	 */
	public function setHeader($header)
	{
		$this->_header = $header;
		return $this;
	}

	/**
	 * @return mixed
	 */
	public function getBody()
	{
		return $this->_body;
	}

	/**
	 * @param mixed $body
	 *
	 * @return self
	 */
	public function setBody($body)
	{
		$this->_body = $body;
		return $this;
	}
}