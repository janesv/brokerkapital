<?php
/**
 * @author Samigullin Kamil <feedback@kamilsk.com>
 * @link http://www.kamilsk.com/
 */
namespace OctoLab\amoCRM\core\cli;

use OctoLab\amoCRM\core\interfaces\iResponse;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Demo command for CLI.
 *
 * @package OctoLab\amoCRM\core\cli
 */
class ExampleCommand extends Command
{
	/**
	 * Command configuration.
	 */
	protected function configure()
	{
		$this
			->setName('example')
			->setDescription('Example of usage library on CLI.')
			->addArgument(
				'method',
				InputArgument::REQUIRED,
				'API method like this account::current (DAO::method). Current support only for Account::current().'
			)
			->addOption(
				'show_code',
				null,
				InputOption::VALUE_OPTIONAL,
				'Show the code of the response.',
				true
			)
			->addOption(
				'show_header',
				null,
				InputOption::VALUE_NONE,
				'If set, then will show the header of the response.'
			)
			->addOption(
				'show_body',
				null,
				InputOption::VALUE_NONE,
				'If set, then will show the body of the response.'
			);
	}

	/**
	 * Command execution.
	 *
	 * @param InputInterface $input
	 * @param OutputInterface $output
	 *
	 * @return void
	 */
	protected function execute(InputInterface $input, OutputInterface $output)
	{
		$supported = array(
			'account' => 'OctoLab\amoCRM\dao\Account',
		);
		list($class, $method) = array_map(function ($value) {
				return strtolower($value);
			}, explode('::', $input->getArgument('method')));
		if (isset($supported[$class]) && class_exists($class = $supported[$class]) && method_exists($class, $method)) {
			$output->writeln(strtr('Result of {class}::{method}() calling.', array(
						'{class}' => $class,
						'{method}' => $method,
					)));
			/** @var iResponse $response */
			$response = call_user_func(array('OctoLab\amoCRM\dao\Account', 'current'));
			$show_code = $input->getOption('show_code');
			if ( ! empty($show_code) && $show_code !== 'false') {
				var_dump($response->getCode());
			}
			if ($input->getOption('show_header')) {
				var_dump($response->getHeader());
			}
			if ($input->getOption('show_body')) {
				var_dump($response->getBody());
			}
			exit;
		}
		$output->writeln(strtr('Something wrong! Please learn the method {method}() in the {file}.', array(
					'{method}' => __METHOD__,
					'{file}' => __FILE__,
				)));
	}
}