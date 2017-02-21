<?php

namespace Codepeak\Cronwatch\Model\ResourceModel\Cronwatch;

/**
 * Class Collection
 *
 * @package Codepeak\Cronwatch\Model\ResourceModel\Cronwatch
 * @author  Robert Lord <robert@codepeak.se>
 */
class Collection extends \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection
{
    /**
     * Define resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init(
            'Codepeak\Cronwatch\Model\Cronwatch',
            'Codepeak\Cronwatch\Model\ResourceModel\Cronwatch'
        );
    }
}
