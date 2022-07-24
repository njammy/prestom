<?php

    if (!defined('_PS_VERSION_')) {
        exit;
    }

    class Prestom extends PaymentModule
    {

        public function __construct()
        {
            $this->name = 'prestom';
            $this->tab = 'payments_gateways';
            $this->version = '1.0.0';
            $this->ps_versions_compliancy = array('min' => '1.7', 'max' => _PS_VERSION_);
            $this->author = 'njammy';
            $this->controllers = array('validation');
            $this->is_eu_compatible = 1;

            $this->currencies = true;
            $this->currencies_mode = 'checkbox';

            $this->bootstrap = true;
            parent::__construct();

            $this->displayName = $this->l('PRESTOM');
            $this->description = $this->l('Make payment');

            if (!count(Currency::checkPaymentCurrencies($this->id))) {
                $this->warning = $this->l('No currency has been set for this module.');
            }
        }

        public function install()
        {
            $sql = "CREATE TABLE IF NOT EXISTS `"._DB_PREFIX_."prestom_payment_track`(
                    `id_prestom_pay` INT(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,
                    `notif_token` VARCHAR(50), 
                    `cart_id` INT(11), 
                    `customer_secure_key` VARCHAR(50),
                    `currency_id` INT(11),
                    `total_order` VARCHAR(50),
                    `is_paid` INT(1),
                    `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
                )";
                
            if(!$result=Db::getInstance()->Execute($sql))
                return false;

            if (!parent::install() || !$this->registerHook('paymentOptions') || !$this->registerHook('paymentReturn')) {
                return false;
            }
            return true;
        }

        public function getContent()
        {
            $output = '';
            if (Tools::isSubmit('submit' . $this->name)) {
                $authHeader = (string) Tools::getValue('auth_header');
                $merchantKey = (string) Tools::getValue('merchant_key');
                $merchantReference = (string) Tools::getValue('merchant_reference');
                $personalToken = (string) Tools::getValue('personnal_token');
                if ( empty($authHeader) || empty($merchantKey) || empty($merchantReference) || empty($personalToken)  || !Validate::isGenericName($merchantKey) || !Validate::isGenericName($merchantReference)) {
                    $output = $this->displayError($this->l('Invalid entries value'));
                } else {
                    Configuration::updateValue('auth_header', $authHeader);
                    Configuration::updateValue('merchant_key', $merchantKey);
                    Configuration::updateValue('merchant_reference', $merchantReference);
                    Configuration::updateValue('personnal_token', $personalToken);
                    $output = $this->displayConfirmation($this->l('Settings updated'));
                }
            }
            return $output . $this->displayForm();
        }
    
        public function displayForm()
        {
            $form = [
                'form' => [
                    'legend' => [
                        'title' => $this->l('Configuration'),
                    ],
                    'input' => [
                        [
                            'type' => 'text',
                            'label' => $this->l('Auth Header'),
                            'name' => 'auth_header',
                            'size' => 90,
                            'required' => true,
                        ],
                        [
                            'type' => 'text',
                            'label' => $this->l('Merchant key'),
                            'name' => 'merchant_key',
                            'size' => 20,
                            'required' => true,
                        ],
                        [
                            'type' => 'text',
                            'label' => $this->l('Merchant Reference'),
                            'name' => 'merchant_reference',
                            'size' => 15,
                            'required' => true,
                        ],
                        [
                            'type' => 'text',
                            'label' => $this->l('Personnal Token'),
                            'name' => 'personnal_token',
                            'size' => 15,
                            'required' => true,
                        ],
                    ],
                    'submit' => [
                        'title' => $this->l('Save'),
                        'class' => 'btn btn-default pull-right',
                    ],
                ],
            ];
    
            $helper = new HelperForm();
            $helper->table = $this->table;
            $helper->name_controller = $this->name;
            $helper->token = Tools::getAdminTokenLite('AdminModules');
            $helper->currentIndex = AdminController::$currentIndex . '&' . http_build_query(['configure' => $this->name]);
            $helper->submit_action = 'submit' . $this->name;
            $helper->default_form_language = (int) Configuration::get('PS_LANG_DEFAULT');
            $helper->fields_value['auth_header'] = Tools::getValue('auth_header', Configuration::get('auth_header'));
            $helper->fields_value['merchant_key'] = Tools::getValue('merchant_key', Configuration::get('merchant_key'));
            $helper->fields_value['merchant_reference'] = Tools::getValue('merchant_reference', Configuration::get('merchant_reference'));
            $helper->fields_value['personnal_token'] = Tools::getValue('personnal_token', Configuration::get('personnal_token'));
            return $helper->generateForm([$form]);
        }
        
        public function uninstall()
        { 
            $sqlDelete = "DROP TABLE `"._DB_PREFIX_."prestom_payment_track`";
            if(!$result=Db::getInstance()->Execute($sqlDelete))
                return false;
            if (parent::uninstall() == false) {
                return false;
            }
            return true;
        }

        public function hookPaymentOptions($params)
        {
            if (!$this->active) {
                return;
            }
            if (!$this->checkCurrency($params['cart'])) {
                return;
            }
            $payment_options = [
                $this->getOmPaymentOption(),
            ];
            return $payment_options;
        }

        public function checkCurrency($cart)
        {
            $currency_order = new Currency($cart->id_currency);
            $currencies_module = $this->getCurrency($cart->id_currency);
            if (is_array($currencies_module)) {
                foreach ($currencies_module as $currency_module) {
                    if ($currency_order->id == $currency_module['id_currency']) {
                        return true;
                    }
                }
            }
            return false;
        }

        public function getOmPaymentOption()
        {
            $omOption = new \PrestaShop\PrestaShop\Core\Payment\PaymentOption();
            $omOption->setCallToActionText($this->l('Pay with Orange Money'))
                        ->setAction($this->context->link->getModuleLink($this->name, 'tokencontroller', array(), true))
                        ->setAdditionalInformation($this->context->smarty->fetch('module:prestom/views/templates/front/payment_infos.tpl'));
            return $omOption;
        }
    }
