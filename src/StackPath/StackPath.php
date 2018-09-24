<?php

/**
*
*/

namespace StackPath;

class StackPath
{
    public $gateway = "https://gateway.stackpath.com";
    public $creds = [
      "stack_id" => "9ad4bdfc-77ec-41bb-ac95-da137d837742"
    ];
    public $debug = false;

    /**
    * Instantiates the client
    *
    * If you haven't declared the client_id and client_secret in the config above, it will be sourced from environment variables
    */
    public function __construct()
    {
        /** Instantiates Guzzle client with gateway as default root */
        $this->client = new \GuzzleHttp\Client([
          "base_uri" => $this->gateway,
          "timeout" => 30
        ]);

        /** Use environment variables for client_id and secret if not supplied in $this->creds */
        if (!isset($this->creds["client_id"])) {
            $this->creds["client_id"] = getenv("STACKPATH_ID");
        }

        if (!isset($this->creds["client_secret"])) {
            $this->creds["client_secret"] = getenv("STACKPATH_SECRET");
        }

        /** Retrive the auth token on instantiation */
        $this->token = $this->post("identity/v1/oauth2/token", ["json" => [
          "client_id" => $this->creds["client_id"],
          "client_secret" => $this->creds["client_secret"],
          "grant_type" => "client_credentials"
        ]])->access_token;
    }

    /**
    * Shorthand method for GET requests
    *
    * @param String $url relative or absolute URL
    * @param Array $payload Data being sent to the API
    */
    public function get($url, $payload)
    {
        return $this->request([
          "url" => $url,
          "method" => "GET",
          "payload" => $payload
        ]);
    }

    /**
    * Shorthand method for POST requests
    *
    * @param String $url relative or absolute URL
    * @param Array $payload Data being sent to the API
    */
    public function post($url, $payload)
    {
        return $this->request([
          "url" => $url,
          "method" => "POST",
          "payload" => $payload
        ]);
    }

    /**
    * Shorthand method for DELETE requests
    *
    * @param String $url relative or absolute URL
    * @param Array $payload Data being sent to the API
    */
    public function delete($url, $payload)
    {
        return $this->request([
          "url" => $url,
          "method" => "DELETE",
          "payload" => $payload
        ]);
    }

    /**
    * Purges files from StackPath CDN
    *
    * Maps $files array to object for removal
    * @param Array $files full URLs to paths for removal
    */
    public function purge_files($fileList, $stack_id = false)
    {
        if (!$stack_id) {
            $stack_id = $this->creds["stack_id"];
        }

        $files = [];

        foreach ($fileList as $file) {
            $files[] = ["url" => $file];
        }

        $purge_id = $this->post("cdn/v1/stacks/{$stack_id}/purge", ["json" => [
          "items" => $files,
        ]]);
    }

    /**
    * Shorthand method for PUT requests
    *
    * @param String $url relative or absolute URL
    * @param Array $payload Data being sent to the API
    */
    public function put($url, $payload)
    {
        return $this->request([
          "url" => $url,
          "method" => "PUT",
          "payload" => $payload
        ]);
    }

    /**
    * Universal method for sending requests to StackPath
    *
    * @param Array $opts All of the request options
    */
    public function request($opts = [])
    {
        /**
        * Default values can be overridden by defining them in the $opts passed to request
        */
        $default_options = [
          /** Payload is this library's term for all of the data being sent to the API */
          "payload" => [

            /** Unless overridden, every payload will send the Accept header set to application/json */
            "headers" => [
              'Accept' => 'application/json'
            ]
          ]
        ];


        /**
        * Recursive merge of custom options over defaults
        */
        $opts = array_merge_recursive($default_options, $opts);

        /**
        * Method MUST be defined in the opts sent over
        */
        if (isset($opts["method"])) {
            $method = $opts["method"];
        } else {
            die("Please provide a method for your request");
        }

        /**
        * URL MUST be defined in the opts sent over
        */
        if (isset($opts["url"])) {
            $url = $opts["url"];
        } else {
            die("Please provide a url for your request");
        }


        $payload = $opts["payload"];

        /** Honors debug mode set above */
        $payload['debug'] = $this->debug;

        /** If the bearer token has been retrieved, supply it as the auth header */
        if (isset($this->token)) {
            $payload["headers"]["Authorization"] = sprintf("Bearer %s", $this->token);
        }

        /** Default payload options. Can be overriden by defining them in $opt["payload"] when supplied to this method */
        $payload_defaults = [
          "allow_redirects" => true
        ];


        $payload = array_merge_recursive($payload_defaults, $payload);

        /** Fires the request */
        $res = $this->client->request($method, $url, $payload);


        try {
            $response = json_decode($res->getBody()->getContents());
        } catch (\Exception $e) {
            $response = $res->getBody()->getContents();
        }

        return $response;
    }
}
