<?php

    require_once dirname(__FILE__) . '/DbConnector.php';
    // use GuzzleHttp\Client;
    // use GuzzleHttp\RequestOptions;

    class ValidationModuleFrontController extends ModuleFrontController
    {
        protected $httpClient;
        public function postProcess()
        {
            $cart = $this->context->cart;
            $authorized = false;
            foreach (Module::getPaymentModules() as $module) {
                if ($module['name'] == 'prestom') {
                    $authorized = true;
                    break;
                }
            }
            if($_GET['status']!= 11){
                if ($cart->id_customer == 0 || $cart->id_address_delivery == 0 || $cart->id_address_invoice == 0 || !$this->module->active) {
                    Tools::redirect('index.php?controller=order&step=1');
                }
                if (!$authorized) {
                    die($this->module->l('This payment method is not available.', 'validation'));
                }
            }
            $this->context->smarty->assign([
                'params' => $_REQUEST,
            ]);

            echo "echo";
            
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
    }
