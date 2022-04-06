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
    private $supportedLang = ['nl', 'fr', 'de', 'it', 'nl', 'pt', 'tr', 'pl', 'es', 'dk'];
    private $defaultLang = 'en';

    public function postProcess()
    {
        try {
            // Collect Gateway data
            $payrexxDbService = $this->get('payrexx.payrexxpaymentgateway.payrexxdbservice');
            $payrexxApiService = $this->get('payrexx.payrexxpaymentgateway.payrexxapiservice');
            $context = Context::getContext();

            $cart = $context->cart;
            $productNames = array();
            $products = $cart->getProducts();
            foreach ($products as $product) {
                $quantity = $product['cart_quantity'] > 1 ? $product['cart_quantity'] . 'x ' : '';
                $productNames[] = $quantity . $product['name'];
            }

            $customer = $context->customer;
            $address = new Address($cart->id_address_delivery);
            $country = Country::getIsoById($address->id_country);

            $total = (float)($cart->getOrderTotal(true, Cart::BOTH));
            $currency = $context->currency->iso_code;

            $successRedirectUrl = $context->link->getModuleLink($this->module->name, 'validation', [], true);
            $cancelRedirectUrl = $context->link->getModuleLink($this->module->name, 'validation', ['payrexxError' => 'cancel'], true);
            $failedRedirectUrl = $context->link->getModuleLink($this->module->name, 'validation', ['payrexxError' => 'fail'], true);
            $currencyIsoCode = !empty($currency) ? $currency : 'USD';

            $purpose = implode(', ', $productNames);

            if ($gatewayId = $payrexxDbService->getCartGatewayId($cart->id)) {
                $payrexxApiService->deletePayrexxGateway($gatewayId);
            }

            $gateway = $payrexxApiService->createPayrexxGateway(
                $purpose,
                $total,
                $currencyIsoCode,
                $successRedirectUrl,
                $cancelRedirectUrl,
                $failedRedirectUrl,
                $cart,
                $customer,
                $address,
                $country
            );

            $context->cookie->paymentId = $gateway->getId();
            $payrexxDbService->insertCartGatewayId($cart->id, $gateway->getId());
            $lang = Language::getIsoById($context->cookie->id_lang);

            if (!in_array($lang, $this->supportedLang)) {
                $lang = $this->defaultLang;
            }

            $link = $gateway->getLink();
            $gatewayUrl = str_replace('?', $lang . '/?', $link);

            Tools::redirect($gatewayUrl);
        } catch (\Payrexx\PayrexxException $e) {
            Tools::redirect(Context::getContext()->link->getModuleLink(self::MODULE_NAME, 'validation', ['payrexxError' => PayrexxValidationModuleFrontController::ERROR_CONFIG], true));
        }
    }
}
