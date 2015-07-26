<?php

namespace Novuscom\CMFBundle\Event;

use Symfony\Component\EventDispatcher\Event;

class UserEvent extends Event
{
    private $user;

    public function getUser(){
        return $this->user;
    }

    public function __construct($user)
    {
        $this->user = $user;
    }



}