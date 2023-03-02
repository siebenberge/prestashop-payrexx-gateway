<?php
/**
 * Do not edit or add to this file if you wish to upgrade PrestaShop to newer
 * versions in the future. If you wish to customize PrestaShop for your
 * needs please refer to http://www.prestashop.com for more information.
 *
 * @author    Payrexx <integration@payrexx.com>
 * @copyright 2023 Payrexx
 * @license   MIT License
 */
namespace Payrexx\PayrexxPaymentGateway\Service;

use Configuration;
use Payrexx\Models\Response\Gateway;
use Payrexx\Models\Response\Transaction;
use Payrexx\Models\Request\SignatureCheck;

class PayrexxApiService
{
    private $instanceName;
    private $apiKey;
    private $platform;

    public function __construct()
    {
        $this->instanceName = Configuration::get('PAYREXX_INSTANCE_NAME');
        $this->apiKey = Configuration::get('PAYREXX_API_SECRET');
        $this->platform = Configuration::get('PAYREXX_PLATFORM');
    }

    /**
     *
     * @param int $gatewayId
     *
     * @return \Payrexx\Models\Response\Gateway|null
     */
    public function getPayrexxGateway($gatewayId): ?Gateway
    {
        if (!$gatewayId) {
            return null;
        }

        $payrexx = $this->getInterface($this->instanceName, $this->apiKey, $this->platform);
        $gateway = new \Payrexx\Models\Request\Gateway();
        $gateway->setId($gatewayId);

        try {
            return $payrexx->getOne($gateway);
        } catch (\Payrexx\PayrexxException $e) {
        }
        return null;
    }

    public function getTransactionByGateway($payrexxGateway): ?\Payrexx\Models\Response\Transaction
    {
        if (!in_array($payrexxGateway->getStatus(), [Transaction::CONFIRMED, Transaction::WAITING])) {
            return null;
        }
        $invoices = $payrexxGateway->getInvoices();

        if (!$invoices || !$invoice = end($invoices)) {
            return null;
        }

        if (!$transactions = $invoice['transactions']) {
            return null;
        }

        return $this->getPayrexxTransaction(end($transactions)['id']);
    }

    /**
     * @param int $transactionId
     * @return \Payrexx\Models\Request\Transaction|NULL
     */
    public function getPayrexxTransaction($transactionId): ?Transaction
    {
        if (!$transactionId) {
            return null;
        }

        $payrexx = $this->getInterface($this->instanceName, $this->apiKey, $this->platform);
        $transaction = new \Payrexx\Models\Request\Transaction();
        $transaction->setId($transactionId);

        try {
            return $payrexx->getOne($transaction);
        } catch (\Payrexx\PayrexxException $e) {
            return null;
        }
    }

    public function createPayrexxGateway(
        string $purpose,
        float $total,
        string $currency,
        array $redirectUrls,
        $cart,
        $customer,
        $address,
        string $country,
        array $pm
    ): ?Gateway
    {
        $basket = [];
        $basketAmount = 0;
        foreach ($cart->getProducts() as $product) {
            $productPrice = round($product['price_wt'] * 100, 0);
            $basket[] = [
                'name' => $product['name'],
                'description' => $product['description_short'],
                'quantity' => $product['quantity'],
                'amount' => $productPrice,
                'sku' => $product['reference'],
            ];
            $basketAmount += $productPrice * $product['quantity'];
        }
        if ($cart->getPackageShippingCost()) {
            $shippingAmount = round($cart->getPackageShippingCost() * 100, 0);
            $basket[] = [
                'name' => 'Shipping',
                'amount' => $shippingAmount,
            ];
            $basketAmount += $shippingAmount;
        }

        if ($cart->getDiscountSubtotalWithoutGifts()) {
            $discountAmount = round($cart->getDiscountSubtotalWithoutGifts() * 100, 0);

            $basket[] = [
                'name' => 'Discount',
                'amount' => -$discountAmount,
            ];
            $basketAmount -= $discountAmount;
        }

        $payrexx = $this->getInterface($this->instanceName, $this->apiKey, $this->platform);

        $gateway = new \Payrexx\Models\Request\Gateway();

        // Fallback for basket feature
        if ((int) $basketAmount === (int) ($total * 100)) {
            $gateway->setBasket($basket);
        } else {
            $gateway->setPurpose($purpose);
        }

        $gateway->setAmount($total * 100);
        $gateway->setVatRate($cart->getAverageProductsTaxRate() * 100);
        $gateway->setCurrency($currency);
        $gateway->setSuccessRedirectUrl($redirectUrls['success']);
        $gateway->setCancelRedirectUrl($redirectUrls['cancel']);
        $gateway->setFailedRedirectUrl($redirectUrls['failed']);
        $gateway->setPsp([]);
        $gateway->setPm($pm);
        $gateway->setReferenceId($cart->id);
        $gateway->setSkipResultPage(true);

        if (Configuration::get('PAYREXX_LOOK_AND_FEEL_ID')) {
            $gateway->setLookAndFeelProfile(Configuration::get('PAYREXX_LOOK_AND_FEEL_ID'));
        }

        $gateway->addField('title', '');
        $gateway->addField('forename', $customer->firstname);
        $gateway->addField('surname', $customer->lastname);
        $gateway->addField('company', $customer->company);
        $gateway->addField('street', $address->address1);
        $gateway->addField('postcode', $address->postcode);
        $gateway->addField('place', $address->city);
        $gateway->addField('country', $country);
        $gateway->addField('phone', $address->phone);
        $gateway->addField('email', $customer->email);
        $gateway->addField('custom_field_1', $cart->id, 'Prestashop ID');

        try {
            return $payrexx->create($gateway);
        } catch (\Payrexx\PayrexxException $e) {
        }
        return null;
    }

    public function deletePayrexxGateway($gatewayId)
    {
        $payrexx = $this->getInterface($this->instanceName, $this->apiKey, $this->platform);

        $gateway = new \Payrexx\Models\Request\Gateway();
        $gateway->setId($gatewayId);

        try {
            $payrexx->delete($gateway);
        } catch (\Payrexx\PayrexxException $e) {
        }
    }

    /**
     * Get payrexx object
     *
     * @param string $instance
     * @param string $apiKey
     * @param string $platform
     * @return Payrexx
     */
    private function getInterface(string $instance, string $apiKey, string $platform): \Payrexx\Payrexx
    {
        return new \Payrexx\Payrexx($instance, $apiKey, '', $platform);
    }

    /**
     * validate the api signature
     *
     * @param string $instance
     * @param string $apiKey
     * @param string $platform
     * @return true|false
     */
    public function validateSignature(string $instance, string $apiKey, string $platform): bool
    {
        $payrexx = $this->getInterface($instance, $apiKey, $platform);
        try {
            $payrexx->getOne(new SignatureCheck());
            return true;
        } catch (\Payrexx\PayrexxException $e) {
            return false;
        }
    }
}
