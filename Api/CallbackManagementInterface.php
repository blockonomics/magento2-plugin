<?php


namespace Blockonomics\Payment\Api;

interface CallbackManagementInterface
{


    /**
     * POST for callback api
     * @param string $param
     * @return string
     */
    
    public function postCallback($param);
}
