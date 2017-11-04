# magento2-plugin

Install
=======

1. Go to Magento2 root folder

2. Enter following commands to install module:

    ```bash
    composer config repositories.blockonomicsmerchant git https://github.com/jusasiiv/magento2-plugin.git
    composer require blockonomics/magento2-plugin:dev-master
    ```
   Wait while dependencies are updated.
   
3. Enter following commands to enable module:

    ```bash
    php bin/magento module:enable Blockonomics_Merchant --clear-static-content
    php bin/magento setup:upgrade
    ```
4. Enable and configure Blockonomics in Magento Admin under Stores/Configuration/Payment Methods/blockonomics
