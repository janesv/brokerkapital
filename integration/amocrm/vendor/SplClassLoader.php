<?php
/**
 * SplClassLoader implementation that implements the technical interoperability
 * standards for PHP 5.3 namespaces and class names.
 * http://groups.google.com/group/php-standards/web/final-proposal
 *     // Example which loads classes for the Doctrine Common package in the
 *     // Doctrine\Common namespace.
 *     $classLoader = new SplClassLoader('Doctrine\Common', '/path/to/doctrine');
 *     $classLoader->register();
 *
 * @author Jonathan H. Wage <jonwage@gmail.com>
 * @author Roman S. Borschel <roman@code-factory.org>
 * @author Matthew Weier O'Phinney <matthew@zend.com>
 * @author Kris Wallsmith <kris.wallsmith@gmail.com>
 * @author Fabien Potencier <fabien.potencier@symfony-project.org>
 *
 * @update Samigullin Kamil <feedback@kamilsk.com>
 */
class SplClassLoader
{
	private $_namespace;
	private $_namespaceSeparator = '\\';
	private $_includePath;
	private $_fileExtension = '.php';

	/**
	 * Creates a new <tt>SplClassLoader</tt> that loads classes of the specified namespace.
	 *
	 * @param string $ns The namespace to use.
	 * @param string $includePath
	 */
	public function __construct($ns = null, $includePath = null)
	{
		$this->_namespace = $ns;
		$this->_includePath = $includePath;
	}

	/**
	 * Gets the namespace separator used by classes in the namespace of this class loader.
	 *
	 * @return string
	 */
	public function getNamespaceSeparator()
	{
		return $this->_namespaceSeparator;
	}

	/**
	 * Sets the namespace separator used by classes in the namespace of this class loader.
	 *
	 * @param string $sep The separator to use.
	 */
	public function setNamespaceSeparator($sep)
	{
		$this->_namespaceSeparator = $sep;
	}

	/**
	 * Gets the base include path for all class files in the namespace of this class loader.
	 *
	 * @return null|string $includePath
	 */
	public function getIncludePath()
	{
		return $this->_includePath;
	}

	/**
	 * Sets the base include path for all class files in the namespace of this class loader.
	 *
	 * @param string $includePath
	 */
	public function setIncludePath($includePath)
	{
		$this->_includePath = $includePath;
	}

	/**
	 * Gets the file extension of class files in the namespace of this class loader.
	 *
	 * @return string $fileExtension
	 */
	public function getFileExtension()
	{
		return $this->_fileExtension;
	}

	/**
	 * Sets the file extension of class files in the namespace of this class loader.
	 *
	 * @param string $fileExtension
	 */
	public function setFileExtension($fileExtension)
	{
		$this->_fileExtension = $fileExtension;
	}

	/**
	 * Register a function with the spl provided __autoload stack.
	 *
	 * @see http://www.php.net/manual/en/function.spl-autoload-register.php
	 */
	public function register()
	{
		spl_autoload_register(array($this, 'loadClass'));
	}

	/**
	 * Unregister a function from the spl provided __autoload stack.
	 *
	 * @see http://www.php.net/manual/en/function.spl-autoload-unregister.php
	 */
	public function unregister()
	{
		spl_autoload_unregister(array($this, 'loadClass'));
	}

	/**
	 * Loads the given class or interface.
	 *
	 * @param string $className The name of the class to load.
	 *
	 * @return void
	 */
	public function loadClass($className)
	{
		// remove global namespace
		$className = ltrim($className, '\\');
		// current namespace
		$baseNs = $this->_namespace . $this->_namespaceSeparator;
		$baseNsLen = strlen($baseNs);
		if (null === $this->_namespace or strpos($className, $baseNs) === 0) {
			$fileName = '';
			if (false !== ($lastNsPos = strripos($className, $this->_namespaceSeparator) + 1)) {
				// relative namespace
				$innerNs = substr($className, $baseNsLen, $lastNsPos - $baseNsLen);
				$className = substr($className, $lastNsPos);
				$fileName = str_replace($this->_namespaceSeparator, DIRECTORY_SEPARATOR, $innerNs) . DIRECTORY_SEPARATOR;
			}
			$fileName .= str_replace('_', DIRECTORY_SEPARATOR, $className) . $this->_fileExtension;
			require ($this->_includePath !== null ? $this->_includePath . DIRECTORY_SEPARATOR : '') . $fileName;
		}
	}
}