<?php
/**
 * @author Samigullin Kamil <feedback@kamilsk.com>
 * @link http://www.kamilsk.com/
 */
namespace OctoLab\amoCRM;

use Exception;
use OctoLab\amoCRM\core\interfaces\iComponent;
use OctoLab\amoCRM\core\interfaces\iQuery;
use SplClassLoader;

require __DIR__ . '/vendor/SplClassLoader.php';
/**
 * @package OctoLab\amoCRM
 */
class API
{
	/**
	 * Singleton.
	 *
	 * @var self
	 */
	static private $_instance;

	/**
	 * Access point to Singleton.
	 *
	 * @return self
	 */
	static public function app()
	{
		if (self::$_instance === null) {
			self::$_instance = new self();
		}
		return self::$_instance;
	}

	/**
	 * Default components configurations.
	 *
	 * @var array
	 */
	protected $default = array(
		'query' => array(
			'class' => 'OctoLab\amoCRM\core\Query',
			'url' => null,
			'user' => null,
			'token' => null,
			'cookie' => null,
			'attempts' => 2,
			'pause' => 1,
		),
	);
	/**
	 * Autoloaders.
	 *
	 * @var array
	 */
	private $_loaders;
	/**
	 * Path to runtime directory.
	 *
	 * @var string
	 */
	private $_runtimePath;
	/**
	 * Components container or their settings.
	 *
	 * @var array
	 */
	private $_dc;

	/**
	 * Add autoloader.
	 *
	 * @param string $ns
	 * @param string $includePath
	 *
	 * @return self
	 */
	public function addLoader($ns = null, $includePath = null)
	{
		if ( ! isset($this->_loaders[$ns])) {
			$this->_loaders[$ns] = $loader = new SplClassLoader($ns, $includePath);
			$loader->register();
		}
		return $this;
	}

	/**
	 * Remove autoloader.
	 *
	 * @param string $ns
	 *
	 * @return self
	 */
	public function removeLoader($ns = null)
	{
		if (isset($this->_loaders[$ns])) {
			/** @var $loader SplClassLoader */
			$loader = $this->_loaders[$ns];
			$loader->unregister();
			unset($this->_loaders[$ns]);
		}
		return $this;
	}

	/**
	 * Get path to runtime directory.
	 *
	 * @return string
	 */
	public function getRuntimePath()
	{
		if ($this->_runtimePath === null) {
			$this->setRuntimePath(__DIR__ . '/runtime');
		}
		return $this->_runtimePath;
	}

	/**
	 * Set path to runtime directory.
	 *
	 * @param string $path
	 *
	 * @return string
	 * @throws Exception
	 */
	public function setRuntimePath($path)
	{
		if (is_dir($path) && is_writable($path)) {
			$this->_runtimePath = $path;
			return $path;
		}
		throw new Exception();
	}

	/**
	 * Registers the component settings in the container.
	 *
	 * @param string $key
	 * @param array $component
	 *
	 * @return self
	 */
	public function register($key, array $component)
	{
		if (isset($this->default[$key])) {
			$component = array_merge($this->default[$key], $component);
		}
		$this->_dc[$key] = $component;
		return $this;
	}

	/**
	 * Get the component for interaction with API.
	 *
	 * @return iQuery
	 */
	public function getQuery()
	{
		if (isset($this->_dc['query'])) {
			if (is_array($this->_dc['query'])) {
				$this->_dc['query'] = $this->buildComponent($this->_dc['query']);
			}
		} else {
			$this->_dc['query'] = $this->buildComponent($this->default['query']);
		}
		return $this->_dc['query'];
	}

	/**
	 * Initializes the component.
	 *
	 * @param array $config
	 *
	 * @return iComponent
	 * @throws Exception
	 */
	protected function buildComponent(array $config)
	{
		$class = $config['class'];
		unset($config['class']);
		$component = new $class();
		if ($component instanceof iComponent) {
			foreach ($config as $property => $value) {
				$component->$property = $value;
			}
			$component->init();
			return $component;
		}
		throw new Exception();
	}

	/**
	 * Singleton.
	 */
	private function __construct() {}
}
// глобальный доступ к приложению
$app = API::app();
$app->addLoader(__NAMESPACE__, __DIR__);