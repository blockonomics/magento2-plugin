<?php
/**
 * Blockonomics interface for bitcoin transactions
 *
 * @category    Blockonomics
 * @package     Blockonomics_Merchant
 * @author      Blockonomics
 * @copyright   Blockonomics (https://blockonomics.co)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

namespace Blockonomics\Merchant\Api\Data;

interface BitcoinTransactionInterface
{
    const ID            = 'id';
    const ID_ORDER      = 'id_order';
    const TIMESTAMP     = 'timestamp';
    const ADDR          = 'addr';
    const TXID          = 'txid';
    const STATUS        = 'status';
    const VALUE         = 'value';
    const BITS          = 'bits';
    const BITS_PAYED    = 'bits_payed';

    public function getId();
    public function getIdOrder();
    public function getTimestamp();
    public function getAddr();
    public function getTxId();
    public function getStatus();
    public function getValue();
    public function getBits();
    public function getBitsPayed();

    public function setId($id);
    public function setIdOrder($order_id);
    public function setTimestamp($timestamp);
    public function setAddr($addr);
    public function setTxId($txid);
    public function setStatus($status);
    public function setValue($value);
    public function setBits($bits);
    public function setBitsPayed($bits_payed);
}
