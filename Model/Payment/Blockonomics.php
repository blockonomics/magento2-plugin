<?php
/**
 * A Magento 2 module named Blockonomics/Merchant
 * Copyright (C) 2017  
 * 
 * This file is part of Blockonomics/Merchant.
 * 
 * Blockonomics/Merchant is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 * 
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 * 
 * You should have received a copy of the GNU General Public License
 * along with this program. If not, see <http://www.gnu.org/licenses/>.
 */

namespace Blockonomics\Merchant\Model\Payment;

use Magento\Framework\Model\Context;
use Magento\Payment\Helper\Data;
use Magento\Payment\Model\Method\AbstractMethod;
use Magento\Payment\Model\Method\Logger;
use Magento\Framework\UrlInterface;
use Magento\Sales\Model\Order;
use Magento\Store\Model\StoreManagerInterface;

class Blockonomics extends AbstractMethod
{

    protected $_code = "blockonomics_merchant";
    protected $_isOffline = true;

    protected $urlBuilder;
    protected $storeManager;

    public function __construct(
        Context $context,
        Data $paymentData,
        Logger $logger,
        UrlInterface $urlBuilder,
        StoreManagerInterface $storeManager
    )
    {
        parent::__construct(
            $context,
            $paymentData,
            $logger
        );

        $this->urlBuilder = $urlBuilder;
        $this->storeManager = $storeManager;

				//Include configuration from the local file.
        $BLOCKONOMICS_BASE_URL = 'https://www.blockonomics.co';
        $BLOCKONOMICS_WEBSOCKET_URL = 'wss://www.blockonomics.co';
        $BLOCKONOMICS_NEW_ADDRESS_URL = $BLOCKONOMICS_BASE_URL.'/api/new_address';
        $BLOCKONOMICS_PRICE_URL = $BLOCKONOMICS_BASE_URL.'/api/price?currency=';
    }
}
