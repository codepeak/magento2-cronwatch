<?php

namespace Codepeak\Cronwatch\Cron;

use \Codepeak\Cronwatch\Model\CronwatchRepository;
use \Magento\Framework\App\Config\ScopeConfigInterface;
use \Magento\Framework\ObjectManagerInterface;
use \Psr\Log\LoggerInterface;

/**
 * Class Monitor
 *
 * @package Codepeak\Cronwatch\Cron
 * @author  Robert Lord, Codepeak AB <robert@codepeak.se>
 */
class Monitor
{
    /**
     * @var LoggerInterface
     */
    protected $logger;

    /**
     * @var ObjectManager
     */
    protected $objectManager;

    /**
     * @var ScopeConfigInterface
     */
    protected $scopeConfig;

    /**
     * Constructor
     *
     * @param LoggerInterface        $logger
     * @param ObjectManagerInterface $objectManager
     * @param ScopeConfigInterface   $scopeConfig
     */
    public function __construct(
        LoggerInterface $logger,
        ObjectManagerInterface $objectManager,
        ScopeConfigInterface $scopeConfig
    ) {
        $this->logger = $logger;
        $this->objectManager = $objectManager;
        $this->scopeConfig = $scopeConfig;
    }

    /**
     * Execute the cron
     *
     * @return void
     */
    public function execute()
    {
        // Make sure we're active
        if ($this->scopeConfig->getValue('system/cronwatch/enabled') !== '1') {
            return;
        }

        // Build e-mail recipients
        $emailRecipients = [];
        foreach (explode(',', $this->scopeConfig->getValue('system/cronwatch/recipients')) as $recipient) {
            $emailRecipients[] = trim($recipient);
        }

        /**
         * Fetch all entries in cron_schedule table
         */
        $cronScheduleCollection = $this->objectManager->get('Magento\Cron\Model\Schedule')
            ->getCollection()
            ->addFieldToFilter('status', ['eq' => 'error'])
            ->addFieldToFilter('cronwatch_id', ['null' => true]);

        // Left join with our status table
        $cronScheduleCollection
            ->getSelect()
            ->joinLeft(
                ['cronwatch' => $cronScheduleCollection->getTable('codepeak_cronwatch')],
                'main_table.schedule_id = cronwatch.cron_schedule_schedule_id',
                ['cronwatch_id']
            );

        /**
         * Loop each entry and build error string
         */
        foreach ($cronScheduleCollection as $schedule) {
            // Remove data not needed
            $schedule->unsetData('cronwatch_id');

            // Build the message
            $message = "Job with code '%s' marked as failed with the reason '%s'.\n\n%s";
            $message = sprintf(
                $message,
                $schedule->getJobCode(),
                $schedule->getMessages(),
                var_export($schedule->getData(), true)
            );

            // Add entry to the logs, might be good to have information here as well
            $this->logger->addError($message);

            // Setup the e-mail
            if (count($emailRecipients)) {
                try {
                    $email = new \Zend_Mail();
                    $email->setSubject('Cronwatch error detected: ' . $schedule->getJobCode());
                    $email->setBodyText($message);
                    $email->setFrom(
                        $this->scopeConfig->getValue('trans_email/ident_general/email'),
                        $this->scopeConfig->getValue('trans_email/ident_general/name')
                    );
                    $email->addTo($emailRecipients);
                    $email->send();
                } catch (\Exception $e) {
                    $this->logger->addError($e->getMessage());
                }
            }

            // Fetch cronwatch repository
            $cronwatchRepository = $this->objectManager->create(CronwatchRepository::class);

            // Create entry in our monitor table to avoid duplicates
            $cronwatchModel = $this->objectManager->get('Codepeak\Cronwatch\Model\Cronwatch');
            $cronwatchModel->setCronScheduleScheduleId($schedule->getScheduleId());

            // Store the entity
            $cronwatchRepository->save($cronwatchModel);
        }

        // Clean our table in case of table cron_schedule was truncated
        $cronwatchCollection = $this->objectManager->get('Codepeak\Cronwatch\Model\Cronwatch')
            ->getCollection();

        // Left join with cron_schedule table
        $cronwatchCollection
            ->getSelect()
            ->joinLeft(
                ['cron_schedule' => $cronwatchCollection->getTable('cron_schedule')],
                'main_table.cron_schedule_schedule_id = cron_schedule.schedule_id',
                ['schedule_id']
            );

        // Remove entries that no longer exists in cron_schedule table, not 100% fool proof but good enough
        foreach ($cronwatchCollection as $cronwatch) {
            if (!$cronwatch->getScheduleId()) {
                // Fetch cronwatch repository
                $cronwatchRepository = $this->objectManager->create(CronwatchRepository::class);

                // Load the model
                $cronwatchModel = $this->objectManager->get('Codepeak\Cronwatch\Model\Cronwatch')->load(
                    $cronwatch->getCronwatchId()
                );

                // Delete it
                $cronwatchRepository->delete($cronwatchModel);
            }
        }
    }
}