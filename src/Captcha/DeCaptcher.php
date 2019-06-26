<?php

namespace hasnhasan\Captcha\Captcha;

use hasnhasan\Captcha\Contracts\CaptchaDecoder;

require 'DeCaptcher/ccproto_client.php';

class DeCaptcher implements CaptchaDecoder
{
	/**
	 * Unique Id for each image
	 *
	 * @var string
	 */
	protected $majorId;
	/**
	 * Unique Id for each image
	 *
	 * @var string
	 */
	protected $minorId;
	/**
	 * HOST Name
	 *
	 * @var string
	 */
	protected $host = 'api.de-captcher.info';
	/**
	 * Default Port Number
	 *
	 * @var string
	 */
	protected $port = '36396';
	/**
	 * Username to make request
	 *
	 * @var string
	 */
	protected $username = '';
	/**
	 * Password to make request
	 *
	 * @var string
	 */
	protected $password = '';

	/**
	 * DeCaptcher constructor.
	 */
	public function __construct()
	{
		$this->host     = config('captcha-decoder.decaptcher.host');
		$this->port     = config('captcha-decoder.decaptcher.port');
		$this->username = config('captcha-decoder.decaptcher.username');
		$this->password = config('captcha-decoder.decaptcher.password');
	}

	/**
	 * Call decoder
	 *
	 * @param string $imageUrl
	 * @return mixed
	 */
	public function decode($imageUrl)
	{
		return $this->getCaptchaValue($imageUrl);
	}

	/**
	 * Decode Captcha Value
	 *
	 * @param string $image
	 * @return null|string|void
	 */
	private function getCaptchaValue($image)
	{
		$ccp = new \ccproto();
		$ccp->init();

		if ($ccp->login($this->host, $this->port, $this->username, $this->password) < 0) {
			echo 'FAILED\n';

			return false;
		} else {
			echo 'OK\n';
		}

		$systemLoad = 0;
		if ($ccp->system_load($systemLoad) != ccERR_OK) {
			echo 'system_load() FAILED\n';

			return false;
		}

		$balance = 0;
		if ($ccp->balance($balance) != ccERR_OK) {
			return false;
		}

		$text    = NULL;
		$majorId = 0;
		$minorId = 0;
		for ($i = 0; $i < 1; $i++) {
			$pict = file_get_contents($image);
			$text = '';

			$pictTo   = ptoDEFAULT;
			$pictType = ptUNSPECIFIED;

			$res = $ccp->picture2($pict, $pictTo, $pictType, $text, $majorId, $minorId);
			switch ($res) {
				// Most common return codes.
				case ccERR_OK:
					echo("got text for id=".$majorId."/".$minorId.", type=".$pictType.", to=".$pictTo.", text='".$text."'");
					break;

				case ccERR_BALANCE:
					echo 'not enough funds to process a picture, balance is depleted';
					break;

				case ccERR_TIMEOUT:
					echo 'picture has been timed out on server (payment not taken)';
					break;

				case ccERR_OVERLOAD:
					echo 'temporarily server-side error';
					echo ' servers overloaded, wait a little before sending a new picture';
					break;

				// Local errors.
				case ccERR_STATUS:
					echo 'local error.';
					echo ' either ccproto_init() or ccproto_login() has not been successfully called prior to ccproto_picture()';
					echo ' need ccproto_init() and ccproto_login() to be called';
					break;

				// Network errors.
				case ccERR_NET_ERROR:
					echo ' network troubles, better to call ccproto_login() again';
					break;

				// Server-side errors.
				case ccERR_TEXT_SIZE:
					echo 'size of the text returned is too big';
					break;

				case ccERR_GENERAL:
					echo 'server-side error, better to call ccproto_login() again';
					break;

				case ccERR_UNKNOWN:
					echo ' unknown error, better to call ccproto_login() again';
					break;

				default:
					break;
			}
			echo "\n";

		}//end for
		$this->majorId = $majorId;
		$this->minorId = $minorId;

		\Log::debug('NEW CAPTCHA FOUND VIA DECAPTCHER : '.$majorId.' : '.$minorId);

		return $text;
	}

	/**
	 * Check Balance
	 *
	 * @return mixed
	 */
	public function checkBalance()
	{
		$ccp = new \ccproto();
		$ccp->init();

		if ($ccp->login($this->host, $this->port, $this->username, $this->password) < 0) {
			return;
		}

		$balance = 0;
		if ($ccp->balance($balance) != ccERR_OK) {
			echo 'balance() FAILED\n';

			return;
		}

		echo 'Balance='.$balance.'\n';

		$ccp->close();
	}

	/**
	 * Report Invalid Captcha
	 *
	 * @return void
	 */
	public function reportInvalidCaptcha()
	{
		$majorId = $this->majorId;
		$minorId = $this->minorId;

		\Log::debug('INVALID CAPTCHA FOUND VIA DECAPTCHER : '.$majorId.' : '.$minorId);

		$ccp = new \ccproto();
		$ccp->init();

		if ($ccp->login($this->host, $this->port, $this->username, $this->password) < 0) {
			return;
		}

		$ccp->picture_bad2($majorId, $minorId);
		$ccp->close();
	}
}