<?php

    class DbAction extends ObjectModel {

        public static function getByPayToken($token)
        {
            $query = new DBQuery();
            $query->from("prestom_payment_track");
            $query->where('notif_token = \'' . $token . '\'');
            $rowOrder = Db::getInstance()->getRow($query);
            if (is_array($rowOrder)) {
                return $rowOrder;
            } else {
                return [];
            }
        }

        public static function setDataTrackWithToken($notif_token, $cart_id, $customer_secure_key, $currency_id, $total_order) {
            Db::getInstance()->insert(
                'prestom_payment_track',
                [
                    "notif_token" => $notif_token,
                    "cart_id" => $cart_id,
                    "customer_secure_key" => $customer_secure_key,
                    "currency_id" => $currency_id,
                    "total_order" => $total_order,
                    "is_paid" => 0
                ]
            );
        }

        public static function updateCartEntryAfterValidation($token){
            Db::getInstance()->update(
                'prestom_payment_track',
                [
                    "is_paid" => 1
                ],
                'notif_token = "' . $token. '"'
            );
        }

    }