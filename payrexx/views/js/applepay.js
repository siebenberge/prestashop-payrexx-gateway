/**
 * Payrexx Payment Gateway.
 *
 * @author    Payrexx <integration@payrexx.com>
 * @copyright 2026 Payrexx
 * @license   MIT License
 */
$(document).on('ready', function() {
    if ((window.ApplePaySession && ApplePaySession.canMakePayments()) !== true) {
        const $containerId = $('input[name="payrexxPaymentMethod"][value="apple-pay"]')
            .closest('.js-payment-option-form')
            .attr('id')
            ?.match(/\d+/)?.[0];

        if ($containerId) {
            $(`#payment-option-${$containerId}-container`).remove();
            console.warn('Payrexx Apple Pay is not supported on this device/browser');
        }
    }
});