<?php

namespace Wpmu_Client;

use DateTime;

/**
 * Provide a admin area view for the plugin
 *
 * This file is used to markup the admin-facing aspects of the plugin.
 *
 * @link       https://santoro.studio
 * @since      1.0.0
 *
 * @package    Wpmu_Client
 * @subpackage Wpmu_Client/admin/partials
 */


class Admin_Settings_Page
{
	/**
	 * Plugin name
	 * @var string
	 */
	protected $plugin_name;

	protected $has_actions;

	protected $reference;

	/**
	 * This will be used for the SubMenu URL in the settings page and to verify which variables to save.
	 *
	 * @var string
	 */
	protected $blog_settings_slug;

	protected $network_settings_slug;

	public function __construct(string $plugin_name, string $network_settings_slug, string $blog_settings_slug)
	{
		$this->network_settings_slug = $network_settings_slug;
		$this->blog_settings_slug = $blog_settings_slug;
		$this->plugin_name = $plugin_name;
	}

	/**
	 * Add single site menu item
	 */
	public function wpmu_client_add_plugin_page()
	{
		add_options_page(
			'DRB.MKT GEN', // page_title
			'DRB.MKT GEN', // menu_title
			'manage_options', // capability
			'wpmu-client-config', // menu_slug
			array($this, 'wpmu_client_create_admin_page') // function
		);
	}

	/**
	 * Render the page
	 */
	public function wpmu_client_create_admin_page()
	{

?>

		<div class="wrap">
			<h2>WPMU Client Configurações</h2>
			<p>Página de configurações para o Gerador de Sites da DRB.MKT</p>
			<?php settings_errors(); ?>

			<form method="post" action="options.php">
				<?php
				settings_fields($this->blog_settings_slug);
				do_settings_sections($this->blog_settings_slug . '-admin');
				?>
			</form>
		</div>
<?php }

	/**
	 * Render single site settings page
	 */
	public function wpmu_client_page_init()
	{
		register_setting(
			$this->blog_settings_slug, // option_group
			$this->blog_settings_slug, // option_name
			array($this, 'wpmu_client_sanitize') // sanitize_callback
		);

		add_settings_section(
			'wpmu_client_upload', // id
			'Envio', // title
			array($this, 'wpmu_client_section_info'), // callback
			$this->blog_settings_slug . '-admin' // page
		);

		add_settings_field(
			'show_config', // id
			'Ver Configuração de Envio', // title
			array($this, 'show_config_callback'), // callback
			$this->blog_settings_slug . '-admin', // page
			'wpmu_client_upload' // section
		);

		add_settings_field(
			'export_log', // id
			'Log de envio', // title
			array($this, 'export_log_callback'), // callback
			$this->blog_settings_slug . '-admin', // page
			'wpmu_client_upload' // section
		);

		add_settings_field(
			'export_button', // id
			'Iniciar Envio', // title
			array($this, 'export_button_callback'), // callback
			$this->blog_settings_slug . '-admin', // page
			'wpmu_client_upload' // section
		);
	}

	public function get_export_actions()
	{

		$this->has_actions = as_has_scheduled_action('wpmu_schedule_export');

		$actions = as_get_scheduled_actions();
		$blog_id = get_current_blog_id();

		foreach ($actions as $key => $action) {
			$args = $action->get_args();
			if (isset($args['blog_id']) && $args['blog_id'] == $blog_id && isset($args['timestamp'])) {
				$this->reference = $args['timestamp'];
			}
		}
	}

	public function wpmu_client_sanitize($input)
	{
		$sanitary_values = array();
		if (isset($input['local_path'])) {
			$sanitary_values['local_path'] = sanitize_text_field($input['local_path']);
		}

		return $sanitary_values;
	}

	public function wpmu_client_section_info()
	{
	}

	public function export_button_callback()
	{
		printf(
			'<input class="button button-secondary" type="button" name="' . $this->blog_settings_slug . '[export_button]" id="export_button" value="%s">',
			"Iniciar Envio"
		);

		printf(
			'<input type="hidden" name="' . $this->blog_settings_slug . '[blog_id]" id="blog_id" value="%s" />',
			get_current_blog_id()
		);

		printf(
			'<input type="hidden" name="' . $this->blog_settings_slug . '[reference]" id="reference" value="%s" />',
			$this->reference
		);

		printf(
			'<input type="hidden" name="' . $this->blog_settings_slug . '[finished]" id="finished" value="%s" />',
			($this->has_actions) ? 'false' : 'true'
		);
	}

	public function show_config_callback()
	{
		printf(
			'<a href="%s" class="button button-secondary" type="button" name="' . $this->blog_settings_slug . '[show_config]" id="show_config" value="">%s</a>',
			add_query_arg(
				array(
					'page' => 'genpage',
					'id' => get_current_blog_id()
				),
				network_admin_url('sites.php')
			),
			__("Mostrar Configuração", $this->plugin_name)
		);
	}

	public function export_log_callback()
	{
		$content = 'O log aparecerá aqui...';
		if (!$this->has_actions && !empty($this->reference)) {
			$blog_id = get_current_blog_id();
			$export_path = get_blog_option($blog_id, $this->blog_settings_slug . "_export_path");
			$reference = new DateTime($this->reference);
			$reference = $reference->format('j-M-Y-H\h-i\m-s\s');
			if (is_file($export_path . "/logs/transfer-" . $reference)) {
				$content = file_get_contents($export_path . "/logs/transfer-" . $reference);
			}
		}

		printf(
			'<pre name="' . $this->blog_settings_slug . '[export_log]" id="export_log">%s</pre>',
			$content
		);
	}

	public function export_local_back()
	{
		printf(
			'<input class="regular-text" type="text" name="' . $this->blog_settings_slug . '[local_path]" id="local_path" value="%s"><pre id="return"></pre>',
			get_option($this->blog_settings_slug . "_local_path", "")
		);
	}

	public function ftp_user_callback()
	{
		printf(
			'<input class="regular-text" type="text" name="' . $this->blog_settings_slug . '[ftp_user]" id="ftp_user" value="%s">',
			get_option($this->blog_settings_slug . "_ftp_user", "")
		);
	}

	public function ftp_host_callback()
	{
		printf(
			'<input class="regular-text" type="text" name="' . $this->blog_settings_slug . '[ftp_host]" id="ftp_host" value="%s">',
			get_option($this->blog_settings_slug . "_ftp_host", "")
		);
	}

	public function ftp_pass_callback()
	{
		printf(
			'<input class="regular-text" type="password" name="' . $this->blog_settings_slug . '[ftp_pass]" id="ftp_pass" value="%s">',
			get_option($this->blog_settings_slug . "_ftp_pass", "")
		);
	}

	public function ftp_port_callback()
	{
		printf(
			'<input class="regular-text" type="number" name="' . $this->blog_settings_slug . '[ftp_port]" id="ftp_port" value="%s">',
			get_option($this->blog_settings_slug . "_ftp_port", "")
		);
	}
}

?>