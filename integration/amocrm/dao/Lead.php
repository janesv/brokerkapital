<?php
/**
 * @author Samigullin Kamil <feedback@kamilsk.com>
 * @link http://www.kamilsk.com/
 */
namespace OctoLab\amoCRM\dao;

use OctoLab\amoCRM\API;
use OctoLab\amoCRM\core\interfaces\iResponse;

/**
 * Сделка.
 *
 * Одна из основных сущностей системы. Состоит из предустановленного набора полей и дополнительных, создаваемых
 * администратором аккаунта. Каждая сделка может быть прикреплена к одному и более контакту или не прикреплена ни к одному.
 *
 * Каждой сделке может быть задан ответственный для разграничения прав доступа между сотрудниками аккаунта.
 *
 * Сделка обладает статусом, который обозначает положение сделки в жизненном цикле (бизнес-процесс). Он должен быть
 * обязательно присвоен сделке. Список статусов может быть изменен в рамках аккаунта, кроме двух системных конечных
 * статусов.
 *
 * @package OctoLab\amoCRM\dao
 */
class Lead
{
	/**
	 * Метод который позволяет получить подробную информацию о уже созданных сделках, имеет возможность фильтрации данных
	 * и постраничной выборки.
	 *
	 * @param array $params
	 * <code></code>
	 *
	 * @return iResponse
	 * @see https://developers.amocrm.ru/rest_api/leads_list.php
	 */
	static public function get(array $params = array())
	{
		return API::app()->getQuery()->get('/api/v2/leads', $params);
	}

	/**
	 * Метод позволяет создавать новые сделки, а также обновлять информацию по уже существующим.
	 *
	 * @param array $params
	 * <code></code>
	 *
	 * @return iResponse
	 * @see https://developers.amocrm.ru/rest_api/leads_set.php
	 */
	static public function set(array $params = array())
	{
		return API::app()->getQuery()->post('/api/v2/leads', $params);
	}
}