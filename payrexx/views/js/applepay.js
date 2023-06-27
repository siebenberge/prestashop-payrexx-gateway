/**
 * Payrexx Payment Gateway.
 *
 * @author    Payrexx <integration@payrexx.com>
 * @copyright 2023 Payrexx
 * @license   MIT License
 */
$(document).on('ready', function() {
    if ((window.ApplePaySession && ApplePaySession.canMakePayments()) !== true) {
        var $containerId = $('input[name=payrexxPaymentMethod][value=apple-pay]')
            .parent('form')
            .parent('.js-payment-option-form')
            .attr('id').match(/\d+/);
        if ($containerId > 0) {
            jQuery('#payment-option-' + $containerId + '-container').remove();
        }
        console.warn("Payrexx Apple Pay is not supported on this device/browser");
    }
});