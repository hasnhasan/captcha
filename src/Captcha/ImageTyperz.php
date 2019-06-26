<?php

namespace HasnHasan\Captcha\Captcha;

use HasnHasan\Captcha\Contracts\CaptchaDecoder;

require_once 'ImageTyperz/ImageTyperzApi.php';

class ImageTyperz implements CaptchaDecoder
{
	/**
	 * Image Id generated for each image submitted
	 *
	 * @var string
	 */
	protected $imageId;
	/**
	 * Username
	 *
	 * @var string
	 */
	private $username = '';
	/**
	 * Password
	 *
	 * @var string
	 */
	private $password = '';

	/**
	 * ImageTyperz constructor.
	 */
	public function __construct()
	{
		$this->username = config('captcha-decoder.imagetyperz.username');
		$this->password = config('captcha-decoder.imagetyperz.password');
	}

	/**
	 * Call decoder
	 *
	 * @param string $imageUrl
	 * @return mixed
	 */
	public function decode($imageUrl)
	{
		$imageReferenceID = uploadcaptcha($this->username, $this->password, $imageUrl);

		$response = str_replace('Uploading file...', '', $imageReferenceID);
		$response = explode('|', $response);

		$this->imageId = $response[0];

		\Log::debug('NEW CAPTCHA VIA ImageTyperz : '.$this->imageId);

		return isset($response[1]) ? $response[1] : false;
	}

	/**
	 * Check Captcha Balance
	 *
	 * @return mixed
	 */
	public function checkBalance()
	{
		return checkBalance($this->username, $this->password);
	}

	/**
	 * Report Invalid Captcha
	 *
	 * @return mixed
	 */
	public function reportInvalidCaptcha()
	{
		\Log::debug('Invalid Captcha VIA ImageTyperz : '.$this->imageId);

		return badImage($this->username, $this->password, $this->imageId);
	}
}