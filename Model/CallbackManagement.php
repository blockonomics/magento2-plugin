<?php


namespace Blockonomics\Payment\Model;

class CallbackManagement
{

    /**
     * {@inheritdoc}
     */
    public function postCallback($param)
    {
        return 'hello api POST return the $param ' . $param;
    }
}
