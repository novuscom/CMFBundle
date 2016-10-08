<?php

namespace Novuscom\CMFBundle;

use Symfony\Component\HttpKernel\Bundle\Bundle;

class NovuscomCMFBundle extends Bundle
{
	public function getParent()
	{
		return 'FOSUserBundle';
	}
}
