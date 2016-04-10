<?php

namespace Novuscom\CMFBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class AdminController extends Controller
{
	public function indexAction()
	{
		return $this->render('NovuscomCMFBundle:Default:admin.html.twig');
	}
}
