<?php

namespace Payrexx\PayrexxPaymentGateway\Helper;

class PayrexxHelper
{
    /**
     * Get payrexx platforms.
     *
     * @return array
     */
    public static function getPlatforms()
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
    public static function getConfigKeys()
    {
        return [
            'PAYREXX_PLATFORM',
            'PAYREXX_API_SECRET',
            'PAYREXX_INSTANCE_NAME',
            'PAYREXX_PAY_ICONS',
            'PAYREXX_LOOK_AND_FEEL_ID',
            'PAYREXX_STATUS',
        ];
    }
}
