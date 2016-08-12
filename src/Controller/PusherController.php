<?php

/**
 * @file
 * Contains \Drupal\pusher_integration\Controller\PusherController.
 */

namespace Drupal\pusher_integration\Controller;

use Pusher;
use Drupal\Core\Controller\ControllerBase;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\Core\Ajax\AjaxResponse;
use Symfony\Component\HttpFoundation\Response;
use Drupal\Core\Config\ConfigFactory;
use Drupal\pusher_integration\Ajax\ReadMessageCommand;
use Drupal\pusher_integration\SiteCommanderUtils;

class PusherController extends ControllerBase {

	protected $configFactory;

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

	public function pusherAuth()
	{
		$config = $this->configFactory->get('pusher_integration.settings');
		$pusherAppId = $config->get('pusherAppId');
		$pusherAppKey = $config->get('pusherAppKey');
		$pusherAppSecret = $config->get('pusherAppSecret');
		$clusterName = $config->get('clusterName');

		$options = array('cluster' => $clusterName, 'encrypted' => true);

		$pusher = new Pusher( $pusherAppKey, $pusherAppSecret, $pusherAppId, $options );

		$presenceData = array('user_id' => $_POST['socket_id']);

		$pusher->socket_auth($_POST['channel_name'], $_POST['socket_id'], $presenceData);

		//echo $pusher->presence_auth($_POST['channel_name'], $_POST['socket_id'], $_POST['socket_id'], $presenceData);
		echo $pusher->presence_auth($_POST['channel_name'], $_POST['socket_id'], $_POST['socket_id'], $presenceData);

    // Create AJAX Response object.
    $response = new AjaxResponse();
		return $response;
	}

	public function broadcastMessage( $config, $channelName, $eventName, $data )
	{
		$pusherConfig = $config->get('pusher_integration.settings');
		$pusherAppId = $pusherConfig->get('pusherAppId');
		$pusherAppKey = $pusherConfig->get('pusherAppKey');
		$pusherAppSecret = $pusherConfig->get('pusherAppSecret');
		$clusterName = $pusherConfig->get('clusterName');

		$options = array('cluster' => $clusterName, 'encrypted' => true);

		$pusher = new Pusher( $pusherAppKey, $pusherAppSecret, $pusherAppId, $options );

		$pusher->trigger( $channelName, $eventName, $data );
	}

}
