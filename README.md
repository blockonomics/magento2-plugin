# Blockonomics Bitcoin Payments #

Accept bitcoin payments on your Magento 2 website with Blockonomics.

## Description ##

- Accept bitcoin payments on your website with ease
- No security risk, payments go directly into your own bitcoin wallet
- All major HD wallets like trezor, blockchain.info, mycelium supported
- No approvals of API key/documentation required
- Supports all major fiat currencies
- Complete checkout process happens within your website

## Installation ##

### From store (Preferred): ###

Coming soon

### From github ###

1. Go to Magento2 root folder

2. Enter following commands to install module:

    ```bash
    composer config repositories.blockonomicsmerchant git https://github.com/blockonomics/magento2-plugin.git
    composer require blockonomics/module-merchant:dev-master
    ```
   Wait while dependencies are updated.
   
3. Enter following commands to enable module:

    ```bash
    php bin/magento module:enable Blockonomics_Merchant --clear-static-content
    php bin/magento setup:upgrade
    ```
4. Enable and configure Blockonomics in Magento Admin under Stores/Configuration/Payment Methods/blockonomics