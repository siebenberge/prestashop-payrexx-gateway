<?php

namespace Payrexx\PayrexxPaymentGateway\Util;

class ConfigurationUtil
{
    /**
     * Get payrexx platforms.
     *
     * @return array
     */
    public static function getPlatforms(): array
    {
        return [
            'payrexx.com' => 'Payrexx',
            'shop-and-pay.com' => 'Shop and Pay',
            'ideal-pay.ch' => 'Ideal Pay',
            'payzzter.com' => 'Payzzter',
        ];
    }

    /**
     * Get configuration keys.
     *
     * @return array
     */
    public static function getConfigKeys(): array
    {
        return [
            'PAYREXX_PLATFORM',
            'PAYREXX_API_SECRET',
            'PAYREXX_INSTANCE_NAME',
            'PAYREXX_PAY_ICONS',
            'PAYREXX_LOOK_AND_FEEL_ID',
        ];
    }

    /**
     * Get payment methods
     *
     * @return array
     */
    public static function getPaymentMethods(): array
    {
        return [
            'masterpass' => 'Masterpass',
            'mastercard' => 'Mastercard',
            'visa' => 'Visa',
            'apple_pay' => 'Apple Pay',
            'maestro' => 'Maestro',
            'jcb' => 'JCB',
            'american_express' => 'American Express',
            'wirpay' => 'WIRpay',
            'paypal' => 'PayPal',
            'bitcoin' => 'Bitcoin',
            'sofort' => 'Sofort Ueberweisung',
            'airplus' => 'Airplus',
            'billpay' => 'Billpay',
            'bonuscard' => 'Bonus card',
            'cashu' => 'CashU',
            'cb' => 'Carte Bleue',
            'diners_club' => 'Diners Club',
            'sepa_direct_debit' => 'Direct Debit',
            'discover' => 'Discover',
            'elv' => 'ELV',
            'ideal' => 'iDEAL',
            'invoice' => 'Invoice',
            'myone' => 'My One',
            'paysafecard' => 'Paysafe Card',
            'post_finance_card' => 'PostFinance Card',
            'post_finance_e_finance' => 'PostFinance E-Finance',
            'swissbilling' => 'SwissBilling',
            'twint' => 'TWINT',
            'barzahlen' => 'Barzahlen/Viacash',
            'bancontact' => 'Bancontact',
            'giropay' => 'GiroPay',
            'eps' => 'EPS',
            'google_pay' => 'Google Pay',
            'wechat_pay' => 'WeChat Pay',
            'alipay' => 'Alipay',
        ];
    }
}
