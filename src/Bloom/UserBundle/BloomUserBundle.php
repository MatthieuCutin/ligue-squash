<?php

namespace Bloom\UserBundle;

use Symfony\Component\HttpKernel\Bundle\Bundle;

class BloomUserBundle extends Bundle
{
	public function getParent()
	{
		return 'FOSUserBundle';
	}
}
