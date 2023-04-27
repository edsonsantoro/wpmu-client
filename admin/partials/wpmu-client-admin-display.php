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


class WPMUClientConfiguraes
{

	private $wpmu_client_configuraes_options;

	public function __construct()
	{
		add_action('admin_menu', array($this, 'wpmu_client_configuraes_add_plugin_page'));
		add_action('admin_init', array($this, 'wpmu_client_configuraes_page_init'));
	}

	public function wpmu_client_configuraes_add_plugin_page()
	{
		add_options_page(
			'DRB.MKT GEN', // page_title
			'DRB.MKT GEN', // menu_title
			'manage_options', // capability
			'wpmu-client-configuraes', // menu_slug
			array($this, 'wpmu_client_configuraes_create_admin_page') // function
		);
	}

	public function wpmu_client_configuraes_create_admin_page()
	{
		$this->wpmu_client_configuraes_options = get_option('wpmu_client_configuraes_option_name'); ?>

		<div class="wrap">
			<h2>WPMU Client Configurações</h2>
			<p>Página de configurações para o Gerador de Sites da DRB.MKT</p>
			<?php settings_errors(); ?>

			<form method="post" action="options.php">
				<?php
				settings_fields('wpmu_client_configuraes_option_group');
				do_settings_sections('wpmu-client-configuraes-admin');
				submit_button();
				?>
			</form>
		</div>
<?php }

	public function wpmu_client_configuraes_page_init()
	{
		register_setting(
			'wpmu_client_configuraes_option_group', // option_group
			'wpmu_client_configuraes_option_name', // option_name
			array($this, 'wpmu_client_configuraes_sanitize') // sanitize_callback
		);

		add_settings_section(
			'wpmu_client_configuraes_setting_section', // id
			'Configurações indiviuais para este site', // title
			array($this, 'wpmu_client_configuraes_section_info'), // callback
			'wpmu-client-configuraes-admin' // page
		);

		add_settings_section(
			'wpmu_client_upload', // id
			'Exportação', // title
			array($this, 'wpmu_client_configuraes_section_info'), // callback
			'wpmu-client-configuraes-admin' // page
		);

		add_settings_field(
			'export_log', // id
			'Log de exportação', // title
			array($this, 'export_log_callback'), // callback
			'wpmu-client-configuraes-admin', // page
			'wpmu_client_upload' // section
		);

		add_settings_field(
			'export_button', // id
			'Iniciar Exportação', // title
			array($this, 'export_button_callback'), // callback
			'wpmu-client-configuraes-admin', // page
			'wpmu_client_upload' // section
		);

		add_settings_field(
			'default_local_export_path', // id
			'Pasta padrão para exportação dos sites', // title
			array($this, 'default_local_export_path_callback'), // callback
			'wpmu-client-configuraes-admin', // page
			'wpmu_client_configuraes_setting_section' // section
		);

		add_settings_field(
			'ftp_user', // id
			'Usuário FTP para conexão com servidor remoto', // title
			array($this, 'ftp_user_callback'), // callback
			'wpmu-client-configuraes-admin', // page
			'wpmu_client_configuraes_setting_section' // section
		);

		add_settings_field(
			'ftp_host', // id
			'Endereço FTP remoto', // title
			array($this, 'ftp_host_callback'), // callback
			'wpmu-client-configuraes-admin', // page
			'wpmu_client_configuraes_setting_section' // section
		);

		add_settings_field(
			'ftp_password', // id
			'Senha de usuário FTP (em branco)', // title
			array($this, 'ftp_password_callback'), // callback
			'wpmu-client-configuraes-admin', // page
			'wpmu_client_configuraes_setting_section' // section
		);

		add_settings_field(
			'ftp_port', // id
			'Porta de FTP (21 por padrão)', // title
			array($this, 'ftp_port_callback'), // callback
			'wpmu-client-configuraes-admin', // page
			'wpmu_client_configuraes_setting_section' // section
		);
	}

