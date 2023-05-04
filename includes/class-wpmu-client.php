<?php

/**
 * The file that defines the core plugin class
 *
 * A class definition that includes attributes and functions used across both the
 * public-facing side of the site and the admin area.
 *
 * @link       https://santoro.studio
 * @since      1.0.0
 *
 * @package    Wpmu_Client
 * @subpackage Wpmu_Client/includes
 */

/**
 * The core plugin class.
 *
 * This is used to define internationalization, admin-specific hooks, and
 * public-facing site hooks.
 *
 * Also maintains the unique identifier of this plugin as well as the current
 * version of the plugin.
 *
 * @since      1.0.0
 * @package    Wpmu_Client
 * @subpackage Wpmu_Client/includes
 * @author     Edson Del Santoro <edsonsantoro@gmail.com>
 */
class Wpmu_Client
{

	/**
	 * The loader that's responsible for maintaining and registering all hooks that power
	 * the plugin.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      Wpmu_Client_Loader    $loader    Maintains and registers all hooks for the plugin.
	 */
	protected $loader;

	/**
	 * The unique identifier of this plugin.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      string    $plugin_name    The string used to uniquely identify this plugin.
	 */
	protected $plugin_name;

	/**
	 * The current version of the plugin.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      string    $version    The current version of the plugin.
	 */
	protected $version;

	/**
	 * If this plugin can run on the network
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      bool    $can_run    If this plugin can run on the network
	 */
	protected $can_run;


	/**
	 * Define the core functionality of the plugin.
	 *
	 * Set the plugin name and the plugin version that can be used throughout the plugin.
	 * Load the dependencies, define the locale, and set the hooks for the admin area and
	 * the public-facing side of the site.
	 *
	 * @since    1.0.0
	 */
	public function __construct()
	{
		if (defined('WPMU_CLIENT_VERSION')) {
			$this->version = WPMU_CLIENT_VERSION;
		} else {
			$this->version = '1.0.0';
		}
		$this->plugin_name = 'wpmu-client';

		$this->load_dependencies();

		// If we can not run for any reason, then just exit.
		if (!$this->can_run) {
			return;
		}

		$this->set_locale();
		$this->define_admin_hooks();
		$this->define_public_hooks();
	}

	/**
	 * Load the required dependencies for this plugin.
	 *
	 * Include the following files that make up the plugin:
	 *
	 * - Wpmu_Client_Loader. Orchestrates the hooks of the plugin.
	 * - Wpmu_Client_i18n. Defines internationalization functionality.
	 * - Wpmu_Client_Admin. Defines all hooks for the admin area.
	 * - Wpmu_Client_Public. Defines all hooks for the public side of the site.
	 * - Wpmu_Client_Network_Config. Defines settings for plugin network configuration.
	 * - Wpmu_Client_Admin_Notice. Show admin notices.
	 * - Wpmu_Client_Process. Execute PHP process and keep track of
	 * 
	 *
	 * Create an instance of the loader which will be used to register the hooks
	 * with WordPress.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function load_dependencies()
	{

		/**
		 * The class responsible for orchestrating the actions and filters of the
		 * core plugin.
		 */
		require_once plugin_dir_path(dirname(__FILE__)) . 'includes/class-wpmu-client-loader.php';

		/**
		 * The class responsible for defining internationalization functionality
		 * of the plugin.
		 */
		require_once plugin_dir_path(dirname(__FILE__)) . 'includes/class-wpmu-client-i18n.php';

		/**
		 * The class responsible for defining all actions that occur in the admin area.
		 */
		require_once plugin_dir_path(dirname(__FILE__)) . 'admin/class-wpmu-client-admin.php';

		/**
		 * The class responsible for defining all actions that occur in the public-facing
		 * side of the site.
		 */
		require_once plugin_dir_path(dirname(__FILE__)) . 'public/class-wpmu-client-public.php';

		/**
		 * The class responsible for the settings page of this plugin for a single blog.
		 */
		require_once plugin_dir_path(dirname(__FILE__)) . 'admin/partials/wpmu-client-admin-display.php';

		/**
		 * The class responsible for the settings page of this plugin for the network.
		 */
		require_once plugin_dir_path(dirname(__FILE__)) . 'admin/partials/wpmu-client-network-display.php';

