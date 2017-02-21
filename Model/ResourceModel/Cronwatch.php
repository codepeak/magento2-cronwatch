<?php

namespace Codepeak\Cronwatch\Model\ResourceModel;

/**
 * Class Cronwatch
 *
 * @package Codepeak\Cronwatch\Model\ResourceModel
 * @author  Robert Lord <robert@codepeak.se>
 */
class Cronwatch extends \Magento\Framework\Model\ResourceModel\Db\AbstractDb
{
    /**
     * Define resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('codepeak_cronwatch', 'cronwatch_id');
    }
}
