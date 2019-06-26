<?php

namespace hasnhasan\Captcha\Captcha;

use hasnhasan\Captcha\Contracts\CaptchaDecoder;

class ByPassCaptcha implements CaptchaDecoder
{
	/**
	 * Unique Key for making request
	 *
	 * @var string
	 */
	private $key = '';

	/**
	 * ByPassCaptcha constructor.
	 */
	public function __construct()
	{
		$this->key = config('captcha-decoder.bypass.key');
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
	 * @param string $image
	 * @return mixed|null
	 */
	private function submitImage($image)
	{
		$key = $this->key;

		global $bcTaskId;

		$bcTaskId = -1;

		// Read image data of image.
		$fp = fopen($image, 'rb');
		if (!$fp) {
			return NULL;
		}

		$fileSize = filesize($image);

		if ($fileSize <= 0) {
			return NULL;
		}

		$data = fread($fp, $fileSize);
		fclose($fp);

		// Use base64 encoding to encode it.
		$encData = base64_encode($data);

		// Submit it to server.
		if (strlen($key) != 40 && strlen($key) != 32) {
			return NULL;
		}

		$data = $this->bcPostData('http://bypasscaptcha.com/upload.php',
			[
				'key'         => $key,
				'file'        => $encData,
				'submit'      => 'Submit',
				'gen_task_id' => 1,
				'base64_code' => 1,
			]
		);

		$dict = $this->bcSplit($data);

		if (array_key_exists('TaskId', $dict) && array_key_exists('Value', $dict)) {
			$bcTaskId = $dict['TaskId'];

			return $dict['Value'];
		}

		return NULL;
	}

	/**
	 * Post Data
	 *
	 * @param string $url
	 * @param string $data
	 * @return mixed
	 */
	private function bcPostData($url, $data)
	{
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_HEADER, 0);
		curl_setopt($ch, CURLOPT_POST, 1);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
		$ret = curl_exec($ch);
		curl_close($ch);

		return $ret;
	}

	/**
	 * Split the image
	 *
	 * @param mixed $data
	 * @return array
	 */
	public function bcSplit($data)
	{
		$ret = [];

		$lines = explode('\n', $data);
		if ($lines) {
			foreach ($lines as $line) {
				$x = trim($line);
				if (strlen($x) == 0) {
					continue;
				}

				$value = strstr($x, '');
				$name  = '';
				if ($value === false) {
					$name  = $x;
					$value = '';
				} else {
					$name  = substr($x, 0, strlen($x) - strlen($value));
					$value = trim($value);
				}
				$ret[$name] = $value;
			}
		}

		return $ret;
	}

	/**
	 * Check Balance
	 *
	 * @return mixed
	 */
	public function checkBalance()
	{
		$ret = $this->bcPostData('http://bypasscaptcha.com/ex_left.php', [
			'key' => $this->key,
		]);

		$dict = $this->bcSplit($ret);

		return $dict['Left'];
	}

	/**
	 * After using the captcha value, you can send the feedback
	 *
	 * @param string $key
	 * @param string $isInputCorrect
	 */
	public function submitFeedback($key, $isInputCorrect)
	{
		global $bcTaskId;

		$this->bcPostData('http://bypasscaptcha.com/check_value.php', [
			'key'     => $key,
			'task_id' => $bcTaskId,
			'cv'      => ($isInputCorrect ? 1 : 0),
			'submit'  => 'Submit',
		]);
	}

	/**
	 * @return mixed
	 */
	public function reportInvalidCaptcha()
	{
		// TODO: Implement reportInvalidCaptcha() method.
	}
}