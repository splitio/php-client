# Split - PHP SDK Client
## Installing Split SDK using composer
```
$ composer require splitio/split-sdk-php
```
## Setting backend cron job
```
# exec crontab -e and add the line below
*/1 * * * * php /path/to/your/project/vendor/bin/splitio <API-KEY>
```
## Write your code!
```php
/** Optional: You could develop your own adapters for cache, log, etc. */
$additional_options = ['cache-adapter'=>$myCacheAdapter, 'log-adapter'=>$myLogAdapter];

/** Create the Split Client instance. */
$splitClient = \SplitIO\Sdk::factory('API_KEY', $additional_options);

/** Checking if the feature is enabled or not. */
if ($splitClient->isOn('user-id','feature-name')) {
    //Code for enabled feature
} else {
    //Code for disabled feature
}
```
