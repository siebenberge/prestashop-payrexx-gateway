/**
 * Payrexx Payment Gateway.
 *
 * @author    Payrexx <integration@payrexx.com>
 * @copyright 2023 Payrexx
 * @license   MIT License
 */
$(document).on('ready', function() {
    try {
        const baseRequest = {
            apiVersion: 2,
            apiVersionMinor: 0
        };
        const allowedCardNetworks = ['MASTERCARD', 'VISA'];
        const allowedCardAuthMethods = ['CRYPTOGRAM_3DS'];
        const baseCardPaymentMethod = {
            type: 'CARD',
            parameters: {
                allowedAuthMethods: allowedCardAuthMethods,
                allowedCardNetworks: allowedCardNetworks
            }
        };

        const isReadyToPayRequest = Object.assign({}, baseRequest);
        isReadyToPayRequest.allowedPaymentMethods = [
            baseCardPaymentMethod
        ];
        const paymentsClient = new google.payments.api.PaymentsClient(
            {
                environment: 'TEST'
            }
        );
        paymentsClient.isReadyToPay(isReadyToPayRequest).then(function(response) {
            if (!response.result) {
                var $containerId = $('input[name=payrexxPaymentMethod][value=google-pay]')
                    .parent('form')
                    .parent('.js-payment-option-form')
                    .attr('id').match(/\d+/);
                if ($containerId > 0) {
                    jQuery('#payment-option-' + $containerId + '-container').remove();
                    console.warn("Payrexx Google Pay is not supported on this device/browser");
                }
            }
        }).catch(function(err) {
            console.log(err);
        });
    } catch (err) {
        console.log(err);
    }
});
