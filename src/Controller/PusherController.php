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

class PusherController extends ControllerBase
{

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
        $this->pusher = new Pusher($pusherAppKey, $pusherAppSecret, $pusherAppId, $options);

        if (!$this->pusher) {
            \Drupal::logger('pusher_integration')->debug('Unable to connect to Pusher.com'); 
        }

        // Enable debug logging if configured in the admin panel
        if ($debugLogging) {
            $this->pusher->set_logger(new PusherDebugLogController());
        }

    }

    /**
   * {@inheritdoc}
   */
    public static function create(ContainerInterface $container) 
    {
        return new static(
          $container->get('config.factory'),
          $container->get('current_user')
        );
    }

    /** 
     * User authentication for presence and private channels
     *
     * @return Symfony\Component\HttpFoundation\Response
     */
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
            if (preg_match('/^presence-/', $_POST['channel_name'])) {
                echo $this->pusher->presence_auth($_POST['channel_name'], $_POST['socket_id'], $this->currentUser->id(), $presenceData); 
            } else {
                echo $this->pusher->socket_auth($_POST['channel_name'], $_POST['socket_id']); 
            }

              $response = new Response();
            return $response;
        } else {
            $response = new Response();
            $response->setStatusCode(403);
            return $response;
        }
    }

    /** 
     * Method to broadcast an event to all connected clients in a particular channel (or an array of channels)
     *
     * @return array
     */
    public function broadcastMessage( $config, $channelNames, $eventName, $data )
    {
        if (!$this->pusher->trigger($channelNames, $eventName, $data)) {
            \Drupal::logger('pusher_integration')->error('Triggered event failed. Data: ' . $channelNames . ' : ' . $eventName);
        }
    }

    /** 
     * Get information about a specific channel (by name)
     *
     * @return array
     */
    public function getChannelInfo( $channelName, $options='' )
    {
        $response = $this->pusher->get_channel_info($channelName, $options);
        if (!$response) {
            \Drupal::logger('pusher_integration')->error('getChannelInfo failed. Data: ' . $channelName . ' : ' . $response); 
        } else {
            return $response; 
        }
    }

    /** 
     * Get a list of channels
     *
     * @return array
     */
    public function getChannelList()
    {
        $response = $this->pusher->get_channels();
        if (!$response) {
            \Drupal::logger('pusher_integration')->error('getChannelList failed.'); 
        } else {
            return $response; 
        }
    }

    /** 
     * Send generic REST request to Pusher
     *
     * @return array
     */
    public function get( $path, $params = array() )
    {
        $response = $this->pusher->get($path, $params);
        if (!$response) {
            \Drupal::logger('pusher_integration')->error('get() failed.'); 
        } else {
            return $response; 
        }
    }

}
