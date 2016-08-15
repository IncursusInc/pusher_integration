var pusher;
var pusherChannels = [];
var presenceChannel;
var privateChannel;

(function($, Drupal) {

    "use strict";

    // TODO - check to make sure we have ALL the fields needed to connect, not just the key! lol
    if (drupalSettings.pusher.pusherAppKey) {

        // Create pusher connection
        pusher = new Pusher(drupalSettings.pusher.pusherAppKey, { cluster: drupalSettings.pusher.clusterName, authEndpoint: '/pusher_integration/pusherAuth' });

        // Join all configured channels
        $.each(
            drupalSettings.pusher.defaultChannels, function(key, channelName) {
                pusherChannels[ channelName ] = pusher.subscribe(channelName);
            }
        );

        // Join all route-matched channels
        $.each(
            drupalSettings.pusher.matchedChannels, function(key, channelName) {
                pusherChannels[ channelName ] = pusher.subscribe(channelName);
            }
        );

        // Presence Channel option, if configured
        if(!drupalSettings.pusher.isUserAnonymous && drupalSettings.pusher.createPresenceChannel && drupalSettings.pusher.presenceChannelName) {
            presenceChannel = pusher.subscribe(drupalSettings.pusher.presenceChannelName); }

        // Private Channel option, if configured
        if(!drupalSettings.pusher.isUserAnonymous && drupalSettings.pusher.createPrivateChannel && drupalSettings.pusher.privateChannelName) {
            privateChannel = pusher.subscribe(drupalSettings.pusher.privateChannelName); }
    }

})(jQuery, Drupal);
