<?php

namespace Wpmu_Client;

/**
 * Fired during plugin activation
 *
 * @link       https://santoro.studio
 * @since      1.0.0
 *
 * @package    Wpmu_Client
 * @subpackage Wpmu_Client/includes
 */

/**
 * Fired during plugin activation.
 *
 * This class defines all code necessary to run during the plugin's activation.
 *
 * @since      1.0.0
 * @package    Wpmu_Client
 * @subpackage Wpmu_Client/includes
 * @author     Edson Del Santoro <edsonsantoro@gmail.com>
 */
class Activator
{

	/**
	 * Short Description. (use period)
	 *
	 * Long Description.
	 *
	 * @since    1.0.0
	 */
	public static function activate()
	{
		$message = __("Plugin ativado. Por favor, <a href='/wp-admin/network/settings.php?page=wpmu_client_network_settings-page'>defina as opções aqui.</a>", WPMU_CLIENT_TEXT_DOMAIN );
		Notice::addInfo( $message );
	}

	
}
