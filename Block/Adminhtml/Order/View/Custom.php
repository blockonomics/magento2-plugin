<?php
/**
 * Blockonomics block for custom order field in admin view
 *
 * @category    Blockonomics
 * @package     Blockonomics_Merchant
 * @author      Blockonomics
 * @copyright   Blockonomics (https://blockonomics.co)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

namespace Blockonomics\Merchant\Block\Adminhtml\Order\View;

use Magento\Framework\View\Element\Template;
use Magento\Framework\View\Element\Template\Context;
use Blockonomics\Merchant\Model\BitcoinTransaction;
use Blockonomics\Merchant\Model\ResourceModel\BitcoinTransaction\Collection;

class Custom extends Template
{
    protected $transactionCollection;

    public function __construct(
        Context $context,
        Collection $transactionCollection
    ) {
        parent::__construct($context);
        $this->transactionCollection = $transactionCollection;
    }

    public function getOrderPaymentAmounts()
    {
        $order_id = $this->getRequest()->getParam('order_id');
        $collection = $this->transactionCollection->addFieldToFilter('id_order', $order_id);
        $expected = '';
        $paid = '';

        foreach ($collection as $item) {
            $expected = $item->getBits();
            $paid = $item->getBitsPayed();
        }

        if ($expected && !$paid) {
            $paid = 'No payment made';
        }

        if ($expected || $paid) {
            return ['expected'=>$expected, 'paid'=>$paid];
        }

        return null;
    }

    public function getOrderTxId()
    {
        $order_id = $this->getRequest()->getParam('order_id');
        $collection = $this->transactionCollection->addFieldToFilter('id_order', $order_id);

        $txId = '';

        foreach ($collection as $item) {
            $txId = $item->getTxId();
        }
        
        return $txId;
    }
    
    public function getOrderAddr()
    {
        $order_id = $this->getRequest()->getParam('order_id');
        $collection = $this->transactionCollection->addFieldToFilter('id_order', $order_id);
        
        $addr = '';

        foreach ($collection as $item) {
            $addr = $item->getAddr();
        }
        
        return $addr;
    }
}
