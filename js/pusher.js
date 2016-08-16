var pusher;
var pusherChannels = [];
var presenceChannel;
var privateChannel;

(function($, Drupal) {

    "use strict";

    var s = drupalSettings.pusher;

    // Set up debug logging to the console (if enabled in the admin panel)
    if (s.debugLogging) {
        Pusher.log = function(message) {
            if (window.console && window.console.log) window.console.log(message);
        };
    }

    if (s.pusherAppKey) {

        // Create pusher connection
        if (s.matchedChannels.length > 0) {

            pusher = new Pusher(s.pusherAppKey, { cluster: s.clusterName, authEndpoint: '/pusher_integration/pusherAuth' });

            // Join all route-matched channels
            $.each(
                s.matchedChannels, function(key, channelName) {
                    if(channelName.includes('presence-')) {
                        presenceChannel = pusher.subscribe(channelName);
                    }
                    else
                    if(channelName.includes('private-')) {
                        privateChannel = pusher.subscribe(s.privateChannelName); }
                    else {
                        pusherChannels[ channelName ] = pusher.subscribe(channelName); }
                }
            );

        }
    }

})(jQuery, Drupal);
