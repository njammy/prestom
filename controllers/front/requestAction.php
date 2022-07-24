<?php
    use GuzzleHttp\RequestOptions;

    class RequestAction {

        public static function getAuthToken($httpClient, $headerKey){
            $body = sprintf('grant_type=client_credentials');
            $access_token = "";
            try{
                $response = $httpClient->post(
                    '/oauth/v3/token', [
                        RequestOptions::BODY => $body,
                        RequestOptions::HEADERS => [
                            'Authorization' => 'Basic '.$headerKey,
                            'Content-Type' => 'application/x-www-form-urlencoded'
                        ]
                    ]
                );
                $respArray = json_decode($response->getBody(), true);
                $access_token = $respArray['access_token'];
            } catch (\Exception $e) {
                echo $e->getMessage();
            }
            return $respArray['access_token'];
        }

    }