<?php

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


class Wpmu_Client_Admin_Page
{

	private $wpmu_client_config;

	public function __construct()
	{
		add_action('admin_menu', array($this, 'wpmu_client_add_plugin_page'));
		add_action('admin_init', array($this, 'wpmu_client_page_init'));
	}

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

	public function wpmu_client_create_admin_page()
	{
		$this->wpmu_client_config = get_option('wpmu_client_config'); ?>

		<div class="wrap">
			<h2>WPMU Client Configurações</h2>
			<p>Página de configurações para o Gerador de Sites da DRB.MKT</p>
			<?php settings_errors(); ?>

			<form method="post" action="options.php">
				<?php
				settings_fields('wpmu_client_config');
				do_settings_sections('wpmu-client-config-admin');
				submit_button();
				?>
			</form>
		</div>
<?php }

	public function wpmu_client_page_init()
	{
		register_setting(
			'wpmu_client_config', // option_group
			'wpmu_client_config', // option_name
			array($this, 'wpmu_client_sanitize') // sanitize_callback
		);

		add_settings_section(
			'wpmu_client_general_section', // id
			'Configurações indiviuais para este site', // title
			array($this, 'wpmu_client_section_info'), // callback
			'wpmu-client-config-admin' // page
		);

		add_settings_section(
			'wpmu_client_upload', // id
			'Exportação', // title
			array($this, 'wpmu_client_section_info'), // callback
			'wpmu-client-config-admin' // page
		);

		add_settings_field(
			'export_log', // id
			'Log de exportação', // title
			array($this, 'export_log_callback'), // callback
			'wpmu-client-config-admin', // page
			'wpmu_client_upload' // section
		);

		add_settings_field(
			'export_button', // id
			'Iniciar Exportação', // title
			array($this, 'export_button_callback'), // callback
			'wpmu-client-config-admin', // page
			'wpmu_client_upload' // section
		);

		add_settings_field(
			'local_path', // id
			'Pasta padrão para exportação dos sites', // title
			array($this, 'export_local_back'), // callback
			'wpmu-client-config-admin', // page
			'wpmu_client_general_section' // section
		);

		add_settings_field(
			'ftp_user', // id
			'Usuário FTP para conexão com servidor remoto', // title
			array($this, 'ftp_user_callback'), // callback
			'wpmu-client-config-admin', // page
			'wpmu_client_general_section' // section
		);

		add_settings_field(
			'ftp_host', // id
			'Endereço FTP remoto', // title
			array($this, 'ftp_host_callback'), // callback
			'wpmu-client-config-admin', // page
			'wpmu_client_general_section' // section
		);

		add_settings_field(
			'ftp_pass', // id
			'Senha de usuário FTP (em branco)', // title
			array($this, 'ftp_pass_callback'), // callback
			'wpmu-client-config-admin', // page
			'wpmu_client_general_section' // section
		);

		add_settings_field(
			'ftp_port', // id
			'Porta de FTP (21 por padrão)', // title
			array($this, 'ftp_port_callback'), // callback
			'wpmu-client-config-admin', // page
			'wpmu_client_general_section' // section
		);
	}

	public function wpmu_client_sanitize($input)
	{
		$sanitary_values = array();
		if (isset($input['local_path'])) {
			$sanitary_values['local_path'] = sanitize_text_field($input['local_path']);
		}

		if (isset($input['ftp_user'])) {
			$sanitary_values['ftp_user'] = sanitize_text_field($input['ftp_user']);
		}

		if (isset($input['ftp_host'])) {
			$sanitary_values['ftp_host'] = sanitize_text_field($input['ftp_host']);
		}

		if (isset($input['ftp_pass'])) {
			$sanitary_values['ftp_pass'] = sanitize_text_field($input['ftp_pass']);
		}

		if (isset($input['ftp_port'])) {
			$sanitary_values['ftp_port'] = sanitize_text_field($input['ftp_port']);
		}

		return $sanitary_values;
	}

	public function wpmu_client_section_info()
	{
	}

	public function export_button_callback()
	{
		printf(
			'<input class="button button-secondary" type="button" name="wpmu_client_config[export_button]" id="export_button" value="%s">',
			"Inicar Exportação"
		);

		printf(
			'<input type="hidden" name="wpmu_client_config[blog_id]" id="blog_id" value="%s" />',
			get_current_blog_id()
		);

	}

	public function export_log_callback()
	{

		printf(
			'<pre name="wpmu_client_config[export_log]" id="export_log">%s</pre>',
			'O log aparecerá aqui...'
		);
	}

	public function export_local_back()
	{
		printf(
			'<input class="regular-text" type="text" name="wpmu_client_config[local_path]" id="local_path" value="%s"><pre id="return"></pre>',
			isset($this->wpmu_client_config['local_path']) ? esc_attr($this->wpmu_client_config['local_path']) : ''
		);
	}

	public function ftp_user_callback()
	{
		printf(
			'<input class="regular-text" type="text" name="wpmu_client_config[ftp_user]" id="ftp_user" value="%s">',
			isset($this->wpmu_client_config['ftp_user']) ? esc_attr($this->wpmu_client_config['ftp_user']) : ''
		);
	}

	public function ftp_host_callback()
	{
		printf(
			'<input class="regular-text" type="text" name="wpmu_client_config[ftp_host]" id="ftp_host" value="%s">',
			isset($this->wpmu_client_config['ftp_host']) ? esc_attr($this->wpmu_client_config['ftp_host']) : ''
		);
	}

	public function ftp_pass_callback()
	{
		printf(
			'<input class="regular-text" type="password" name="wpmu_client_config[ftp_pass]" id="ftp_pass" value="%s">',
			isset($this->wpmu_client_config['ftp_pass']) ? esc_attr($this->wpmu_client_config['ftp_pass']) : ''
		);
	}

	public function ftp_port_callback()
	{
		printf(
			'<input class="regular-text" type="number" name="wpmu_client_config[ftp_port]" id="ftp_port" value="%s">',
			isset($this->wpmu_client_config['ftp_port']) ? esc_attr($this->wpmu_client_config['ftp_port']) : ''
		);
	}
}

if (is_admin()) $wpmu_client_config = new Wpmu_Client_Admin_Page();

/* 
 * Retrieve this value with:
 * $wpmu_client_config = get_option( 'wpmu_client_config' ); // Array of All Options
 * $local_path = $wpmu_client_config['local_path']; // Pasta padrão para exportação dos sites
 * $ftp_user = $wpmu_client_config['ftp_user']; // Usuário FTP para conexão com servidor remoto
 * $ftp_host = $wpmu_client_config['ftp_host']; // Endereço FTP remoto
 * $ftp_pass = $wpmu_client_config['ftp_pass']; // Senha de usuário FTP (em branco)
 * $ftp_port = $wpmu_client_config['ftp_port']; // Porta de FTP (21 por padrão)
 */

?>