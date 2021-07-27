<?php

class Zend {

	// PROPS
	public $prefix = "zendlk_woocommerce";


	public function __construct() {

		/**
		 * We have to add Zend API settings to the WooCommerce
		 * dashboard in order to customize from the client side
		 */
		add_filter('woocommerce_settings_tabs_array', array($this, 'add_zend_settings_tab') , 50);
		add_action('woocommerce_settings_tabs_zendlk_woocommerce_settings_tab', array($this, 'display_settings_tab'));
		add_action('woocommerce_update_options_zendlk_woocommerce_settings_tab', array($this, 'update_settings'));

		/**
		 * We have to register dispatcher to handle WooCommerce order
		 * events.
		 */
		$Dispatcher = new Dispatcher();
		add_action('woocommerce_order_status_changed', array($Dispatcher, 'OnEvent_StatusChange'), 11, 3);
		add_action('woocommerce_new_customer_note', array($Dispatcher, 'OnEvent_Customer_NewOrderNotes'));

	}


	public function add_zend_settings_tab($settings_tabs) { $settings_tabs[$this->prefix.'_settings_tab'] = __('Zend.lk', $this->prefix); return $settings_tabs; }
	public function update_settings() { woocommerce_update_options($this->get_configuration_fields()); }
	public function display_settings_tab() { woocommerce_admin_fields($this->get_configuration_fields()); }


	private function get_configuration_fields() {

		// EMPTY ARRAY
		$fields = array();
		$wc_get_order_status = wc_get_order_statuses();


		/**
		 * Message template sent out for the customer mobile phone
		 * numbers on the checkout process.
		 */
		array_push($fields, [
			"type"		=> "title",
			"title"		=> "Notifications for Customer",
			"desc"		=> "Send SMS to customer's mobile phone. Will be sent to the phone number which customer is providing while checkout process."
		]);
		array_push($fields, [
			"type"		=> "textarea",
			"title"		=> "Default Message",
			"css"		=> "min-width:500px;min-height:80px;",
			"id"		=> $this->prefix."_sms_template_default",
			"default"	=> "Your order #{{order_id}} is now {{order_status}}. Thank you for shopping at {{shop_name}}.",
			"desc_tip"	=> "This message will be sent by default if there are no any text in the following event message fields.",
		]);

		foreach ( $wc_get_order_status as $key => $element ):
			$key = str_replace("wc-", "", $key);
			$key = str_replace("-", "_", $key);
			array_push($fields, [
				"default"	=> "yes",
				"title"		=> $element,
				"type"		=> "checkbox",
				"desc"		=> "Enable ".$element." status alert",
				"id"		=> $this->prefix."_sms_template_status_".$key,
			]);
			array_push($fields, [
				"id"			=> $this->prefix."_sms_template_".$key,
				"type"			=> "textarea",
				"default"		=> "Your order #{{order_id}} is now ".$element.". Thank you for shopping at {{shop_name}}.",
				"placeholder"	=> "Message text for the ".$element." event",
				"css"			=> "min-width:500px;margin-top:-25px;min-height:80px;"
			]);
		endforeach;
		array_push($fields, ["type" => "sectionend"]);


		/**
		 * Message template for notes sent to the customer from
		 * the administrator dashboard.
		 */
		array_push($fields, [
			"type"		=> "title",
			"title"		=> "Customer Note Notifications",
			"desc"		=> "Enable SMS notifications for new customer notes.",
		]);
		array_push($fields, [
			"default"	=> "no",
			"type"		=> "checkbox",
			"title"		=> "Send Notes Alerts",
			"id"		=> $this->prefix."_customer_note_status",
			"desc"		=> "Enable SMS alerts for new customer notes"
		]);
		array_push($fields, [
			"type"		=> "textarea",
			"css"		=> "min-width:500px;",
			"title"		=> "Note Message Prefix",
			"default"	=> "You have a new note: ",
			"id"		=> $this->prefix."_customer_note_sms_template",
			"desc_tip"	=> "Text you provide here will be prepended to your customer note."
		]);
		array_push($fields, ["type" => "sectionend"]);


		/**
		 * Message templates sent out for the shop owners mobile
		 * phone numbers on the new order event.
		 */
		array_push($fields, [
			"type"		=> "title",
			"title"		=> "Notifications for Administrators",
			"desc"		=> "Control the SMS notifications send out to shop owner mobile numbers on new orders"
		]);
		array_push($fields, [
			"default"	=> "no",
			"type"		=> "checkbox",
			"title"		=> "Enable Notifications",
			"id"		=> $this->prefix."_administrator_notification_status",
			"desc"		=> "Enable administrator notifications for new orders."
		]);
		array_push($fields, [
			"type"			=> "text",
			"placeholder"	=> "+94777123456",
			"title"			=> "Mobile numbers",
			"id"			=> $this->prefix."_administrator_notification_recipients",
			"desc_tip"		=> 'Enter mobile numbers. You can use multiple numbers by separating with a comma.'
		]);
		array_push($fields, [
			"title"		=> "Message",
			"type"		=> "textarea",
			"css"		=> "min-width:500px;min-height:80px;",
			"id"		=> $this->prefix."_administrator_notification_sms_template",
			"default"	=> "You have a new customer order for {{shop_name}}. Order #{{order_id}}, Total Value: {{order_amount}}"
		]);
		array_push($fields, ["type" => "sectionend"]);


		/**
		 * We need section to fill out Zend.lk API access information
		 * in order our plugin to work with.
		 */
		array_push($fields, [
			"type"		=> "title",
			"title"		=> "Zend.lk Settings",
			"desc"		=> "Provide following details from your Zend.lk account."
		]);
		array_push($fields, [
			"type"		=> "text",
			"title"		=> "API Token",
			"css"		=> "min-width:300px;",
			"id"		=> $this->prefix."_api_token",
			"desc_tip"	=> "API Token available in your Zend.lk account."
		]);
		array_push($fields, [
			"type"		=> "text",
			"title"		=> "Sender ID",
			"css"		=> "min-width:300px;",
			"id"		=> $this->prefix."_sender_id",
			"desc_tip"	=> "Sender ID available in your Zend.lk account."
		]);
		array_push($fields, ["type" => "sectionend"]);
		return $fields;

	}

}

?>