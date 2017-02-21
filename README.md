# magento2-cronwatch

This module will monitor the table `cron_schedule` for jobs marked as `error`. When entry is found there is a note of it in the `system.log` and also e-mail is sent to given recipients.

**Use this module at own risk and make sure to test it first!**

## How to install

After installing Magento 2, run the following commands from your Magento 2 root directory:

```
composer require codepeak/magento2-cronwatch
php bin/magento cache:flush
```

## How to configure

Enter Magento 2 admin and navigate to `Stores > Configuration > Advanced > System`. There is a new group called `Cron watch`. Enable the module and enter recipients separated with comma (`,`). Then flush the cache.

## Contribute

Feel free to **fork** and contribute to this module. Simply create a pull request and we'll review and merge your changes to master branch.

## About Codepeak

Codepeak is a Magento consultant agency located in Sweden. For more information, please visit [codepeak.se](https://codepeak.se).