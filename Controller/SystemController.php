<?php

namespace Novuscom\CMFBundle\Controller;

use Symfony\Component\Console\Output\ConsoleOutput;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use \Doctrine\Common\Collections\ArrayCollection;
use APY\BreadcrumbTrailBundle\Annotation\Breadcrumb;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\Response;
use Composer\Console\Application;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\NullOutput;
use Symfony\Component\Process\Process;
use Ifsnop\Mysqldump as IMysqldump;

class SystemController extends Controller
{
	public function updateAction()
	{

		return $this->render('NovuscomCMFBundle:System:index.html.twig', array());
	}

	public function upgradeAction()
	{

		$dump = new IMysqldump\Mysqldump(
			'mysql:host=localhost;dbname='.$this->container->getParameter('database_name'),
			$this->getParameter('database_user'),
			$this->getParameter('database_password')
		);
		$dump->start($this->get('kernel')->getRootDir().'/dump-'.date('d-m-Y_h-i-s').'.sql');
		$path = realpath($this->get('kernel')->getRootDir() . '/../');
		$result = array(
			$this->runCommand($path, 'curl -sS https://getcomposer.org/installer | php'),
			$this->runCommand($path, 'php app/console doctrine:schema:update --dump-sql'),
			$this->runCommand($path, 'php app/console doctrine:schema:update --force'),
			$this->runCommand($path, 'COMPOSER_HOME="' . $path . '" php composer.phar update --ansi'),
			$this->runCommand($path, 'COMPOSER_HOME="' . $path . '" php composer.phar dump-autoload --optimize'),
			$this->runCommand($path, 'php app/console cache:clear'),
			$this->runCommand($path, 'php app/console cache:clear --env=prod --no-debug'),
		);
		$response = new Response(json_encode($result));
		$response->headers->set('Content-Type', 'application/json; charset=UTF-8');
		return $response;
	}

	private function runCommand($path, $command)
	{
		$process = new Process('cd ' . $path . ';' . $command);
		$process->run();
		$stdout = $process->getOutput();
		$stderr = $process->getErrorOutput();
		$result = $process->getExitCode();
		return (object)compact('command', 'stdout', 'stderr', 'result');
	}
}
