<?php
/**
 * @author Samigullin Kamil <feedback@kamilsk.com>
 * @link http://www.kamilsk.com/
 */
namespace OctoLab\amoCRM\dao;

use OctoLab\amoCRM\API;
use OctoLab\amoCRM\core\interfaces\iResponse;

/**
 * Событие (примечание).
 *
 * События представляют возможность добавлять дополнительную структурированную или не структурированную информацию к
 * контакту или сделке. События бывают системные (звонки, СМС-сообщения и т.д.), созданные пользоваталем (примечания,
 * файлы). События в карточках отображаются на ряду с задачами, т.к. не имеют ответственного и не прикреплены к дате.
 *
 * Зачастую события используются виджетами для добавления дополнительной информации к сделке или контакту, когда не очень
 * удобно использовать кастомные поля. События очень удобно использовать как лог, т.к. они всегда отображаются в
 * хронологическом порядке в ленте и, если ваша информация привязана к дате (хронологии), то желательно использовать
 * именно события.
 *
 * @package OctoLab\amoCRM\dao
 */
class Task
{
	/**
	 * Метод для получения списка событий с возможностью фильтрации и постраничной выборки.
	 *
	 * @param array $params
	 * <code></code>
	 *
	 * @return iResponse
	 * @see https://developers.amocrm.ru/rest_api/tasks_list.php
	 */
	static public function get(array $params = array())
	{
		return API::app()->getQuery()->get('/api/v2/tasks', $params);
	}

	/**
	 * Метод позволяет добавлять события по одному или пакетно, а также обновлять данные по уже существующим событиям.
	 *
	 * @param array $params
	 * <code></code>
	 *
	 * @return iResponse
	 * @see https://developers.amocrm.ru/rest_api/tasks_set.php
	 */
	static public function set(array $params = array())
	{
		return API::app()->getQuery()->post('/api/v2/tasks', $params);
	}
}