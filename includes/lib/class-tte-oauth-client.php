<?php

/* !
 * HybridAuth
 * http://hybridauth.sourceforge.net | http://github.com/hybridauth/hybridauth
 * (c) 2009-2012, HybridAuth authors | http://hybridauth.sourceforge.net/licenses.html 
 * 
 * Our OAuth Library, based on HybridAuth - Modified in order to cater to our plugin specifics
 * @author Samer Bechara <sam@thoughtengineer.com>
 */

use Meetup as TTE_OAUTH;

if (!class_exists('TTE_OAuth_Client')) {

    class TTE_OAuth_Client {

        public $api_base_url = "";

        public $authorize_url = "";

        public $token_url = "";

        // URL to get token info from
        public $token_info_url = "";

        // The application's API Key
        public $client_id = "";
        
        // The application's secret Key
        public $client_secret = "";

        // The URL to redirect to after authentication
        public $redirect_uri = "";

        // The user's access token
        public $access_token = "";

        // Stores the refresh token
        public $refresh_token = "";

        // Access token expires in time, e.g. 123 seconds
        public $access_token_expires_in = "";

        // Date of access token expiry
        public $access_token_expires_at = "";        

        // Stores whether to decode JSON response or not
        public $decode_json = false;

        // Various curl options
        public $curl_time_out = 30;

        public $curl_connect_time_out = 30;

        public $curl_ssl_verifypeer = false;

        public $curl_header = array();

        public $curl_useragent = "OAuth/2 Simple PHP Client v0.1; HybridAuth http://hybridauth.sourceforge.net/";

        public $curl_proxy = null;

        // Stores HTTP response code
        public $http_code = "";

        // Stores HTTP info
        public $http_info = "";


        /*
         * Class construction - Initializes various variables
         */
        public function __construct($client_id = false, $client_secret = false, $redirect_uri = '') {
            $this->client_id = $client_id;
            $this->client_secret = $client_secret;
            $this->redirect_uri = $redirect_uri;

        }

        /*
         * Generates our authorize url
         */
        public function authorizeUrl($extras = array()) {
            $params = array(
                "client_id" => $this->client_id,
                "redirect_uri" => $this->redirect_uri,
                "response_type" => "code"
            );

            if (count($extras))
                foreach ($extras as $k => $v)
                    $params[$k] = $v;

            return $this->authorize_url . "?" . http_build_query($params);

        }

        /*
         * Preforms an authentication
         */
        public function authenticate($code) {
            $params = array(
                "client_id" => $this->client_id,
                "client_secret" => $this->client_secret,
                "grant_type" => "authorization_code",
                "redirect_uri" => $this->redirect_uri,
                "code" => $code
            );

            $response = $this->request($this->token_url, $params, TTE_OAUTH\Plugin_Config::$http_auth_method);

            $response = $this->parseRequestResult($response);

            if (!$response || !isset($response->access_token)) {
                throw new Exception("The Authorization Service has return: " . $response->error);
            }

            if (isset($response->access_token))
                $this->access_token = $response->access_token;
            if (isset($response->refresh_token))
                $this->refresh_token = $response->refresh_token;
            if (isset($response->expires_in))
                $this->access_token_expires_in = $response->expires_in;

            // calculate when the access token expire
            if (isset($response->expires_in)) {
                $this->access_token_expires_at = time() + $response->expires_in;
            }

            return $response;

        }

        /*
         * Checks whether we're authenticated or not
         */
        public function authenticated() {
            if ($this->access_token) {
                if ($this->token_info_url && $this->refresh_token) {
                    // check if this access token has expired, 
                    $tokeninfo = $this->tokenInfo($this->access_token);

                    // if yes, access_token has expired, then ask for a new one
                    if ($tokeninfo && isset($tokeninfo->error)) {
                        $response = $this->refreshToken($this->refresh_token);

                        // if wrong response
                        if (!isset($response->access_token) || !$response->access_token) {
                            throw new Exception("The Authorization Service has return an invalid response while requesting a new access token. given up!");
                        }

                        // set new access_token
                        $this->access_token = $response->access_token;
                    }
                }

                return true;
            }

            return false;

        }

        /**
         * Format and sign an oauth for provider api 
         */
        public function api($url, $method = "GET", $parameters = array()) {
            if (strrpos($url, 'http://') !== 0 && strrpos($url, 'https://') !== 0) {
                $url = $this->api_base_url . $url;
            }

            $parameters[TTE_OAUTH\Plugin_Config::$access_token_name] = $this->access_token;
            $response = null;

            switch ($method) {
                case 'GET' : $response = $this->request($url, $parameters, "GET");
                    break;
                case 'POST' : $response = $this->request($url, $parameters, "POST");
                    break;
            }

            if ($response && $this->decode_json) {
                $response = json_decode($response);
            }

            return $response;

        }

        /**
         * GET wrapper for provider apis request
         */
        function get($url, $parameters = array()) {
            return $this->api($url, 'GET', $parameters);

        }

        /**
         * POST wrapper for provider apis request
         */
        function post($url, $parameters = array()) {
            return $this->api($url, 'POST', $parameters);

        }

        // Get access token information
        public function tokenInfo($accesstoken) {
            $params['access_token'] = $this->access_token;
            $response = $this->request($this->token_info_url, $params);
            return $this->parseRequestResult($response);

        }

        // Refresh our access token
        public function refreshToken($parameters = array()) {
            $params = array(
                "client_id" => $this->client_id,
                "client_secret" => $this->client_secret,
                "grant_type" => "refresh_token"
            );

            foreach ($parameters as $k => $v) {
                $params[$k] = $v;
            }

            $response = $this->request($this->token_url, $params, "POST");
            return $this->parseRequestResult($response);

        }

        // Perform a request
        private function request($url, $params = false, $type = "GET") {

            if ($type == "GET") {
                $url = $url . ( strpos($url, '?') ? '&' : '?' ) . http_build_query($params);
            }

            $this->http_info = array();
            $ch = curl_init();

            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
            curl_setopt($ch, CURLOPT_TIMEOUT, $this->curl_time_out);
            curl_setopt($ch, CURLOPT_USERAGENT, $this->curl_useragent);
            curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, $this->curl_connect_time_out);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, $this->curl_ssl_verifypeer);
            curl_setopt($ch, CURLOPT_HTTPHEADER, $this->curl_header);

            if ($this->curl_proxy) {
                curl_setopt($ch, CURLOPT_PROXY, $this->curl_proxy);
            }

            if ($type == "POST") {
                curl_setopt($ch, CURLOPT_POST, 1);
                if ($params)
                    curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($params));
            }

            $response = curl_exec($ch);

            $this->http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            $this->http_info = array_merge($this->http_info, curl_getinfo($ch));

            curl_close($ch);

            return $response;

        }

        /*
         * Parses our request result and returns it
         */
        private function parseRequestResult($result) {
            if (json_decode($result))
                return json_decode($result);

            parse_str($result, $ouput);

            $result = new StdClass();

            foreach ($ouput as $k => $v)
                $result->$k = $v;

            return $result;

        }

    }

}