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
		$clusterName = $pusherConfig->get('clusterName');
		$options = array('cluster' => $clusterName, 'encrypted' => true);

		// Create connection to Pusher
		$this->pusher = new Pusher( $pusherAppKey, $pusherAppSecret, $pusherAppId, $options );
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

			//$config = $this->configFactory->get('pusher_integration.settings');
			//$pusherAppId = $config->get('pusherAppId');
			//$pusherAppKey = $config->get('pusherAppKey');
			//$pusherAppSecret = $config->get('pusherAppSecret');
			//$clusterName = $config->get('clusterName');

			//$options = array('cluster' => $clusterName, 'encrypted' => true);

			//$pusher = new Pusher( $pusherAppKey, $pusherAppSecret, $pusherAppId, $options );

			// Load the current user.
			$u = \Drupal\user\Entity\User::load($this->currentUser->id());

			$presenceData = array(
				'user_id' => $this->currentUser->id(),
				'user_name' => $u->get('name')->value
			);

			$this->pusher->socket_auth($_POST['channel_name'], $_POST['socket_id'], $presenceData);
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

	// Method to broadcast an event to all connected clients in a particular channel
	public function broadcastMessage( $config, $channelName, $eventName, $data )
	{
		$this->pusher->trigger( $channelName, $eventName, $data );
	}

	public function getChannelInfo( $channelName, $options='' )
	{
		return $this->pusher->get_channel_info($channelName, $options);
	}

}
