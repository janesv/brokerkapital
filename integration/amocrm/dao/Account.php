<?php
/**
 * @author Samigullin Kamil <feedback@kamilsk.com>
 * @link http://www.kamilsk.com/
 */
namespace OctoLab\amoCRM\dao;

use OctoLab\amoCRM\API;
use OctoLab\amoCRM\core\interfaces\iResponse;

/**
 * Аккаунт.
 *
 * Через API вы можете получить необходимую информацию по аккаунту: название, оплаченный период, пользователи аккаунта и
 * их права, справочники дополнительных полей контактов и сделок, справочник статусов сделок, справочник типов событий,
 * справочник типов задач и другие параметры аккаунта.
 *
 * @package OctoLab\amoCRM\dao
 */
class Account
{
	/**
	 * Получение информации по аккаунту в котором произведена авторизация.
	 *
	 * @return iResponse
	 * @see https://developers.amocrm.ru/rest_api/accounts_current.php
	 */
	static public function current(array $params = array())
	{
		return API::app()->getQuery()->get('/api/v2/account', $params);
	}
}