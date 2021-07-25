<?php

use \Zend\Support\Config;
use \Zend\API\SMS;

class Dispatcher {

	// PROPS
	private $prefix = "zendlk_woocommerce";
	private $Config = null;


	/**
	 * We have to make Zend API client to talk with Zend API
	 * and dispatch the proper notifications to the customers
	 */
	public function __construct() {
		$this->Config = Config::create([
			"token" => get_option($this->prefix."_api_token"),
			"sender" => get_option($this->prefix."_sender_id"),
			"version" => "2.0"
		]);
	}


	/**
	 * We have to check if the notification for triggered event
	 * is enabled and allowed for notifications to send out
	 * before processing any furthur. This also handle dispatching
	 * administrator notifications
	 */
	public function OnEvent_StatusChange($order_id, $previous_status, $new_status) {
		self::Dispatch_CustomerNotifications($order_id, $new_status);
		if ($new_status == "processing" &&  get_option($this->prefix."_administrator_notification_status") == "yes") { $this->Dispatch_StaffNotifications($order_id, $new_status); }
	}

	/**
	 * This method will dispatch the formatted notification to
	 * store owners mobile numbers queried from plugin configuration
	 */
	private function Dispatch_StaffNotifications($id, $status) {

		/**
		 * Check if the administrator recipients string is not empty
		 * before processing any furthur with sending operation.
		 */
		if ( !empty(get_option($this->prefix."_administrator_notification_recipients")) ):
			$recipients = explode(",", get_option($this->prefix."_administrator_notification_recipients"));


			/**
			 * Check if the administrator message template string is
			 * not empty before processing any furthur with the sending
			 * operation.
			 */
			if ( !empty(get_option($this->prefix."_administrator_notification_sms_template")) ):

				$Order = new WC_Order($id);

				$short_code_informations = [
					"{{shop_name}}"			=> get_bloginfo('name'),
					"{{order_id}}"			=> $Order->get_order_number(),
					"{{order_amount}}"		=> $Order->get_total(),
					"{{order_status}}"		=> ucfirst($Order->get_status()),
					"{{first_name}}"		=> ucfirst($Order->billing_first_name),
					"{{last_name}}"			=> ucfirst($Order->billing_last_name),
					"{{billing_city}}"		=> ucfirst($Order->billing_city),
					"{{customer_phone}}"	=> $Order->billing_phone,
				];

				$message = get_option($this->prefix."_administrator_notification_sms_template");
				$message = str_replace(array_keys($short_code_informations), $short_code_informations, $message);

				// SEND
				SMS::compose($this->Config, [
					"to" => $recipients,
					"text" => $message,
				])->send();

			endif;

		endif;

	}


	/**
	 * Here we dispatch notification sms to customers
	 * according to the information submited to the
	 * function by woocom implementation.
	 */
	private function Dispatch_CustomerNotifications($id, $status) {

		/**
		 * Check if the requested message template is enabled
		 * by the configuration.
		 */
		$key = str_replace("-", "_", $status);
		if (get_option($this->prefix."_sms_template_status_".$key) == "yes"):

			/**
			 * Check if the message template for the requested
			 * status is not empty and fall back to the default
			 * message if the specified status template is empty
			 */
			$message = get_option($this->prefix."_sms_template_".$key);
			if (empty($message)):
				$message = get_option($this->prefix."_sms_template_default");
			endif;

			$Order = new WC_Order($id);
			$short_code_informations = [
				"{{shop_name}}"			=> get_bloginfo('name'),
				"{{order_id}}"			=> $Order->get_order_number(),
				"{{order_amount}}"		=> $Order->get_total(),
				"{{order_status}}"		=> ucfirst($Order->get_status()),
				"{{first_name}}"		=> ucfirst($Order->billing_first_name),
				"{{last_name}}"			=> ucfirst($Order->billing_last_name),
				"{{billing_city}}"		=> ucfirst($Order->billing_city),
				"{{customer_phone}}"	=> $Order->billing_phone,
			];

			$message = str_replace(array_keys($short_code_informations), $short_code_informations, $message);

			/**
			 * Now we have to check and format the mobile number
			 * of the recipient according to E164 standard format
			 * before handing over to the Zend API.
			 */
			preg_match_all('/^(?:0|94|\+94|0094|\+940)?(?:(?P<area>11|21|23|24|25|26|27|31|32|33|34|35|36|37|38|41|45|47|51|52|54|55|57|63|65|66|67|81|91)(?P<land_carrier>0|2|3|4|5|7|9)|7(?P<mobile_carrier>0|1|2|4|5|6|7|8)\d)\d{6}$/', $Order->billing_phone, $matches, PREG_SET_ORDER, 0);

			// SEND
			SMS::compose($this->Config, [
				"to" => ["+94".substr($matches[0][0], -9)],
				"text" => $message,
			])->send();

		endif;

	}

}

?>