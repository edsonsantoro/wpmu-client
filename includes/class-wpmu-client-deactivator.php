<?php

namespace Wpmu_Client;
/**
 * Fired during plugin deactivation
 *
 * @link       https://santoro.studio
 * @since      1.0.0
 *
 * @package    Wpmu_Client
 * @subpackage Wpmu_Client/includes
 */

/**
 * Fired during plugin deactivation.
 *
 * This class defines all code necessary to run during the plugin's deactivation.
 *
 * @since      1.0.0
 * @package    Wpmu_Client
 * @subpackage Wpmu_Client/includes
 * @author     Edson Del Santoro <edsonsantoro@gmail.com>
 */
class Deactivator {

	/**
	 * Short Description. (use period)
	 *
	 * Long Description.
	 *
	 * @since    1.0.0
	 */
	public static function deactivate() {
		$message = __("Plugin desativado.", WPMU_CLIENT_TEXT_DOMAIN );
		Notice::addInfo( $message );
	}

}