		/**
		 * The class responsible for displaying admin notices.
		 */
		require_once plugin_dir_path(dirname(__FILE__)) . 'admin/class-wpmu-client-admin-notices.php';

		/**
		 * The class responsible for adding clients as part of the network.
		 */
		//require_once plugin_dir_path(dirname(__FILE__)) . 'admin/partials/wpmu-client-network-clients.php';

		// We depend on Simply Static plugin, if it is not present, set plugin as can not run.
		if (!function_exists('simply_static_run_plugin')) {
			$message = 'WPMU-CLIENT: O plugin Simply Static não existe ou não está ativo na rede. Não podemos trabalhar. Abortando.';
			error_log($message);
			$notice = __($message, 'wpmu-client');
			new Wpmu_Client_Admin_Notice($notice, 'error', true);
			$this->can_run = false;
			wp_die($message);
		}

		// All clear to run
		$this->can_run = true;
		$this->loader = new Wpmu_Client_Loader();
	}

	/**
	 * Define the locale for this plugin for internationalization.
	 *
	 * Uses the Wpmu_Client_i18n class in order to set the domain and to register the hook
	 * with WordPress.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function set_locale()
	{

		$plugin_i18n = new Wpmu_Client_i18n();

		$this->loader->add_action('plugins_loaded', $plugin_i18n, 'load_plugin_textdomain');
	}

	/**
	 * Register all of the hooks related to the admin area functionality
	 * of the plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function define_admin_hooks()
	{

		$plugin_admin = new Wpmu_Client_Admin($this->get_plugin_name(), $this->get_version());
		$plugin_network_admin = new Wpmu_Client_Network_Config($this->get_plugin_name());

		$this->loader->add_action('admin_enqueue_scripts', $plugin_admin, 'enqueue_styles');
		$this->loader->add_action('wp_validate_site_data', $plugin_admin, 'verify_custom_site_fields', 10, 3);
		$this->loader->add_action('wp_initialize_site', $plugin_admin, 'save_custom_site_fields', 10, 2);
		$this->loader->add_action('wp_update_site', $plugin_admin, 'update_custom_site_fields', 10, 2);
		$this->loader->add_action('network_site_new_form', $plugin_admin, 'enqueue_scripts');
		$this->loader->add_action('admin_enqueue_scripts', $plugin_admin, 'enqueue_scripts');
		$this->loader->add_action('network_site_new_form', $plugin_admin, 'show_client_site_field');
		$this->loader->add_action('network_site_new_form', $plugin_admin, 'show_ftp_credentials_fields');
		$this->loader->add_action('switch_blog', $plugin_admin, 'set_ss_options', 10, 3);
		$this->loader->add_action('network_admin_menu', $plugin_admin, 'wpmu_new_page');
		$this->loader->add_action('wp_ajax_wpmu_init_export', $plugin_admin, 'wpmu_init_export');
		$this->loader->add_action('wp_ajax_check_typed_directory', $plugin_network_admin, 'check_typed_directory');

	}

	/**
	 * Register all of the hooks related to the public-facing functionality
	 * of the plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function define_public_hooks()
	{

		$plugin_public = new Wpmu_Client_Public($this->get_plugin_name(), $this->get_version());

		//$this->loader->add_action( 'wp_enqueue_scripts', $plugin_public, 'enqueue_styles' );
		//$this->loader->add_action( 'wp_enqueue_scripts', $plugin_public, 'enqueue_scripts' );

	}

	/**
	 * Run the loader to execute all of the hooks with WordPress.
	 *
	 * @since    1.0.0
	 */
	public function run()
	{
		$this->loader->run();
	}

	/**
	 * The name of the plugin used to uniquely identify it within the context of
	 * WordPress and to define internationalization functionality.
	 *
	 * @since     1.0.0
	 * @return    string    The name of the plugin.
	 */
	public function get_plugin_name()
	{
		return $this->plugin_name;
	}

	/**
	 * The reference to the class that orchestrates the hooks with the plugin.
	 *
	 * @since     1.0.0
	 * @return    Wpmu_Client_Loader    Orchestrates the hooks of the plugin.
	 */
	public function get_loader()
	{
		return $this->loader;
	}

	/**
	 * Retrieve the version number of the plugin.
	 *
	 * @since     1.0.0
	 * @return    string    The version number of the plugin.
	 */
	public function get_version()
	{
		return $this->version;
	}
}
