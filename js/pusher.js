var pusher;
var pusherChannels = [];

(function($, Drupal) {

  "use strict";

	// TODO - check to make sure we have ALL the fields needed to connect, not just the key! lol
	if (drupalSettings.pusher.pusherAppKey) {

		// Create pusher connection
		pusher = new Pusher(drupalSettings.pusher.pusherAppKey, { cluster: drupalSettings.pusher.clusterName, authEndpoint: '/pusher/pusherAuth' });

		// Join all configured channels
		$.each( drupalSettings.pusher.defaultChannels, function(key, channelName) {
			pusherChannels[ channelName ] = pusher.subscribe( channelName );
		});

		// TODO - Presence Channel option, if configured
	}

})(jQuery, Drupal);
