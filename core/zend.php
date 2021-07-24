<?php

class Zend {

    // PROPS
    public $prefix = "zendlk_woocommerce";


    public function __construct($BASE_DIR) {

        /**
         * We have to add Zend API settings to the WooCommerce
         * dashboard in order to customize from the client side
         */
        add_filter('woocommerce_settings_tabs_array', array($this, 'add_zend_settings_tab') , 50);
        add_action('woocommerce_settings_tabs_zendlk_woocommerce_settings_tab', array($this, 'display_settings_tab'));
        add_action('woocommerce_update_options_zendlk_woocommerce_settings_tab', array($this, 'update_settings'));

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
            "type" => "title",
            "title" => "Notifications for Customer",
            "desc" => "Send SMS to customer's mobile phone. Will be sent to the phone number which customer is providing while checkout process."
        ]);

        array_push($fields, [
            "id" => $this->prefix."_sms_template_default",
            "type" => "textarea",
            "title" => "Default Message",
            "css" => "min-width:500px;min-height:80px;",
            "default" => "Your order #{{order_id}} is now {{order_status}}. Thank you for shopping at {{shop_name}}.",
            "desc_tip" => "This message will be sent by default if there are no any text in the following event message fields.",
        ]);

        foreach ( $wc_get_order_status as $key => $element ):
            $key = str_replace("wc-", "", $key);
            $key = str_replace("-", "_", $key);
            array_push($fields, [
                "id" => $this->prefix."_sms_template_status_".$key,
                "title" => $element,
                "default" => "yes",
                "type" => "checkbox",
                "desc" => "Enable ".$element." status alert",
            ]);
            array_push($fields, [
                "id" => $this->prefix."_sms_template_".$key,
                "type" => "textarea",
                "placeholder" => "SMS Content for the ".$element." event",
                "css" => "min-width:500px;margin-top:-25px;min-height:80px;"
            ]);
        endforeach;
        array_push($fields, ["type" => "sectionend"]);


        /**
         * We need section to fill out Zend.lk API access information
         * in order our plugin to work with.
         */
        array_push($fields, [
            "type" => "title",
            "title" => "Zend.lk Settings",
            "desc" => "Provide following details from your Zend.lk account."
        ]);
        array_push($fields, [
            "id" => $this->prefix."_api_token",
            "css" => "min-width:300px;",
            "type" => "text",
            "title" => "API Token",
            "desc_tip" => "API Token available in your Zend.lk account."
        ]);
        array_push($fields, [
            "id" => $this->prefix."_sender_id",
            "css" => "min-width:300px;",
            "type" => "text",
            "title" => "Sender ID",
            "desc_tip" => "Sender ID available in your Zend.lk account."
        ]);
        array_push($fields, ["type" => "sectionend"]);
        return $fields;

    }

}

?>