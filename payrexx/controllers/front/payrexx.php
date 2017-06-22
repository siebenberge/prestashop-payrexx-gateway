<?php

class PayrexxPayrexxModuleFrontController extends ModuleFrontController
{
    public $name = 'payrexx';

    public function postProcess()
    {
        $context = Context::getContext();
        $cart = $context->cart;
        $customer = $context->customer;
        $address = new Address($cart->id_address_delivery);

        $total = (float)($cart->getOrderTotal(true, Cart::BOTH));
        $currency = $context->currency->iso_code;

        $successRedirectUrl = Context::getContext()->link->getModuleLink($this->name, 'validation', array(), true);
        $failedRedirectUrl = Tools::getShopDomain(true, true).'/index.php?controller=order&step=1';

        spl_autoload_register(function ($class) {
            $root = __DIR__ . '/payrexx-php-master';
            $classFile = $root . '/lib/' . str_replace('\\', '/', $class) . '.php';
            if (file_exists($classFile)) {
                require_once $classFile;
            }
        });

        $instanceName = Configuration::get('PAYREXX_INSTANCE_NAME');
        $secret = Configuration::get('PAYREXX_API_SECRET');
        $payrexx = new \Payrexx\Payrexx($instanceName, $secret);
        $gateway = new \Payrexx\Models\Request\Gateway();

        $gateway->setAmount($total * 100);

        if ($currency == "") {
            $currency = "USD";
        }
        $gateway->setCurrency($currency);

        $gateway->setSuccessRedirectUrl($successRedirectUrl);
        $gateway->setFailedRedirectUrl($failedRedirectUrl);
        $gateway->setPsp([]);

        $gateway->setReferenceId($cart->id);

        $gateway->addField($type = 'title', $value = '');
        $gateway->addField($type = 'forename', $value = $customer->firstname);
        $gateway->addField($type = 'surname', $value = $customer->lastname);
        $gateway->addField($type = 'company', $value = $customer->company);
        $gateway->addField($type = 'street', $value = $address->address1);
        $gateway->addField($type = 'postcode', $value = $address->postcode);
        $gateway->addField($type = 'place', $value = $address->city);
        $gateway->addField($type = 'country', $value = $address->country);
        $gateway->addField($type = 'phone', $value = $address->phone);
        $gateway->addField($type = 'email', $value = $customer->email);
        $gateway->addField($type = 'custom_field_1', $value = $cart->id, $name = 'Prestashop ID');

        try {
            $response = $payrexx->create($gateway);
            $context->cookie->paymentId = $response->getId();
            $lang = Language::getIsoById($context->cookie->id_lang);
            Tools::redirect('https://' . $instanceName . '.payrexx.com/' . $lang . '/?payment=' . $response->getHash());
        } catch (\Payrexx\PayrexxException $e) {
            Tools::redirect('index.php?controller=order&step=1');
        }
    }
}
