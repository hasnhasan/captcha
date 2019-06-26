<?php

namespace hasnhasan\Captcha\Captcha;

use hasnhasan\Captcha\Contracts\CaptchaDecoder;

class CaptchaManager implements CaptchaDecoder
{
	/**
	 * An array of available Decoders
	 *
	 * @var array
	 */
	public $decoders = [
		'bypass'      => ByPassCaptcha::class,
		'decaptcher'  => DeCaptcher::class,
		'2captcha'    => TwoCaptcha::class,
		'imagetyperz' => ImageTyperz::class,
	];
	/**
	 * Default Captcha Service
	 *
	 * @var string
	 */
	private $defaultCaptchaService;
	/**
	 * Instance of Captcha Decoder
	 *
	 * @var CaptchaDecoder
	 */
	private $captchaDecoder;

	/**
	 * CapcthaManager constructor.
	 */
	public function __construct()
	{
		$this->getDecoderInstance();
	}

	/**
	 * Get an instance of Captcha Decoder
	 *
	 * @return void
	 */
	public function getDecoderInstance()
	{
		$decoder = $this->getCaptchaServiceFromConfig();

		$class = $this->getClass($decoder);

		$this->captchaDecoder = new $class;
	}

	/**
	 * Get Default Captcha Service
	 *
	 * @return mixed
	 */
	public function getCaptchaServiceFromConfig()
	{
		$defaultCaptchaService = env('CAPTCHA_SERVICE', 'decaptcher');

		$this->defaultCaptchaService = $defaultCaptchaService;

		return $this->defaultCaptchaService;
	}

	/**
	 * Get an instance of Decoder Class
	 *
	 * @param string $decoder
	 * @return mixed
	 */
	private function getClass($decoder)
	{
		if (!isset($this->decoders[$decoder])) {
			abort('404', "$decoder Decoder does not exist");
		}

		return $this->decoders[$decoder];
	}

	/**
	 * Call the Captcha Class
	 *
	 * @param string $decoder
	 * @return CaptchaDecoder
	 */
	public function via($decoder)
	{
		$class = $this->getClass($decoder);

		$this->captchaDecoder = new $class;

		return $this;
	}

	/**
	 * Call decoder
	 *
	 * @param string $imageUrl
	 * @return mixed
	 */
	public function decode($imageUrl)
	{
		return $this->captchaDecoder->decode($imageUrl);
	}

	/**
	 * Check Captcha Balance
	 *
	 * @return mixed
	 */
	public function checkBalance()
	{
		return $this->captchaDecoder->checkBalance();
	}

	/**
	 * Report Invalid Captcha
	 *
	 * @return mixed
	 */
	public function reportInvalidCaptcha()
	{
		return $this->captchaDecoder->reportInvalidCaptcha();
	}
}