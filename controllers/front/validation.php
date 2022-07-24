<?php

    require_once dirname(__FILE__) . '/dbAction.php';
    require_once dirname(__FILE__) . '/requestAction.php';
    use GuzzleHttp\Client;

    class PrestomValidationModuleFrontController extends ModuleFrontController
    {
        protected $httpClient;
        public function postProcess()
        {
            $this->httpClient = new Client(['base_url' => 'https://api.orange.com']);
            $cart = $this->context->cart;
            $authorized = false;
            $customer = new Customer($cart->id_customer);

            foreach (Module::getPaymentModules() as $module) {
                if ($module['name'] == 'prestom') {
                    $authorized = true;
                    break;
                }
            }

            if($_GET['status']!= 111){
                if ($cart->id_customer == 0 || $cart->id_address_delivery == 0 || $cart->id_address_invoice == 0 || !$this->module->active) {
                    Tools::redirect('index.php?controller=order&step=1');
                }
                if (!$authorized) {
                    die($this->module->l('This payment method is not available.', 'validation'));
                }
                if (!Validate::isLoadedObject($customer)) {
                    Tools::redirect('index.php?controller=order&step=1');
                }
            }
            
            $status = $_GET['status'];
            switch ($status) {
                case 10:
                    $this->setTemplate('module:prestom/views/templates/front/payment_cancel.tpl');
                    break;
                case 110:
                    $merchantKey = Configuration::get('merchant_key');
                    $amount = (float)$cart->getOrderTotal(true, Cart::BOTH);
                    $orderId = $this->generateRandomString(10, $cart->id); 
                    $reference = Configuration::get('merchant_reference');
                    $personnalToken = Configuration::get('personnal_token');
                    $tokenKey =  $_GET['token'];
                    RequestAction::getOrangePaymentUrl($this->httpClient, $customer, $cart, $this->context->currency->id, $merchantKey, $amount, $orderId, $reference, $personnalToken, $this->url(), $tokenKey);
                    break;
                case 111:
                    $resBd = DbAction::getByPayToken($_GET['notiftoken']);
                    if(count($resBd)!=0){
                        $this->module->validateOrder($resBd['cart_id'], Configuration::get('PS_OS_PAYMENT'), floatval($resBd['total_order']), 'Orange Money', NULL, $mailVars, (int)$resBd['currency_id'], false, $resBd['customer_secure_key']);
                        DbAction::updateCartEntryAfterValidation($_GET['notiftoken']);
                        header($_SERVER['SERVER_PROTOCOL'].' 200 OK');
                        exit;
                    }
                    break;
                default:
                    $tokenKey = RequestAction::getAuthToken($this->httpClient, Configuration::get('auth_header'));
                    header(str_replace(" ", "", "Location:validation?status=110&token=".$tokenKey));
            }
        }

        
        function generateRandomString($length, $cartId) {
            $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
            $charactersLength = strlen($characters);
            $randomString = '';
            for ($i = 0; $i < $length; $i++) {
                $randomString .= $characters[rand(0, $charactersLength - 1)];
            }
            return $randomString.'_'.$cartId;
        }

        function url() {
            $potential_url = sprintf(
                "%s://%s",
                isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] != 'off' ? 'https' : 'http',
                $_SERVER['SERVER_NAME']
            );
            return $potential_url == "https://localhost" || $potential_url == "http://localhost" ? "https://github.com" : $potential_url;
        }
    }