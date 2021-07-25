<?php
namespace Zend\API;

class Account {

	private static $config = null;

	/**
	 * Get information related to the specified account
	 * in the JWT token.
	 */
	public static function get(\Zend\Support\Config $Config) {
		self::$config = $Config;

		$handler = curl_init(self::$config->url()."/account");
		curl_setopt($handler, CURLOPT_CUSTOMREQUEST, "GET");
		curl_setopt($handler, CURLOPT_HEADER, false);
		curl_setopt($handler, CURLOPT_HTTPHEADER, [
			"Content-Type: application/json",
			"Authorization: Bearer ".self::$config->token()
		]);
		curl_setopt($handler, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($handler, CURLOPT_USERAGENT, "zend/php-sdk");
		$response = json_decode(curl_exec($handler), true);
		curl_close($handler);
        return $response;

	}

}

?>
