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
			"sender" => get_option($this->prefix."_sender_id")
		]);
	}


	/**
	 * We have to check if the notification for triggered event
	 * is enabled and allowed for notifications to send out
	 * before processing any furthur. This also handle dispatching
	 * administrator notifications
	 */
	public function OnEvent_StatusChange($order_id, $previous_status, $new_status) {
		if (get_option($this->prefix."_sms_template_status_".$new_status) == "yes") { self::Dispatch_StaffNotifications($order_id, $new_status); }
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


	private function Dispatch_CustomerNotifications($id, $status) {
		error_log("Dispatcher::Dispatch()");
	}

}

?>