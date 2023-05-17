<?php

namespace Wpmu_Client;

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
	 * @var      Loader    $loader    Maintains and registers all hooks for the plugin.
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
	 * The network settings slug
	 *
	 * @since		1.0.0
	 * @var 		string
	 * @access		protected
	 */
	protected $network_settings_slug;

	/**
	 * The blog settings slug
	 *
	 * @since		1.0.0
	 * @var 		string
	 * @access		protected
	 */
	protected $blog_settings_slug;

	/**
	 * The admin config instance
	 *
	 * @since		1.0.0
	 * @var 		Admin_Functions
	 * @access		protected
	 */
	protected $admin = null;


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
		$this->blog_settings_slug = 'wpmu_client_blog_settings';
		$this->network_settings_slug = 'wpmu_client_network_settings';
		$this->load_dependencies();
		$this->set_locale();
		$this->define_admin_hooks();
		$this->define_public_hooks();
		$this->check_upgrade();
	}

	/**
	 * Load the required dependencies for this plugin.
	 *
	 * Include the following files that make up the plugin:
	 *
	 * - Loader. Orchestrates the hooks of the plugin.
	 * - i18n. Defines internationalization functionality.
	 * - Admin_Functions. Defines all hooks for the admin area.
	 * - Public_Functions. Defines all hooks for the public side of the site.
	 * - Network_Settings_Page. Defines settings for plugin network configuration
	 * - Notice. Show admin otices.
	 * - Process. Execute PHP process and keep track of
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

		// Set defaults
		$this->can_run = true;

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
		require_once plugin_dir_path(dirname(__FILE__)) . 'admin/partials/wpmu-client-network-settings-page.php';

		/**
		 * The class responsible for the settings page of this plugin for the network.
		 */
		require_once plugin_dir_path(dirname(__FILE__)) . 'admin/partials/wpmu-client-network-tab-page.php';

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
			$this->can_run = false;
		}

		$this->loader = new Loader();
	}

	/**
	 * Define the locale for this plugin for internationalization.
	 *
	 * Uses the i18n class in order to set the domain and to register the hook
	 * with WordPress.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function set_locale()
	{

		$plugin_i18n = new i18n();

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
		$version = $this->get_version();
		$network_settings_slug = $this->get_network_settings_slug();
		$blog_settings_slug = $this->get_blog_settings_slug();
		$plugin_name = $this->get_plugin_name();

		$plugin_admin 		= new Admin_Functions($plugin_name, $version, $network_settings_slug, $blog_settings_slug);
		$blog_admin_page 	= new Admin_Settings_Page($plugin_name, $network_settings_slug, $blog_settings_slug);
		$network_admin_page = new Network_Settings_Page($plugin_name, $version, $network_settings_slug, $blog_settings_slug);
		$network_tab_page	= new Network_Tab_Page($plugin_name, $version, $network_settings_slug, $blog_settings_slug);


		// ---------------- Network Settings Page actions and filters ----------------
		// Add our network plugin menu item
		$this->loader->add_action('network_admin_menu', $network_admin_page, 'add_network_menu_item');
		// Add fields and sections to the page
		$this->loader->add_action('admin_init', $network_admin_page, 'init_network_page');
		// Save our custom fields from the GEN tab on edit site screen
		$this->loader->add_action('network_admin_edit_site_update', $network_admin_page,  'update_network_settings', 50);


		// ---------------- Network Site Edit Tab Tab actions and filters ----------------
		// Add a GEN tab to site info screen
		$this->loader->add_filter('network_edit_site_nav_links', $network_tab_page, 'add_network_site_tab');
		// Save our custom fields from the GEN tab on edit site screen
		$this->loader->add_action('network_admin_edit_gen_update', $network_tab_page,  'save_network_settings', 50);
		// Add our custom page to the tab
		$this->loader->add_action('network_admin_menu', $network_tab_page, 'add_network_tab_page');
		// Render single site settings page
		$this->loader->add_action('admin_init', $network_tab_page, 'init_tab_page');


		// ---------------- Site Settings Page actions and filters ----------------
		// Add single site menu item
		$this->loader->add_action('admin_menu', $blog_admin_page, 'wpmu_client_add_plugin_page');
		// Render single site settings page
		$this->loader->add_action('admin_init', $blog_admin_page, 'wpmu_client_page_init');


		// ---------------- General Admin Functions actions and filters ----------------
		// Display client site field on WPMU New Site screen
		$this->loader->add_action('network_site_new_form', $plugin_admin, 'show_client_field');
		// Display FTP credentials field on WPMU New Site screen
		$this->loader->add_action('network_site_new_form', $plugin_admin, 'show_ftp_credentials_fields');
		// Ajax function to check directory names
		$this->loader->add_action('wp_ajax_check_typed_directory', $plugin_admin, 'check_typed_directory');
		// Register the stylesheets for the admin area.
		$this->loader->add_action('admin_enqueue_scripts', $plugin_admin, 'enqueue_styles', 100);
		// Verify client name when creating new site
		$this->loader->add_action('wp_validate_site_data', $plugin_admin, 'check_new_blog_fields', 10, 3);
		// Save our custom fields data when site is initialized
		$this->loader->add_action('wp_initialize_site', $plugin_admin, 'save_new_blog_options', 10, 2);
		// Add export button to the top bar
		$this->loader->add_action('admin_bar_menu', $plugin_admin, 'add_admin_button', 80);
		// Register the JavaScript for the admin area.
		$this->loader->add_action('admin_enqueue_scripts', $plugin_admin, 'enqueue_scripts');
		// Always set Simply Static options to our defaults when changing blogs
		$this->loader->add_action('switch_blog', $plugin_admin, 'set_ss_options', 10, 3);
		// The Ajax function responsible for exporting static generated sites to remote FTPs
		$this->loader->add_action('wp_ajax_wpmu_init_export', $plugin_admin, 'wpmu_init_export');

		add_action('wp_print_scripts', function(){
			//wp_deregister_script( 'elementor-pro-webpack-runtime' );
			//wp_dequeue_script( 'elementor-pro-webpack-runtime' );
		});

		add_filter('ss_match_tags', function($match_tags){
			$match_tags['div'] = array( 'data-settings' );
			return $match_tags;
		});
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
		//$plugin_public = new Public_Functions($this->get_plugin_name(), $this->get_version());
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
	 * Deactivate this plugin
	 *
	 * @since 1.0.0
	 * @param string 	$message	 A mensagem para mostrar ao usuário
	 * @param string 	$type		 Tipo de mensagem, error ou info
	 * 
	 */
	public function shutdown(string $message = "Plugin desativado", string $type = "error")
	{

		if (empty($message)) {
			$message = __("Plugin desativado", $this->plugin_name);
			new Notice($message, $type, true, true);
		}
		require_once(ABSPATH . 'wp-admin/includes/plugin.php');
		deactivate_plugins("wpmu-client/wpmu-client.php", false, true);
		deactivate_plugins("wpmu-client/wpmu-client.php", false, false);
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
	 * @since     1.0.0
	 * @return    Loader    Orchestrates the hooks of the plugin.
	 */
	public function get_loader()
	{
		return $this->loader;
	}

	/**
	 * Retrieve the version number of the plugin.
	 * @since     1.0.0
	 * @return    string    The version number of the plugin.
	 */
	public function get_version()
	{
		return $this->version;
	}

	/**
	 * [Description for get_network_settings_slug]
	 * @since	1.1.0
	 * @return  string	This plugin's network option slug
	 */
	public function get_network_settings_slug()
	{
		return $this->network_settings_slug;
	}

	/**
	 * [Description for get_blog_settings_slug]
	 * @since	1.1.0
	 * @return  string	This plugin's blog option slug
	 */
	public function get_blog_settings_slug()
	{
		return $this->blog_settings_slug;
	}

	/**
	 * [Description for can_run]
	 * @since	1.0.0
	 * @return 	bool	If plugin can run or not
	 */
	public function can_run()
	{
		return $this->can_run;
	}

	/**
	 * Check if we need to upgrade old options
	 * @since	1.1.0
	 * @return 	void
	 */
	public function check_upgrade()
	{
		$old_opts = get_site_option("wpmu_client_network_config");		
		$opts = get_site_option($this->get_network_settings_slug());
		$act_version = (isset($opts['version'])) ? $opts['version'] : "";
		$version = $this->get_version();

		// Version is not defined, but we are using new slug.
		// So we need to include version option to this new slug
		if($old_opts == false && $act_version == "") {
			$opts['version'] = $this->get_version();
			$this->upgrade_network_options();
		}

		// Versions match, abort
		if ($act_version == $version) return;

		$sites = get_sites();

		// First, upgrade network options
		$this->upgrade_network_options();

		// Loop each site and upgrade their options
		foreach ($sites as $site) {
			$this->upgrade_blog_options($site->blog_id);
		}
	}

	/**
	 * Upgrade network previous options to the newest
	 * @since	1.1.0
	 * @return	bool		If the network option was updated
	 */
	public function upgrade_network_options()
	{

		// This option is on network level
		$old_options = get_site_option("wpmu_client_network_config");

		// If option is not defined check version and adjust, so return it.
		if ($old_options == false) {
			$opts = get_site_option($this->get_network_settings_slug());
			// We have new options, but not the version, upgrade it
			if(!isset($opts['version'])) {
				$opts['version'] = $this->get_version();
				update_site_option($this->get_network_settings_slug(), $opts);
			}
		}

		// Get old options to array.
		$new_options = [
			"local_path" => '',
			"export_dir_created" => 0,
			"clear_path" => 0,
			"clear_local_path" => 0,
			"version" => $this->get_version()
		];

		// Update each option into database
		foreach ($new_options as $key => $value) {
			if (isset($old_options[$key]) && !empty($old_options[$key])) {
				$new_options[$key] = $old_options[$key];
			}
		}

		// Upgrade site options with new options
		$updated = update_site_option($this->network_settings_slug, $new_options);
		if ($updated) {
			delete_site_option("wpmu_client_network_config");
		}

		return $updated;
	}

	/**
	 * Upgrade previous options to the newest
	 * @since	1.1.0
	 * @param 	int		$blog_id	The blog id to work it
	 */
	public function upgrade_blog_options(int $blog_id)
	{

		// Get old options to array.
		$old_options = [
			"client" => get_blog_option($blog_id, "wpmu-client_client", ""),
			"ftp_host" => get_blog_option($blog_id, "wpmu-client_ftp_host", ""),
			"ftp_user" => get_blog_option($blog_id, "wpmu-client_ftp_user", ""),
			"ftp_pass" => get_blog_option($blog_id, "wpmu-client_ftp_pass", ""),
			"ftp_port" => get_blog_option($blog_id, "wpmu-client_ftp_port", ""),
			"ftp_path" => get_blog_option($blog_id, "wpmu-client_ftp_path", ""),
			"folder_created" => get_blog_option($blog_id, "wpmu_folder_created", 0),
			"export_path" => get_blog_option($blog_id, "wpmu_export_path", ""),
			"ftp_clear_remote" => 0
		];

		// Update each option into database
		foreach ($old_options as $key => $value) {
			if (isset($value) && !empty($value)) {

				$updated = update_blog_option($blog_id, $this->blog_settings_slug . "_" . $key, $value);

				// Now that the new value is stored, delete the previous option
				if ($updated) {
					delete_blog_option($blog_id, "wpmu-client_" . $key);
				}

				// Delete other option slugs
				if($key == 'folder_created' || $key == 'export_path') {
					delete_blog_option($blog_id, "wpmu_export_path");
					delete_blog_option($blog_id, "wpmu_folder_created");					
				}
			}
		}
	}
}
