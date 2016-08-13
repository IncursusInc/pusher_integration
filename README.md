# Pusher Integration
Pusher Integration is a Drupal 8 module designed to provide a robust interface for integrating Pusher.com services into Drupal modules and apps. By itself, it doesn't do anything, but rather is a tool for Drupal developers to incorporate realtime message broadcasting into their modules and applications.

# Requirements

* Drupal 8.x
* PHP 5.5 or higher (untested under PHP 7)
* An account at Pusher.com (at least a free sandbox account for smaller sites)

# Known Issues

* None at this time

# Installation Instructions

1. Download and install the module (./modules/custom/push_integration)
2. Configure the module (admin/config/pusher_integration)
3. Install whatever other module needs it and go from there (e.g. [SiteCommander](https://github.com/IncursusInc/sitecommander))

# Usage Information for Developers

## Server-Side PHP

On the server side, in your app or module, you can simply broadcast commands as follows:

```php

<?php

/**
 * @file
 * Contains \Drupal\sitecommander\Controller\SiteCommanderController.
 */

namespace Drupal\my_module\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Config\ConfigFactory;

use Drupal\pusher_integration\Controller\PusherController;

class MyController extends ControllerBase {

  protected configFactory;

  public function __construct( ConfigFactory $configFactory )
  {
    $this->configFactory = $configFactory;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('config.factory')
    );
  }

  ...

  public function foo()
  {
		...

    $data = array(
      'someVar' => 'Some value',
      'anotherVar' => 'Some other value'
    );

    PusherController::broadcastMessage( $this->configFactory, 'my-channel-name-here', 'my-event-name-here', $data );

		...
  }

	...
}
```

## Client-side Javascript

This module will create a global Javascript object simply called "pusher". You may use that to access the Pusher connection that is created automatically for you on page loads:

```javascript
var myChannel;

if (pusher)
{
  myChannel = pusher.subscribe('my-channel-name-here');

  // Bind to the "my-event-name-here" event, so we can listen for it to come across the wire!
  pusher.bind('my-event-name-here', function(data) {
	// Access your event information via the "data" object once the event is received by the client/browser
	console.log( data );
  });

}
```
