<?php

namespace hasnhasan\Captcha\Captcha;

use GuzzleHttp\Client;
use hasnhasan\Captcha\Contracts\CaptchaDecoder;

class TwoCaptcha implements CaptchaDecoder
{
	/**
	 * Id of Request sent
	 *
	 * @var string
	 */
	protected $captchaId;
	/**
	 * Unique Key for making request
	 *
	 * @var string
	 */
	private $key = '';

	/**
	 * TwoCaptcha constructor.
	 */
	public function __construct()
	{
		$this->key = config('captcha-decoder.2captcha.key');
	}

	/**
	 * Call decoder
	 *
	 * @param string $imageUrl
	 * @return mixed
	 */
	public function decode($imageUrl)
	{
		return $this->submitImage($imageUrl);
	}

	/**
	 * Submit Image
	 *
	 * @param string $imageUrl
	 * @return mixed
	 */
	private function submitImage($imageUrl)
	{
		$client = new Client();

		$path   = $imageUrl;
		$type   = pathinfo($path, PATHINFO_EXTENSION);
		$data   = file_get_contents($path);
		$base64 = 'data:image/'.$type.';base64,'.base64_encode($data);

		$response = $client->post('http://2captcha.com/in.php', [
			'form_params' => [
				'key'      => $this->key,
				'body'     => $base64,
				'method'   => 'base64',
				'json'     => 1,
				'regsense' => 1,
				'numeric'  => 4,
				'phrase'   => 0,
				'calc'     => 0,

			],
		]);

		$initialResponse = json_decode($response->getBody()->getContents());

		if (isset($initialResponse->request)) {

			$this->captchaId = $initialResponse->request;

			do {
				$finalResponse = $this->getCaptchaValue(1);

			} while ($finalResponse->request == 'CAPCHA_NOT_READY');

			\Log::debug('New CAPTCHA FOUND VIA 2CAPTCHER : '.$finalResponse->request.' For ID : '.$this->captchaId);

			return $finalResponse->request;
		}
	}

	/**
	 * Make get request to get captcha text
	 *
	 * @param integer $sleepTime
	 * @return mixed
	 */
	private function getCaptchaValue($sleepTime)
	{
		sleep($sleepTime);

		$client = new Client();

		$response = $client->get('http://2captcha.com/res.php?key='.$this->key.'&action=get&id='.$this->captchaId.'&json=1');

		return json_decode($response->getBody()->getContents());
	}

	/**
	 * Check Captcha Balance
	 *
	 * @return mixed
	 */
	public function checkBalance()
	{
		// TODO: Implement checkBalance() method.
	}

	/**
	 * Report Invalid Captcha
	 *
	 * @return mixed
	 */
	public function reportInvalidCaptcha()
	{
		\Log::debug('INVALID CAPTCHA FOUND VIA 2CAPTCHER : '.$this->captchaId);

		$client = new Client();

		$response = $client->get('http://2captcha.com/res.php?key='.$this->key.'&action=reportbad&id='.$this->captchaId.'&json=1');

		return json_decode($response->getBody()->getContents());
	}
}