<?php

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
    
            if (!$this->module->active || $cart->id_customer == 0 || $cart->id_address_delivery == 0
                || $cart->id_address_invoice == 0) {
                Tools::redirect('index.php?controller=order&step=1');
            }

            foreach (Module::getPaymentModules() as $module) {
                if ($module['name'] == 'prestom') {
                    $authorized = true;
                    break;
                }
            }
    
            if (!$authorized) {
                die($this->l('This payment method is not available.'));
            }
    
            $customer = new Customer($cart->id_customer);
    
            if (!Validate::isLoadedObject($customer)) {
                Tools::redirect('index.php?controller=order&step=1');
            }
    
            // echo Configuration::get('auth_header');
            // echo Configuration::get('merchant_key');
            // echo Configuration::get('merchant_reference');
            // echo Configuration::get('personnal_token');
            
            
            //header(str_replace(" ", "", "Location:validation?status=111&token=".$respArray['access_token']));
        }
    }