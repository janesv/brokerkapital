<?php
/**
 * @author Samigullin Kamil <feedback@kamilsk.com>
 * @link http://www.kamilsk.com/
 */
namespace OctoLab\amoCRM\core\interfaces;
/**
 * @package OctoLab\amoCRM\core\interfaces
 */
interface iResponse
{
	/**
	 * @return mixed
	 */
	public function getCode();

	/**
	 * @param mixed $code
	 *
	 * @return self
	 */
	public function setCode($code);

	/**
	 * @return mixed
	 */
	public function getHeader();

	/**
	 * @param mixed $header
	 *
	 * @return self
	 */
	public function setHeader($header);

	/**
	 * @return mixed
	 */
	public function getBody();

	/**
	 * @param mixed $body
	 *
	 * @return self
	 */
	public function setBody($body);
}