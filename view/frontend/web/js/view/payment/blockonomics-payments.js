/**
 * Blockonomics payment method model
 *
 * @category    Blockonomics
 * @package     Blockonomics_Merchant
 * @author      Blockonomics
 * @copyright   Blockonomics (https://blockonomics.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
/*browser:true*/
/*global define*/
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
                type: 'blockonomics_merchant',
                component: 'Blockonomics_Merchant/js/view/payment/method-renderer/blockonomics-method'
            }
        );
        /** Add view logic here if needed */
        return Component.extend({});
    }
);
