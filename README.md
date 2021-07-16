This is fork of [Official Split SDK for PHP](https://github.com/splitio/php-client)

---

# Split SDK for PHP

[![Build Status](https://api.travis-ci.com/splitio/php-client.svg?branch=master)](https://api.travis-ci.com/splitio/php-client)

This SDK is designed to work with Split, the platform for controlled rollouts, serving features to your users via the Split feature flag to manage your complete customer experience.

## Compatibility
This SDK is compatible with PHP 5.6 and above.

## Getting started
Below is a simple example that describes the instantiation and most basic usage of our SDK.
Keep in mind that since PHP does not have the ability to share memory between processes the use of the [split-synchronizer](https://help.split.io/hc/en-us/articles/360019686092-Split-Synchronizer-Proxy) is mandatory for this SDK.

```php
<?php

$sdkConfig = ['cache' => ['adapter' => 'predis']];
$splitFactory = \SplitIO\Sdk::factory('SDK_API_KEY', $sdkConfig);

$splitClient = $splitFactory->client();
$treatment = $splitClient->getTreatment('CUSTOMER_ID','SPLIT_NAME');
if ($treatment === 'on') {
    // Feature is enabled for this user!
} elseif ($treatment === 'off') {
    // Feature is disabled for this user!
} else {
    // Unable to perform evaluation.
}
```

## Submitting issues
 
The Split team monitors all issues submitted to this [issue tracker](https://github.com/splitio/php-client/issues). We encourage you to use this issue tracker to submit any bug reports, feedback, and feature enhancements. We'll do our best to respond in a timely manner.

## Contributing
Please see [Contributors Guide](CONTRIBUTORS-GUIDE.md) to find all you need to submit a Pull Request (PR).

## License
Licensed under the Apache License, Version 2.0. See: [Apache License](http://www.apache.org/licenses/).

## About Split
 
Split is the leading Feature Delivery Platform for engineering teams that want to confidently deploy features as fast as they can develop them. Split’s fine-grained management, real-time monitoring, and data-driven experimentation ensure that new features will improve the customer experience without breaking or degrading performance. Companies like Twilio, Salesforce, GoDaddy and WePay trust Split to power their feature delivery.
 
To learn more about Split, contact hello@split.io, or get started with feature flags for free at https://www.split.io/signup.
 
Split has built and maintains SDKs for:
 
* Java [Github](https://github.com/splitio/java-client) [Docs](https://help.split.io/hc/en-us/articles/360020405151-Java-SDK)
* Javascript [Github](https://github.com/splitio/javascript-client) [Docs](https://help.split.io/hc/en-us/articles/360020448791-JavaScript-SDK)
* Node [Github](https://github.com/splitio/javascript-client) [Docs](https://help.split.io/hc/en-us/articles/360020564931-Node-js-SDK)
* .NET [Github](https://github.com/splitio/dotnet-client) [Docs](https://help.split.io/hc/en-us/articles/360020240172--NET-SDK)
* Ruby [Github](https://github.com/splitio/ruby-client) [Docs](https://help.split.io/hc/en-us/articles/360020673251-Ruby-SDK)
* PHP [Github](https://github.com/splitio/php-client) [Docs](https://help.split.io/hc/en-us/articles/360020350372-PHP-SDK)
* Python [Github](https://github.com/splitio/python-client) [Docs](https://help.split.io/hc/en-us/articles/360020359652-Python-SDK)
* GO [Github](https://github.com/splitio/go-client) [Docs](https://help.split.io/hc/en-us/articles/360020093652-Go-SDK)
* Android [Github](https://github.com/splitio/android-client) [Docs](https://help.split.io/hc/en-us/articles/360020343291-Android-SDK)
* iOS [Github](https://github.com/splitio/ios-client) [Docs](https://help.split.io/hc/en-us/articles/360020401491-iOS-SDK)
 
For a comprehensive list of open source projects visit our [Github page](https://github.com/splitio?utf8=%E2%9C%93&query=%20only%3Apublic%20).
 
**Learn more about Split:**
 
Visit [split.io/product](https://www.split.io/product) for an overview of Split, or visit our documentation at [help.split.io](http://help.split.io) for more detailed information.
