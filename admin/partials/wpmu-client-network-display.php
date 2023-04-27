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
class WPMUClientNetworkConfig
{

	private $wpmu_config_options;

	/**
	 * This will be used for the SubMenu URL in the settings page and to verify which variables to save.
	 *
	 * @var string
	 */
	protected $settings_slug = 'wpmu_client_network_settings';

	public function __construct()
	{
		add_action('network_admin_menu', [$this, 'wpmu_config_add_plugin_page']);
		add_action('admin_init', [$this, 'wpmu_config_page_init']);
		add_action('network_admin_edit_' . $this->settings_slug . '-update', [$this, 'wpmu_save_settings']);
	}

	/**
	 * Static Factory method.
	 *
	 * You can GET an instance of this class by calling `$a = Settings_Page::get_instance();`
	 *
	 * @return self
	 */
	public static function get_instance(): self
	{
		static $obj;
		return isset($obj) ? $obj : $obj = new self();
	}

	public function wpmu_config_add_plugin_page()
	{
		add_submenu_page(
			'settings.php',
			'DRB.MKT GEN', // page_title
			'DRB.MKT GEN', // menu_title
			'manage_network_options', // capability
			$this->settings_slug . '-page', // menu_slug
			[$this, 'wpmu_config_create_admin_page'], // function
		);
	}

	public function wpmu_save_settings()
	{	
		
		// Array of posted values
		$posted = $_POST[$this->settings_slug . '-page'];

		// Our fields hard-coded
		$fields = [
			"network_local_export_path",
			"ftp_user",
			"ftp_host",
			"ftp_pass",
			"ftp_port",
			"remote_ftp_path",
			"clear_path",
		];

		// Loop our fields and match posted values to update records
		foreach ( $fields as $option) {
			
			if(isset($posted[$option]) ){
				
				update_site_option($option, $posted[$option]);
				if( $option == 'clear_path') update_site_option($option, 1);

			} else {
				delete_site_option($option);
			}

		}

		wp_safe_redirect(
			add_query_arg(
				array(
					'page' => $this->settings_slug . '-page',
					'updated' => true,
				),
				network_admin_url('settings.php')
			)
		);
		exit;
	}

	public function wpmu_config_create_admin_page()
	{
		if (isset($_GET['updated'])) : ?>
			<div id="message" class="updated notice is-dismissible">
				<p><?php esc_html_e('Options Saved', 'wpmu-config'); ?></p>
			</div>
		<?php endif; ?>

		<div class="wrap">
			<h2><?php echo esc_attr(get_admin_page_title()); ?></h2>
			<p>Página de configurações para o Gerador de Sites da DRB.MKT</p>
			<?php settings_errors(); ?>

			<form method="post" action="edit.php?action=<?php echo esc_attr($this->settings_slug); ?>-update">
				<?php
				settings_fields($this->settings_slug . '-page');
				do_settings_sections( $this->settings_slug . '-page' );
				submit_button();
				?>
			</form>
		</div>
<?php }

	/**
	 * Html after the new section title.
	 *
	 * @return void
	 */
	public function section_first()
	{
		esc_html_e('Intro text to the first section.', 'multisite-settings');
	}

	public function wpmu_config_page_init()
	{
		register_setting(
			$this->settings_slug . '_page', // option_group
			'wpmu_config_option_name', // option_name
			[$this, 'wpmu_config_sanitize'] // sanitize_callback
		);

		add_settings_section(
			'wpmu_config_setting_section', // id
			'Configurações indiviuais para este site', // title
			[$this, 'wpmu_config_section_info'], // callback
			$this->settings_slug . '-page' // page
		);

		add_settings_field(
			'network_local_export_path', // id
			'Pasta padrão para exportação dos sites', // title
			[$this, 'network_local_export_path_callback'], // callback
			$this->settings_slug . '-page', // page
			'wpmu_config_setting_section' // section
		);

		add_settings_field(
			'ftp_user', // id
			'Usuário FTP para conexão com servidor remoto', // title
			[$this, 'ftp_user_callback'], // callback
			$this->settings_slug . '-page', // page
			'wpmu_config_setting_section' // section
		);

		add_settings_field(
			'ftp_host', // id
			'Endereço FTP remoto', // title
			[$this, 'ftp_host_callback'], // callback
			$this->settings_slug . '-page', // page
			'wpmu_config_setting_section' // section
		);

		add_settings_field(
			'ftp_pass', // id
			'Senha de usuário FTP (em branco)', // title
			[$this, 'ftp_pass_callback'], // callback
			$this->settings_slug . '-page', // page
			'wpmu_config_setting_section' // section
		);

		add_settings_field(
			'ftp_port', // id
			'Porta de FTP (21 por padrão)', // title
			[$this, 'ftp_port_callback'], // callback
			$this->settings_slug . '-page', // page
			'wpmu_config_setting_section' // section
		);

		add_settings_field(
			'remote_ftp_path', // id
			'Caminho FTP para exportação remota', // title
			[$this, 'remote_ftp_path_callback'], // callback
			$this->settings_slug . '-page', // page
			'wpmu_config_setting_section' // section
		);

		add_settings_field(
			'clear_path', // id
			'Limpar diretório remoto antes da exportação?', // title
			[$this, 'clear_path_callback'], // callback
			$this->settings_slug . '-page', // page
			'wpmu_config_setting_section' // section
		);
	}

