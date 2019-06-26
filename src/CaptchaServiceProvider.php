<?php

namespace HasnHasan\Captcha;

use Illuminate\Support\ServiceProvider;
use HasnHasan\Captcha\Captcha\CaptchaManager;

class CaptchaServiceProvider extends ServiceProvider
{
	/**
	 * Register the service provider.
	 *
	 * @return void
	 */
	public function register()
	{
		$this->app->singleton('captcha', function($app) {
			return new CaptchaManager();
		});
	}

	/**
	 * Bootstrap services.
	 *
	 * @return void
	 */
	public function boot()
	{

		$this->publishes([
			__DIR__.'/Configs/captcha-decoder.php' => config_path('captcha-decoder.php'),
		]);

		$this->mergeConfigFrom(
			__DIR__.'/Configs/captcha-decoder.php',
			'captcha-decoder'
		);

	}
}
