<?php
/**
 * Blockonomics bitcoin transaction model
 *
 * @category    Blockonomics
 * @package     Blockonomics_Merchant
 * @author      Blockonomics
 * @copyright   Blockonomics (https://blockonomics.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

namespace Blockonomics\Merchant\Model;

use \Magento\Framework\Model\AbstractModel;
use \Magento\Framework\DataObject\IdentityInterface;
use \Blockonomics\Merchant\Api\Data\BitcoinTransactionInterface;

class BitcoinTransaction extends AbstractModel implements BitcoinTransactionInterface, IdentityInterface
{

    const CACHE_TAG = 'blockonomics_merchant_bitcointransaction';

    /**
     * @return void
     */
    protected function _construct()
    {
        $this->_init('Blockonomics\Merchant\Model\ResourceModel\BitcoinTransaction');
    }

    public function getId()
    {
        return $this->getData(self::ID);
    }

    public function getIdOrder()
    {
        return $this->getData(self::ID_ORDER);
    }

    public function getTimestamp()
    {
        return $this->getData(self::TIMESTAMP);
    }

    public function getAddr()
    {
        return $this->getData(self::ADDR);
    }

    public function getTxId()
    {
        return $this->getData(self::TXID);
    }

    public function getStatus()
    {
        return $this->getData(self::STATUS);
    }

    public function getValue()
    {
        return $this->getData(self::VALUE);
    }

    public function getBits()
    {
        return $this->getData(self::BITS);
    }

    public function getBitsPayed()
    {
        return $this->getData(self::BITS_PAYED);
    }

    public function getIdentities()
    {
        return [self::CACHE_TAG . '_' . $this->getId()];
    }


    public function setId($id)
    {
        return $this->setData(self::ID, $id);
    }

    public function setIdOrder($id_order)
    {
        return $this->setData(self::ID_ORDER, $id_order);
    }

    public function setTimestamp($timestamp)
    {
        return $this->setData(self::TIMESTAMP, $timestamp);
    }

    public function setAddr($addr)
    {
        return $this->setData(self::ADDR, $addr);
    }

    public function setTxId($txid)
    {
        return $this->setData(self::TXID, $txid);
    }

    public function setStatus($status)
    {
        return $this->setData(self::STATUS, $status);
    }

    public function setValue($value)
    {
        return $this->setData(self::VALUE, $value);
    }

    public function setBits($bits)
    {
        return $this->setData(self::BITS, $bits);
    }

    public function setBitsPayed($bits_payed)
    {
        return $this->setData(self::BITS_PAYED, $bits_payed);
    }
}
