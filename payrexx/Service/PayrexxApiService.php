<?php
/**
 * Do not edit or add to this file if you wish to upgrade PrestaShop to newer
 * versions in the future. If you wish to customize PrestaShop for your
 * needs please refer to http://www.prestashop.com for more information.
 *
 * @author Payrexx <integration@payrexx.com>
 * @copyright  2019 Payrexx
 * @license MIT License
 */

namespace PayrexxPaymentGateway\Service;

use Payrexx\Models\Response\Gateway;
use Payrexx\Models\Response\Transaction;

class PayrexxApiService
{

    private $instanceName;
    private $apiKey;
    private $platform;

    public function __construct($instanceName, $apiKey, $platform = '')
    {
        $this->instanceName = $instanceName;
        $this->apiKey = $apiKey;
        $this->platform = $platform;
    }

    /**
     *
     * @param int $gatewayId
     * @return \Payrexx\Models\Response\Gateway|null
     */
    public function getPayrexxGateway($gatewayId): ?Gateway
    {
        if (!$gatewayId) {
            return null;
        }

        $payrexx = $this->getInterface();
        $gateway = new \Payrexx\Models\Request\Gateway();
        $gateway->setId($gatewayId);

        try {
            return $payrexx->getOne($gateway);
        } catch (\Payrexx\PayrexxException $e) {
        }
        return null;
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

        $payrexx = $this->getInterface();
        $transaction = new \Payrexx\Models\Request\Transaction();
        $transaction->setId($transactionId);

        try {
            return $payrexx->getOne($transaction);
        } catch (\Payrexx\PayrexxException $e) {
            return null;
        }
    }

    public function createPayrexxGateway(string $purpose, float $amount, string $currency, string $successRedirectUrl, string $failedRedirectUrl, $cart, $customer, $address, string $country): ?Gateway
    {

        $payrexx = $this->getInterface();

        $gateway = new \Payrexx\Models\Request\Gateway();
        $gateway->setPurpose($purpose);
        $gateway->setAmount($amount * 100);
        $gateway->setCurrency($currency);
        $gateway->setSuccessRedirectUrl($successRedirectUrl);
        $gateway->setCancelRedirectUrl($failedRedirectUrl);
        $gateway->setFailedRedirectUrl($failedRedirectUrl);
        $gateway->setPsp([]);
        $gateway->setReferenceId($cart->id);
        $gateway->setSkipResultPage(true);

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
        $payrexx = $this->getInterface();

        $gateway = new \Payrexx\Models\Request\Gateway();
        $gateway->setId($gatewayId);

        try {
            $payrexx->delete($gateway);
        } catch (\Payrexx\PayrexxException $e) {
        }
    }

    private function getInterface(): \Payrexx\Payrexx
    {
        spl_autoload_register(function ($class) {
            $root = _PS_MODULE_DIR_ . '/payrexx/controllers/front/payrexx-php-master';
            $classFile = $root . '/lib/' . str_replace('\\', '/', $class) . '.php';
            if (file_exists($classFile)) {
                require_once $classFile;
            }
        });
        return new \Payrexx\Payrexx($this->instanceName, $this->apiKey, '', $this->platform);
    }
}
