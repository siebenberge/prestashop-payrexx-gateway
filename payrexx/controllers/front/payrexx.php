<?php
/**
 * Payrexx FrontController
 *
 * @author Payrexx <integration@payrexx.com>
 * @copyright  2023 Payrexx
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
            $productNames = [];
            $products = $cart->getProducts();
            foreach ($products as $product) {
                $quantity = $product['cart_quantity'] > 1 ? $product['cart_quantity'] . 'x ' : '';
                $productNames[] = $quantity . $product['name'];
            }

            $customer = $context->customer;
            $address = new Address($cart->id_address_delivery);
            $country = Country::getIsoById($address->id_country);

            $total = (float) $cart->getOrderTotal(true, Cart::BOTH);
            $currency = $context->currency->iso_code;

            $redirectUrls = [
                'success' => $context->link->getModuleLink($this->module->name, 'validation', [], true),
                'cancel' => $context->link->getModuleLink($this->module->name, 'validation', ['payrexxError' => 'cancel'], true),
                'failed' => $context->link->getModuleLink($this->module->name, 'validation', ['payrexxError' => 'fail'], true),
            ];
            $currencyIsoCode = !empty($currency) ? $currency : 'USD';

            $purpose = implode(', ', $productNames);

            if ($gatewayId = $payrexxDbService->getCartGatewayId($cart->id)) {
                $payrexxApiService->deletePayrexxGateway($gatewayId);
            }
            $paymentMethod = Tools::getValue('payrexxPaymentMethod');
            $pm = ($paymentMethod != 'payrexx') ? [$paymentMethod] : [];
            $gateway = $payrexxApiService->createPayrexxGateway(
                $purpose,
                $total,
                $currencyIsoCode,
                $redirectUrls,
                $cart,
                $customer,
                $address,
                $country,
                $pm
            );

            if (!$gateway) {
                throw new \Payrexx\PayrexxException();
            }
            $context->cookie->paymentId = $gateway->getId();
            $payrexxDbService->insertGatewayInfo(
                $cart->id,
                $gateway->getId(),
                $paymentMethod
            );
            $lang = Language::getIsoById($context->cookie->id_lang);

            if (!in_array($lang, $this->supportedLang)) {
                $lang = $this->defaultLang;
            }

            $link = $gateway->getLink();
            $gatewayUrl = str_replace('?', $lang . '/?', $link);

            Tools::redirect($gatewayUrl);
        } catch (\Payrexx\PayrexxException $e) {
            Tools::redirect(
                Context::getContext()->link->getModuleLink(
                    $this->module->name,
                    'validation',
                    [
                        'payrexxError' => 'config',
                    ],
                    true,
                )
            );
        }
    }
}
