var pusher;
var pusherChannels = [];
var presenceChannel;

(function($, Drupal) {

  "use strict";

	// TODO - check to make sure we have ALL the fields needed to connect, not just the key! lol
	if (drupalSettings.pusher.pusherAppKey) {

		// Create pusher connection
		pusher = new Pusher(drupalSettings.pusher.pusherAppKey, { cluster: drupalSettings.pusher.clusterName, authEndpoint: '/pusher_integration/pusherAuth' });

		// Join all configured channels
		$.each( drupalSettings.pusher.defaultChannels, function(key, channelName) {
			pusherChannels[ channelName ] = pusher.subscribe( channelName );
		});

		// Join all route-matched channels
		$.each( drupalSettings.pusher.matchedChannels, function(key, channelName) {
			pusherChannels[ channelName ] = pusher.subscribe( channelName );
		});

		// Presence Channel option, if configured
		// TODO - only do this if we have an auth'd user in Drupal, otherwise it throws a (harmless) Javascript error
		if(!drupalSettings.pusher.isUserAnonymous && drupalSettings.pusher.createPresenceChannel && drupalSettings.pusher.presenceChannelName)
			presenceChannel = pusher.subscribe(drupalSettings.pusher.presenceChannelName);

	}

})(jQuery, Drupal);
