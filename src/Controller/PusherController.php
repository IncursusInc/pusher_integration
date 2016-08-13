<?php

/**
 * @file
 * Contains \Drupal\pusher_integration\Controller\PusherController.
 */

namespace Drupal\pusher_integration\Controller;

use Pusher;

use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Config\ConfigFactory;
use Drupal\Core\Session\AccountInterface;

use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\Response;

use Drupal\pusher_integration\SiteCommanderUtils;
use Drupal\pusher_integration\Controller\PusherDebugLogController;

class PusherController extends ControllerBase {

	protected $configFactory;
	protected $currentUser;
	protected $pusher;

	public function __construct( ConfigFactory $configFactory, AccountInterface $account )
	{
		$this->configFactory = $configFactory;
		$this->currentUser = $account;

		// Read in Pusher config
		$pusherConfig = $this->configFactory->get('pusher_integration.settings');
		$pusherAppId = $pusherConfig->get('pusherAppId');
		$pusherAppKey = $pusherConfig->get('pusherAppKey');
		$pusherAppSecret = $pusherConfig->get('pusherAppSecret');
		$debugLogging = $pusherConfig->get('debugLogging');
		$clusterName = $pusherConfig->get('clusterName');
		$options = array('cluster' => $clusterName, 'encrypted' => true);

		// Create connection to Pusher
		$this->pusher = new Pusher( $pusherAppKey, $pusherAppSecret, $pusherAppId, $options );

		// Enable debug logging if configured in the admin panel
		if($debugLogging) {
			$this->pusher->set_logger( new PusherDebugLogController() );
		}

	}

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('config.factory'),
      $container->get('current_user')
    );
  }

	// User authentication for presence and private channels
	public function pusherAuth()
	{
		// Only do this if the user is NOT anonymous! i.e. they are logged into Drupal
		if (!$this->currentUser->isAnonymous()) {

			// Load the current user.
			$u = \Drupal\user\Entity\User::load($this->currentUser->id());

			$presenceData = array(
				'user_id' => $this->currentUser->id(),
				'user_name' => $u->get('name')->value
			);

			// Authenticate to the presence channel
			echo $this->pusher->presence_auth($_POST['channel_name'], $_POST['socket_id'], $this->currentUser->id(), $presenceData);

    	$response = new Response();
			return $response;
		}
		else
		{
    	$response = new Response();
			$response->setStatusCode(403);
			return $response;
		}
	}

	// Method to broadcast an event to all connected clients in a particular channel (or an array of channels)
	public function broadcastMessage( $config, $channelNames, $eventName, $data )
	{
		$this->pusher->trigger( $channelNames, $eventName, $data );
	}

	// Get information about a specific channel (by name)
	public function getChannelInfo( $channelName, $options='' )
	{
		return $this->pusher->get_channel_info($channelName, $options);
	}

	// Get a list of channels
	public function getChannelList( $filter )
	{
		return $this->pusher->get_channels( $filter );
	}

	// Send generic REST request to Pusher
	public function get( $path, $params = array() )
	{
		return $this->pusher->get( $path, $params );
	}

}
