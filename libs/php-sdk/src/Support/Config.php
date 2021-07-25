<?php
namespace Zend\Support;

class Config {

    private static $token = null;
    private static $version = "1.0";
    private static $sender = null;
    private static $url = "https://api.zend.lk/v";

    public static function create(array $Config) {

        /**
         * Check if the authentication token is configured
         * and throw an exception if not configured.
         */
        if ( isset($Config["token"]) AND !empty($Config["token"]) ):
            self::$token = $Config["token"];
        else:
            throw new \Exception("undefined authentication token");
        endif;


        /**
         * Check if we got any specific version to work with or
         * fall back to default version that define in the sdk.
         */
        if ( isset($Config["version"]) AND !empty($Config["version"]) ):
            self::$version = $Config["version"];
        endif;

        /**
         * Check if we got any specific sender id to work with
         * and fall back to nothing. This will evaluvated again
         * in the sending libraries to reconfigure or throw and
         * exception
         */
        if ( isset($Config["sender"]) AND !empty($Config["sender"]) ):
            self::$sender = $Config["sender"];
        endif;

        /**
         * finally we return the instance of self object to utilize
         * in the furthur actions inside in the SDK.
         */
        return new self;
    }

    // GETTERS
    public function token() { return self::$token; }
    public function version() { return self::$version; }


    /**
     * We have to build final API endpoint URL based on the
     * specified access version.
     */
    public function url() { return self::$url.self::$version; }



    /**
     * This method is responsible for returning the sender
     * id if defined or return false if the sender id is
     * not defuned.
     */
    public function sender() { return ( isset(self::$sender) AND !empty(self::$sender) ) ? self::$sender : false; }
}

?>