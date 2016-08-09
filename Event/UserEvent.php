<?php

namespace Novuscom\CMFBundle\Event;

use Symfony\Component\EventDispatcher\Event;

class UserEvent extends Event
{
	private $user;

	private $routeParams;

	private $request;

	public function getRequest()
	{
		return $this->request;
	}

	public function getUser()
	{
		return $this->user;
	}

	public function getRouteParams()
	{
		return $this->routeParams;
	}

	public function __construct($user, $routeParams, $request)
	{
		$this->user = $user;
		$this->routeParams = $routeParams;
		$this->request = $request;
	}


}