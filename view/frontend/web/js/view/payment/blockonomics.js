define(
    [
        'uiComponent',
        'Magento_Checkout/js/model/payment/renderer-list'
    ],
    function (
        Component,
        rendererList
    ) {
        'use strict';
        rendererList.push(
            {
                type: 'blockonomics',
                component: 'Blockonomics_Payment/js/view/payment/method-renderer/blockonomics-method'
            }
        );
        return Component.extend({});
    }
);