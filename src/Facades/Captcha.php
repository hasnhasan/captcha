<?php

namespace hasnhasan\Captcha\Facades;

use Illuminate\Support\Facades\Facade;

class Captcha extends Facade
{
	protected static function getFacadeAccessor()
	{
		return 'captcha';
	}
}