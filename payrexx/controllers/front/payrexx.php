<?php
/**
 * Do not edit or add to this file if you wish to upgrade PrestaShop to newer
 * versions in the future. If you wish to customize PrestaShop for your
 * needs please refer to http://www.prestashop.com for more information.
 *
 * @author Payrexx <support@payrexx.com>
 * @copyright  2017 Payrexx
 * @license MIT License
 */

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
        $gateway->setPsp(array());

        $gateway->setReferenceId($cart->id);

        $gateway->addField('title', '');
        $gateway->addField('forename', $customer->firstname);
        $gateway->addField('surname', $customer->lastname);
        $gateway->addField('company', $customer->company);
        $gateway->addField('street', $address->address1);
        $gateway->addField('postcode', $address->postcode);
        $gateway->addField('place', $address->city);
        $gateway->addField('country', $address->country);
        $gateway->addField('phone', $address->phone);
        $gateway->addField('email', $customer->email);
        $gateway->addField('custom_field_1', $cart->id, 'Prestashop ID');

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
