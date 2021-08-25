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

require_once _PS_MODULE_DIR_ . '/payrexx/Service/PayrexxApiService.php';
class PayrexxPayrexxModuleFrontController extends ModuleFrontController
{
    const MODULE_NAME = 'payrexx';

    private $supportedLang = ['nl', 'fr', 'de', 'it', 'nl', 'pt', 'tr', 'pl', 'es', 'dk'];
    private $defaultLang = 'en';

    public function postProcess()
    {
        try {
            // Collect Gateway data
            $payrexxApiService = new \PayrexxPaymentGateway\Service\PayrexxApiService(Configuration::get('PAYREXX_INSTANCE_NAME'), Configuration::get('PAYREXX_API_SECRET'), Configuration::get('PAYREXX_PLATFORM'));
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

            $total = (float)($cart->getOrderTotal(true, Cart::BOTH));
            $currency = $context->currency->iso_code;

            $successRedirectUrl = Context::getContext()->link
                ->getModuleLink(self::MODULE_NAME, 'validation', array(), true);
            $failedRedirectUrl = Tools::getShopDomain(true, true) . '/index.php?controller=order&step=1';
            $currencyIsoCode = !empty($currency) ? $currency : 'USD';
            $country = Country::getIsoById($address->id_country);

            $purpose = implode(', ', $productNames);

            if ($gatewayId = static::getGatewayIdByForCartId($cart->id)) {
                $payrexxApiService->deletePayrexxGateway($gatewayId);
            }

            $gateway = $payrexxApiService->createPayrexxGateway($purpose, $total, $currencyIsoCode, $successRedirectUrl, $failedRedirectUrl, $cart, $customer, $address, $country);

            $context->cookie->paymentId = $gateway->getId();
            static::insertCartGatewayId($cart->id, $gateway->getId());
            $lang = Language::getIsoById($context->cookie->id_lang);

            if (!in_array($lang, $this->supportedLang)) {
                $lang = $this->defaultLang;
            }

            $link = $gateway->getLink();
            $gatewayUrl = str_replace('?', $lang . '/?', $link);

            Tools::redirect($gatewayUrl);
        } catch (\Payrexx\PayrexxException $e) {
            Tools::redirect('index.php?controller=order&step=1');
        }
    }

    /**
     *
     * @param int $id_cart cart id
     * @param int $id_gateway gateway id
     * @return boolean
     */
    private static function insertCartGatewayId($id_cart, $id_gateway)
    {
        if (empty($id_cart) || empty($id_gateway)) {
            return false;
        }

        return Db::getInstance(_PS_USE_SQL_SLAVE_)->execute('
            INSERT INTO `' . _DB_PREFIX_ . 'payrexx_gateway` (`id_cart`, `id_gateway`)
            VALUES (' . (int)$id_cart . ',' . (int)$id_gateway . ')'
            . 'ON DUPLICATE KEY UPDATE id_gateway = ' . (int)$id_gateway . '
        ');
    }

    /**
     *
     * @param int $id_cart cart id
     * @return int
     */
    public static function getGatewayIdByForCartId($id_cart)
    {
        if (empty($id_cart)) {
            return null;
        }

        return (int)Db::getInstance(_PS_USE_SQL_SLAVE_)->getValue('
            SELECT id_gateway FROM `' . _DB_PREFIX_ . 'payrexx_gateway`
            WHERE id_cart = ' . (int)$id_cart);
    }
}