	public function wpmu_config_sanitize($input)
	{
		$sanitary_values = array();
		if (isset($input['network_local_export_path'])) {
			$sanitary_values['network_local_export_path'] = sanitize_text_field($input['network_local_export_path']);
		}

		if (isset($input['ftp_user'])) {
			$sanitary_values['ftp_user'] = sanitize_text_field($input['ftp_user']);
		}

		if (isset($input['ftp_host'])) {
			$sanitary_values['ftp_host'] = sanitize_text_field($input['ftp_host']);
		}

		if (isset($input['ftp_pass'])) {
			$sanitary_values['ftp_pass'] = $input['ftp_pass'];
		}

		if (isset($input['ftp_port'])) {
			$sanitary_values['ftp_port'] = sanitize_text_field($input['ftp_port']);
		}

		if (isset($input['remote_ftp_path'])) {
			$sanitary_values['remote_ftp_path'] = sanitize_text_field($input['remote_ftp_path']);
		}

		if (isset($input['clear_path'])) {
			$sanitary_values['clear_path'] = $input['clear_path'];
		}

		return $sanitary_values;
	}

	public function wpmu_config_section_info()
	{
	}

	public function network_local_export_path_callback()
	{
		printf(
			'<input class="regular-text" type="text" name="'.$this->settings_slug.'-page[network_local_export_path]" id="network_local_export_path" value="%s">',
			get_site_option('network_local_export_path', '')
		);
	}

	public function ftp_user_callback()
	{
		printf(
			'<input class="regular-text" type="text" name="'.$this->settings_slug.'-page[ftp_user]" id="ftp_user" value="%s">',
			get_site_option('ftp_user', '')
		);
	}

	public function ftp_host_callback()
	{
		printf(
			'<input class="regular-text" type="text" name="'.$this->settings_slug.'-page[ftp_host]" id="ftp_host" value="%s">',
			get_site_option('ftp_host', '')
		);
	}

	public function ftp_pass_callback()
	{
		printf(
			'<input class="regular-text" type="password" name="'.$this->settings_slug.'-page[ftp_pass]" id="ftp_pass" value="%s">',
			get_site_option('ftp_pass', '')
		);
	}

	public function ftp_port_callback()
	{
		printf(
			'<input class="regular-text" type="number" name="'.$this->settings_slug.'-page[ftp_port]" id="ftp_port" value="%s">',
			get_site_option('ftp_port', '')
		);
	}
	
	public function remote_ftp_path_callback()
	{
		printf(
			'<input class="regular-text" type="text" name="'.$this->settings_slug.'-page[remote_ftp_path]" id="remote_ftp_path" value="%s">',
			get_site_option('remote_ftp_path', '')
		);
	}

	public function clear_path_callback()
	{
		printf(
			'<input type="checkbox" name="'.$this->settings_slug.'-page[clear_path]" id="clear_path" %s>',
			(get_site_option('clear_path', '') == 1 ) ? 'checked' : ''
		);
	}
}

if (is_admin()) $wpmu_config = new WPMUClientNetworkConfig();

/* 
 * Retrieve this value with:
 * $wpmu_config_options = get_option( 'wpmu_config_option_name' ); // Array of All Options
 * $network_local_export_path = $wpmu_config_options['network_local_export_path']; // Pasta padrão para exportação dos sites
 * $ftp_user = $wpmu_config_options['ftp_user']; // Usuário FTP para conexão com servidor remoto
 * $ftp_host = $wpmu_config_options['ftp_host']; // Endereço FTP remoto
 * $ftp_pass = $wpmu_config_options['ftp_pass']; // Senha de usuário FTP (em branco)
 * $ftp_port = $wpmu_config_options['ftp_port']; // Porta de FTP (21 por padrão)
 */

?>