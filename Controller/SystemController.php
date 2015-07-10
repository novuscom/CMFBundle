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

class SystemController extends Controller
{
    public function updateAction()
    {

        return $this->render('NovuscomCMFBundle:System:index.html.twig', array());
    }

    public function upgradeAction()
    {
        $path = realpath($this->get('kernel')->getRootDir() . '/../');
        $object = $this->runCommand($path, 'COMPOSER_HOME="' . $path . '" php composer.phar update --ansi');
        $response = new Response(json_encode($object));
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
