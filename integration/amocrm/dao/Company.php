<?php
/**
 * @author Samigullin Kamil <feedback@kamilsk.com>
 * @link http://www.kamilsk.com/
 */
namespace OctoLab\amoCRM\dao;

use OctoLab\amoCRM\API;
use OctoLab\amoCRM\core\interfaces\iResponse;

/**
 * Контакт.
 *
 * Одна из основных сущностей системы. Состоит из предустановленного набора полей и дополнительных, создаваемых
 * администратором аккаунта. Каждый контакт может участвовать в одной и более сделке или может быть вообще не связан ни
 * с одной. Каждый контакт может быть прикреплен к одной компании.
 *
 * E-mail контакта и телефон используются как уникальные идентификаторы в связке с другими системами. К примеру, именно
 * в события контакта попадает информация о совершенных звонках, о e-mail-переписке.
 *
 * Каждому контакту может быть задан ответственный для разграничения прав доступа между сотрудниками аккаунта.
 *
 * @package OctoLab\amoCRM\dao
 */
class Company
{

	static public function get(array $params = array())
	{
		return API::app()->getQuery()->get('/api/v2/companies', $params);
	}


	static public function set(array $params = array())
	{
		return API::app()->getQuery()->post('/api/v2/companies', $params);
	}

}