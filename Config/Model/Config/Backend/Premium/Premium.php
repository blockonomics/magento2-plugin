<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

/**
 * System config email field backend model
 */
namespace Blockonomics\Merchant\Config\Model\Config\Backend\Premium;

use \Magento\Framework\Exception\LocalizedException;
use \Magento\Framework\App\Config\Value;

class Premium extends Value
{

    public function beforeSave()
    {
        $value = $this->getValue();

        if ($value != 0)
        {
            if (!filter_var($value, FILTER_VALIDATE_INT)) {
                throw new LocalizedException(__('Please enter a whole number between -30 and 30 for premium adjustment'));
            }
        }

        if ($value < -30 || $value > 30) {
            throw new LocalizedException(__('Premium adjustment must be between -30% and +30%'));
        }
        return $this;
    }
}
