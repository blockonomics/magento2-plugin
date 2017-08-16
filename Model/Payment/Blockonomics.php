<?php


namespace Blockonomics\Payment\Model\Payment;

class Blockonomics extends \Magento\Payment\Model\Method\AbstractMethod
{

    protected $_code = "blockonomics";
    protected $_isOffline = true;

    public function isAvailable(
        \Magento\Quote\Api\Data\CartInterface $quote = null
    ) {
        return parent::isAvailable($quote);
    }
}