	public function wpmu_client_configuraes_sanitize($input)
	{
		$sanitary_values = array();
		if (isset($input['default_local_export_path'])) {
			$sanitary_values['default_local_export_path'] = sanitize_text_field($input['default_local_export_path']);
		}

		if (isset($input['ftp_user'])) {
			$sanitary_values['ftp_user'] = sanitize_text_field($input['ftp_user']);
		}

		if (isset($input['ftp_host'])) {
			$sanitary_values['ftp_host'] = sanitize_text_field($input['ftp_host']);
		}

		if (isset($input['ftp_password'])) {
			$sanitary_values['ftp_password'] = sanitize_text_field($input['ftp_password']);
		}

		if (isset($input['ftp_port'])) {
			$sanitary_values['ftp_port'] = sanitize_text_field($input['ftp_port']);
		}

		return $sanitary_values;
	}

	public function wpmu_client_configuraes_section_info()
	{
	}

	public function export_button_callback()
	{
		printf(
			'<input class="button button-secondary" type="button" name="wpmu_client_configuraes_option_name[export_button]" id="export_button" value="%s">',
			"Inicar Exportação"
		);

		printf(
			'<input type="hidden" name="wpmu_client_configuraes_option_name[blog_id]" id="blog_id" value="%s" />',
			get_current_blog_id()
		);

	}

	public function export_log_callback()
	{

		printf(
			'<pre name="wpmu_client_configuraes_option_name[export_log]" id="export_log">%s</pre>',
			'O log aparecerá aqui...'
		);
	}

	public function default_local_export_path_callback()
	{
		printf(
			'<input class="regular-text" type="text" name="wpmu_client_configuraes_option_name[default_local_export_path]" id="default_local_export_path" value="%s">',
			isset($this->wpmu_client_configuraes_options['default_local_export_path']) ? esc_attr($this->wpmu_client_configuraes_options['default_local_export_path']) : ''
		);
	}

	public function ftp_user_callback()
	{
		printf(
			'<input class="regular-text" type="text" name="wpmu_client_configuraes_option_name[ftp_user]" id="ftp_user" value="%s">',
			isset($this->wpmu_client_configuraes_options['ftp_user']) ? esc_attr($this->wpmu_client_configuraes_options['ftp_user']) : ''
		);
	}

	public function ftp_host_callback()
	{
		printf(
			'<input class="regular-text" type="text" name="wpmu_client_configuraes_option_name[ftp_host]" id="ftp_host" value="%s">',
			isset($this->wpmu_client_configuraes_options['ftp_host']) ? esc_attr($this->wpmu_client_configuraes_options['ftp_host']) : ''
		);
	}

	public function ftp_password_callback()
	{
		printf(
			'<input class="regular-text" type="password" name="wpmu_client_configuraes_option_name[ftp_password]" id="ftp_password" value="%s">',
			isset($this->wpmu_client_configuraes_options['ftp_password']) ? esc_attr($this->wpmu_client_configuraes_options['ftp_password']) : ''
		);
	}

	public function ftp_port_callback()
	{
		printf(
			'<input class="regular-text" type="number" name="wpmu_client_configuraes_option_name[ftp_port]" id="ftp_port" value="%s">',
			isset($this->wpmu_client_configuraes_options['ftp_port']) ? esc_attr($this->wpmu_client_configuraes_options['ftp_port']) : ''
		);
	}
}

if (is_admin()) $wpmu_client_configuraes = new WPMUClientConfiguraes();

/* 
 * Retrieve this value with:
 * $wpmu_client_configuraes_options = get_option( 'wpmu_client_configuraes_option_name' ); // Array of All Options
 * $default_local_export_path = $wpmu_client_configuraes_options['default_local_export_path']; // Pasta padrão para exportação dos sites
 * $ftp_user = $wpmu_client_configuraes_options['ftp_user']; // Usuário FTP para conexão com servidor remoto
 * $ftp_host = $wpmu_client_configuraes_options['ftp_host']; // Endereço FTP remoto
 * $ftp_password = $wpmu_client_configuraes_options['ftp_password']; // Senha de usuário FTP (em branco)
 * $ftp_port = $wpmu_client_configuraes_options['ftp_port']; // Porta de FTP (21 por padrão)
 */

?>