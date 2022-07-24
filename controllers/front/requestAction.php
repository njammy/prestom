<?php

    require_once dirname(__FILE__) . '/dbAction.php';
    use GuzzleHttp\RequestOptions;

    class RequestAction 
    {

        public static function getAuthToken($httpClient, $headerKey)
        {
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
            } catch (\Exception $e) {
                echo $e->getMessage();
            }
            return $respArray['access_token'];
        }

        public static function getOrangePaymentUrl($httpClient, $customer, $cart, $currency_id, $merchantKey, $amount, $orderId, $reference, $personnalToken, $url_base_str, $tokenKey) 
        {
            $body = sprintf('{
                "merchant_key": "%s",
                "currency": "XAF",
                "amount": %s,
                "order_id": "%s",
                "return_url": "%s/historique-commandes",
                "cancel_url": "%s/module/prestom/validation?status=10",
                "notif_url": "%s/module/prestom/notify?token=%s",
                "lang": "fr",
                "reference": "%s"
            }',$merchantKey, $amount, $orderId, $url_base_str, $url_base_str, $url_base_str, $personnalToken, $reference);

            try{
                $respWebsiteRedirrect = $httpClient->post(
                    '/orange-money-webpay/cm/v1/webpayment', [
                        RequestOptions::BODY => $body,
                        RequestOptions::HEADERS => [
                            'Authorization' => 'Bearer '.$tokenKey,
                            'Content-Type' => 'application/json'
                        ]
                    ]
                );
                $respArray = json_decode($respWebsiteRedirrect->getBody(), true);
                DbAction::setDataTrackWithToken($respArray["notif_token"],$cart->id, $customer->secure_key, $currency_id, ''.$amount);
                header('Location: '.$respArray["payment_url"]);
            } catch (\Exception $e) {
                echo $e->getMessage();
            }
        }
    }