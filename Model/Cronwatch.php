<?php

namespace Codepeak\Cronwatch\Model;

use Codepeak\Cronwatch\Api\Data\CronwatchInterface;

/**
 * Class Cronwatch
 *
 * @package Codepeak\Cronwatch\Model
 * @author  Robert Lord <robert@codepeak.se>
 */
class Cronwatch extends \Magento\Framework\Model\AbstractModel implements CronwatchInterface
{
    /**
     * @return void
     */
    protected function _construct()
    {
        $this->_init('Codepeak\Cronwatch\Model\ResourceModel\Cronwatch');
    }

    /**
     * Get cronwatch_id
     * @return string
     */
    public function getCronwatchId()
    {
        return $this->getData(self::CRONWATCH_ID);
    }

    /**
     * Set cronwatch_id
     *
     * @param string $cronwatchId
     *
     * @return Codepeak\Cronwatch\Api\Data\CronwatchInterface
     */
    public function setCronwatchId($cronwatchId)
    {
        return $this->setData(self::CRONWATCH_ID, $cronwatchId);
    }

    /**
     * Get cron_schedule_schedule_id
     * @return string
     */
    public function getCronScheduleScheduleId()
    {
        return $this->getData(self::CRON_SCHEDULE_SCHEDULE_ID);
    }

    /**
     * Set cron_schedule_schedule_id
     *
     * @param string $cron_schedule_schedule_id
     *
     * @return Codepeak\Cronwatch\Api\Data\CronwatchInterface
     */
    public function setCronScheduleScheduleId($cron_schedule_schedule_id)
    {
        return $this->setData(self::CRON_SCHEDULE_SCHEDULE_ID, $cron_schedule_schedule_id);
    }
}