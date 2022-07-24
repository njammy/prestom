<?php

    class TokenActionController extends ModuleFrontController
    {
        protected $httpClient;
        public function initContent()
        {
            parent::initContent();
            echo "you are in token action !";
        }
    }