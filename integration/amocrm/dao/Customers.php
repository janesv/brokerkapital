<?php

namespace OctoLab\amoCRM\dao;

use OctoLab\amoCRM\API;
use OctoLab\amoCRM\core\interfaces\iResponse;


class Customers
{
	/**
	 * Метод для получения списка покумателей с возможностью фильтрации и постраничной выборки.
	 *
	 * @param array $params
	 * <code></code>
	 *
	 * @return iResponse
	 */
	static public function get(array $params = array())
	{
		return API::app()->getQuery()->get('/api/v2/customers', $params);
	}

	/**
	 * Метод позволяет добавлять покупателя по одному или пакетно, а также обновлять данные по уже существующим покупателям.
	 *
	 * @param array $params
	 * <code></code>
	 *
	 * @return iResponse
	 */
	static public function set(array $params = array())
	{
		return API::app()->getQuery()->post('/api/v2/customers', $params);
	}
}