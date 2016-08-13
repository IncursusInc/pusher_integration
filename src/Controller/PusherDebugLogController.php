<?php

/**
 * @file
 * Contains \Drupal\pusher_integration\Controller\PusherDebugLogController.
 */

namespace Drupal\pusher_integration\Controller;

use Drupal\Core\Controller\ControllerBase;

class PusherDebugLogController extends ControllerBase {

	// Watchdog logger
	public function log( $msg ) {
		\Drupal::logger('pusher_integration')->debug($msg);
	}

}
