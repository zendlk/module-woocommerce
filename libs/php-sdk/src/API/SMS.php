<?php
namespace Zend\API;

class SMS {

    private static $json = array();
    private static $config = null;
    private static $response = null;

    /**
     * Here we arrange all the information that need to compose
     * single SMS message and then we return instance of self
     * class to continue work.
     */
    public static function compose(\Zend\Support\Config $Config, array $Message) {
        self::$config = $Config;

        /**
         * First of all check if we got the sender id information
         * on the message and override current sender id configuration
         * provided by the configuration instance. otherwise we have
         * to check configuration instance for sender id information.
         */
        if ( isset($Message["sender"]) AND !empty($Message["sender"]) ):
            self::$json["from"] = $Message["sender"];
        else:
            if ( self::$config->sender() ):
                self::$json["from"] = self::$config->sender();
            else:
                throw new \Exception("sender id undefined");
            endif;
        endif;

        /**
         * Check if we got valid set of destination or and throw an
         * Exception if there is no destinations defined.
         */
        if ( isset($Message["to"]) AND !empty($Message["to"]) ):
            self::$json["to"] = $Message["to"];
        else:
            throw new \Exception("Undefined recipients");
        endif;

        /**
         * Check if we got valid message body and throw an
         * Exception if there is no message body defined.
         */
        if ( array_key_exists("text", $Message) AND !empty($Message["text"]) ):
            self::$json["message"] = $Message["text"];
        else:
            throw new \Exception("message text undefined");
        endif;

        /**
         * Here we return the instance of self to chain other
         * method on the self instance for furthur tasks
         */
        return new self;

    }

    /**
     * We can call to this method to dispatch the API
     * call to Zend API and then return response status
     * based on the returned data.
     */
    public function send() {

        $handler = curl_init(self::$config->url()."/sms");
        curl_setopt($handler, CURLOPT_CUSTOMREQUEST, "POST");
        curl_setopt($handler, CURLOPT_POSTFIELDS, json_encode(self::$json));
        curl_setopt($handler, CURLOPT_HEADER, false);
        curl_setopt($handler, CURLOPT_HTTPHEADER, [
            "Content-Type: application/json",
            "Authorization: Bearer ".self::$config->token()
        ]);
        curl_setopt($handler, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($handler, CURLOPT_USERAGENT, "zend/php-sdk");
        $response = json_decode(curl_exec($handler), true);
        curl_close($handler);

        /**
         * return true or false based on the reponse we got
         * from the Zend API.
         */
        return ( isset(self::$response["status"]) == "success" ) ? true : false;

    }

    /**
     * Get information out from the API response once we called
     * the API and receive information.
     */
    public function __get($key) {
        if ( self::$response != null ):
            if ( array_key_exists($key, self::$response["data"]) ):
                return self::$response["data"][$key];
            else:
                return false;
            endif;
        else:
            return false;
        endif;
    }

}

?>