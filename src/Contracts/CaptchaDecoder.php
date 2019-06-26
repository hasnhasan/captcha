<?php

namespace HasnHasan\Captcha\Contracts;

interface CaptchaDecoder
{
	/**
	 * Call decoder
	 *
	 * @param string $imageUrl
	 * @return mixed
	 */
	public function decode($imageUrl);

	/**
	 * Check Captcha Balance
	 *
	 * @return mixed
	 */
	public function checkBalance();

	/**
	 * Report Invalid Captcha
	 *
	 * @return mixed
	 */
	public function reportInvalidCaptcha();
}