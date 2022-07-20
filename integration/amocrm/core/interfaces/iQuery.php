<?php
/**
 * @author Samigullin Kamil <feedback@kamilsk.com>
 * @link http://www.kamilsk.com/
 */
namespace OctoLab\amoCRM\core\interfaces;
/**
 * @package OctoLab\amoCRM\core\interfaces
 */
interface iQuery
{
	/**
	 * @param string $endpoint
	 * @param array $params
	 *
	 * @return iResponse
	 */
	public function get($endpoint, array $params = array());

	/**
	 * @param string $endpoint
	 * @param array $params
	 *
	 * @return iResponse
	 */
	public function post($endpoint, array $params = array());

	/**
	 * @return bool
	 */
	public function authenticate();
}