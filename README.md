# Pusher Integration
Pusher Integration is a Drupal 8 module designed to provide a robust interface for integrating Pusher.com services into Drupal modules and apps. By itself, it doesn't do anything, but rather is a tool for Drupal developers to incorporate realtime message broadcasting into their modules and applications.

# Requirements

* Drupal 8.x
* PHP 5.5 or higher (untested under PHP 7)
* An account at Pusher.com (at least a free sandbox account for smaller sites)
* Composer Manager module installed

# Known Issues

* None at this time

# Installation Instructions

1. Download and install the module (./modules/custom/push_integration)
2. Run an update with Drush to pull in dependencies: "drush up" (Be sure to have the Composer Manager module installed!)
3. Configure the module (admin/config/pusher_integration)
4. Install whatever other module needs it and go from there (e.g. [SiteCommander](https://github.com/IncursusInc/sitecommander))

# Configuration

The configuration options can be accessed via the normal admin configuration menu (under "Pusher Integration Options"), or by visiting /admin/config/pusher_integration.

## Pusher.com Connection Settings

This is pretty straightforward. Just plug in the configuration values that Pusher.com gives you for your app.

## Channel Configuration

This section requires a bit more explanation. In essence, this textarea field allows you to configure channels for specific paths on your site. Since the paths support regex,
you can quickly and easily create global channels that affect your whole site, or just certain sections of your site.

The format for each line is:

    CHANNEL_NAME|PATH_PATTERN

Where CHANNEL_NAME can be:

    presence-SOMESTRING: to create a presence channel
    private-SOMESTRING: to create a private channel
    SOMESTRING: Without "presence-" or "private-" in it, to create a public channel

For example, our [SiteCommander module](https://github.com/IncursusInc/sitecommander) supports Pusher for message broadcasting. It requires a public channel simply called "site-commander"
to be setup for all pages on the site.  So that entry would look like:

```
site-commander:.*
```

As another example, let's say you wanted to create a private channel, but only on a page at "/super/secret/path". That entry would look like:

```
private-my-secret-channel|/super/secret/path
```

You get the idea.

## Miscellaneous Settings

Currently, there is only one option in this section, and it will allow you to enable debug logging to the Drupal Watchdog logger. Note: this is not recommended for production!

# Usage Information for Developers

## Server-Side PHP

On the server side, in your app or module, you can simply broadcast commands as follows:

```php
  use Drupal\pusher_integration\Controller\PusherController;
  ...
  $this->pusher = new PusherController( $configFactory, $currentUser );
  $data = array(
    'someVar' => 'Some value',
    'anotherVar' => 'Some other value'
  );

  // Broadcast an event to a single channel
  $this->pusher->broadcastMessage( $this->configFactory, 'my-channel-name-here', 'my-event-name-here', $data );
  ...
  // Broadcast an event to an array of channels
  $this->pusher->broadcastMessage( $this->configFactory, array('my-channel-name-here', 'channel2'), 'my-event-name-here', $data );
  ...
  // Get info on a specific channel
  $this->pusher->getChannelInfo( 'my-channel-name-here' );
  ...
  // Get list of channels
  $this->pusher->getChannelList();
  ...
  // Send any generic Pusher.com REST command
  $this->pusher->get('/channels');
```


Here is a more pseudo-complete example, with dependency injection:

```php
<?php

/**
 * @file
 * Contains \Drupal\sitecommander\Controller\SiteCommanderController.
 */

namespace Drupal\my_module\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Config\ConfigFactory;
use Drupal\Core\Session\AccountInterface;

use Drupal\pusher_integration\Controller\PusherController;

class MyController extends ControllerBase {

  protected $configFactory;
  protected $currentUser;
  protected $pusher;

  public function __construct( ConfigFactory $configFactory, AccountInterface $account )
  {
    $this->configFactory = $configFactory;
    $this->currentUser = $account;

    $this->pusher = new PusherController( $configFactory, $currentUser );
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('config.factory'),
      $container->get('current_user'),
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

    $this->pusher->broadcastMessage( $this->configFactory, 'my-channel-name-here', 'my-event-name-here', $data );

		...
  }

	...
}
```

## Client-side Javascript

This module will create a global Javascript object simply called "pusher". You may use that to access the Pusher connection that is created automatically for you on page loads. Additionally, it will create the following global Javascript variables that can be used to access various Pusher channels:

*pusher*
  the global Pusher object

*pusherChannels*
  array of public channels the user is subscribed to. This is generally your default public channel, plus any default channels you've configured.
  
*privateChannel*
  If you've configured the option to automatically create a private channel, this will be set to the channel object.

*presenceChannel*
  If you've configured the option to automatically create a presence channel, this will be set to the channel object.
  
```javascript
...
  // Bind to the "my-event-name-here" event on the private channel, so we can listen for it to come across the wire!
  privateChannel.bind('my-event-name-here', function(data) {
	// Access your event information via the "data" object once the event is received by the client/browser
	console.log( data );
  });
...
```

Here is an example of creating your own channel and subscribing to an event:
  
```javascript
var myChannel;

if (pusher)
{
  myChannel = pusher.subscribe('my-channel-name-here');

  // Bind to the "my-event-name-here" event, so we can listen for it to come across the wire!
  myChannel.bind('my-event-name-here', function(data) {
	// Access your event information via the "data" object once the event is received by the client/browser
	console.log( data );
  });

}
```

If you have the need, you can also trigger/broadcast events straight from your app via Javascript as well:

```javascript
var triggered = publicChannel.trigger('some-event-name', { your: data });
```

In order for this to work, be sure to enable client events inside your app settings at Pusher.com.
