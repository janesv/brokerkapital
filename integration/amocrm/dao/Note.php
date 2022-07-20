<?php
/**
 * @author Samigullin Kamil <feedback@kamilsk.com>
 * @link http://www.kamilsk.com/
 */
namespace OctoLab\amoCRM\dao;

use OctoLab\amoCRM\API;
use OctoLab\amoCRM\core\interfaces\iResponse;

/**
 * Задача.
 *
 * Задача должна обязательно иметь ответственного и дату (число и время). Также задача может быть связана со сделкой или
 * контактом, но не обязательно, она может быть не связана ни с каким объектом. Самая основная сущность системы считается
 * задачи.
 *
 * @package OctoLab\amoCRM\dao
 */
class Note
{
	/**
	 * Метод для получения списка уже созданных задач, с возможностью фильтрации данных и постраничной выборки.
	 *
	 * @param array $params
	 * <code></code>
	 *
	 * @return iResponse
	 * @see https://developers.amocrm.ru/rest_api/notes_list.php
	 */
	static public function get(array $params = array())
	{
		return API::app()->getQuery()->get('/api/v2/notes', $params);
	}

	/**
	 * Метод позволяет создавать новые задачи, а также обновлять информацию по уже существующим задачам.
	 *
	 * @param array $params
	 * <code></code>
	 *
	 * @return iResponse
	 * @see https://developers.amocrm.ru/rest_api/notes_set.php
	 */
	static public function set(array $params = array())
	{
		return API::app()->getQuery()->post('/api/v2/notes', $params);
	}
}