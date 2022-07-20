<?php
/**
 * @author Samigullin Kamil <feedback@kamilsk.com>
 * @link http://www.kamilsk.com/
 */
namespace OctoLab\amoCRM\core;

use Exception;
use OctoLab\amoCRM\API;
use OctoLab\amoCRM\core\interfaces\iComponent;
use OctoLab\amoCRM\core\interfaces\iQuery;
use OctoLab\amoCRM\core\interfaces\iResponse;

/**
 * @package OctoLab\amoCRM\core
 */
class Query implements iComponent, iQuery
{
	/** @var string */
	public $url;
	/** @var string */
	public $user;
	/** @var string */
	public $token;
	/** @var string */
	public $cookie;
	/** @var int */
	public $attempts;
	/** @var int */
	public $pause;

	/** @var resource */
	protected $ch;
	/** @var array */
	protected $headers;

	/**
	 * @throws Exception
	 */
	public function init()
	{
		if (empty($this->url)) {
			throw new Exception();
		}
		if (empty($this->user)) {
			throw new Exception();
		}
		if (empty($this->token)) {
			throw new Exception();
		}
		if (empty($this->cookie)) {
			$this->cookie = implode('/', array(
					API::app()->getRuntimePath(),
					md5($this->user . $this->token),
				));
		}
		if ( ! is_file($this->cookie)) {
			if (false === ($fh = @fopen($this->cookie, 'w'))) {
				throw new Exception();
			}
			fwrite($fh, '');
			fclose($fh);
		}
		if ($this->attempts < 0) {
			throw new Exception();
		}
		if ($this->pause < 0) {
			throw new Exception();
		}
		$options = array(
			CURLOPT_RETURNTRANSFER => true,
			CURLOPT_USERAGENT => 'amoCRM-API-client/1.0',
			CURLOPT_SSL_VERIFYPEER => 0,
			CURLOPT_SSL_VERIFYHOST => 0,
			CURLOPT_HEADERFUNCTION => array($this, 'header'),
		);
		if (false === ($this->ch = curl_init()) or false === curl_setopt_array($this->ch, $options)) {
			throw new Exception();
		}
	}

	/**
	 * @param string $endpoint
	 * @param array $params
	 *
	 * @return iResponse
	 * @throws Exception
	 */
	public function get($endpoint, array $params = array())
	{
		$ch = curl_copy_handle($this->ch);
		if (isset($params['if-modified-since'])) {
			$validate = date_parse_from_format('D, d M Y H:i:s', $params['if-modified-since']);
			if ($validate['warning_count'] === 0 && $validate['error_count'] === 0) {
				$modified = $params['if-modified-since'];
			} elseif (false === ($modified = date_create($params['if-modified-since']))) {
				throw new Exception();
			}
			$options = array(
				CURLOPT_HTTPHEADER => array(strtr('IF-MODIFIED-SINCE: {date}', array(
						'{date}' => $modified->format('D, d M Y H:i:s'),
					)),
				));
			if (false === curl_setopt_array($ch, $options)) {
				throw new Exception();
			}
			unset($params['if-modified-since']);
		}
		$params = http_build_query($params);
		if ($params !== '') {
			$endpoint .= strrpos($endpoint, '?') !== false ? $params : "?{$params}";
		}
		return $this->exec($ch, $endpoint);
	}

	/**
	 * @param string $endpoint
	 * @param array $params
	 *
	 * @return iResponse
	 * @throws Exception
	 */
	public function post($endpoint, array $params = array())
	{
		$ch = curl_copy_handle($this->ch);
		$options = array(
			CURLOPT_POST => true,
			CURLOPT_POSTFIELDS => json_encode($params),
			CURLOPT_HTTPHEADER => array('Content-Type: application/json'),
		);
		if (false === curl_setopt_array($ch, $options)) {
			throw new Exception();
		}
		return $this->exec($ch, $endpoint);
	}

	/**
	 * В случае успешной авторизации возвращает идентификатор сессии пользователя, который необходимо использовать при
	 * обращении к остальным методам API.
	 *
	 * @return bool
	 * @throws Exception
	 * @see https://developers.amocrm.ru/rest_api/auth.php
	 */
	public function authenticate()
	{
		$ch = curl_copy_handle($this->ch);
		$options = array(
			CURLOPT_URL => $this->link('/private/api/auth.php?type=json'),
			CURLOPT_POST => true,
			CURLOPT_POSTFIELDS => array(
				'USER_LOGIN' => $this->user,
				'USER_HASH' => $this->token,
			),
			CURLOPT_COOKIEJAR => $this->cookie,
		);
		if (false === curl_setopt_array($ch, $options)) {
			throw new Exception();
		}
		$i = 0;
		do {
			curl_exec($ch);
			$code = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);
			$i++;
		} while ($i < $this->attempts && Response::repeat($code) && sleep($this->pause) !== false);
		return $code === Response::SUCCESS_200;
	}

	/**
	 * @param resource $ch
	 * @param string $endpoint
	 *
	 * @return iResponse
	 * @throws Exception
	 */
	protected function exec($ch, $endpoint)
	{
		$options = array(
			CURLOPT_URL => $this->link($endpoint),
			CURLOPT_COOKIEFILE => $this->cookie,
		);
		if (false === curl_setopt_array($ch, $options)) {
			throw new Exception();
		}
		$i = 0;
		do {
			$body = curl_exec($ch);
			$code = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);
			if ($code === Response::ERROR_401 && $this->authenticate()) {
				$copy = curl_copy_handle($ch);
				curl_close($ch);
				$body = curl_exec($copy);
				$code = (int) curl_getinfo($copy, CURLINFO_HTTP_CODE);
				$ch = $copy;
			}
			$hash = $this->hash($ch);
			$i++;
		} while ($i < $this->attempts && Response::repeat($code) && sleep($this->pause) !== false);
		curl_close($ch);
		return $this->response()->setCode($code)->setHeader($this->headers[$hash])->setBody($body);
	}

	/**
	 * @param string $endpoint
	 *
	 * @return string
	 */
	protected function link($endpoint)
	{
		return $this->url . $endpoint;
	}

	/**
	 * @param resource $ch
	 *
	 * @return string
	 */
	protected function hash($ch)
	{
		return md5(curl_getinfo($ch, CURLINFO_EFFECTIVE_URL));
	}

	/**
	 * @param resource $ch
	 * @param string $header_piece
	 *
	 * @return int
	 */
	protected function header($ch, $header_piece)
	{
		$hash = $this->hash($ch);
		if (isset($this->headers[$hash])) {
			$this->headers[$hash] .= $header_piece;
		} else {
			$this->headers[$hash] = $header_piece;
		}
		return strlen($header_piece);
	}

	/**
	 * @return Response
	 */
	protected function response()
	{
		return new Response();
	}
}