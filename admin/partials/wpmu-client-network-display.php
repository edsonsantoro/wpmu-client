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
class Wpmu_Client_Network_Config
{

	/**
	 * This will be used for the SubMenu URL in the settings page and to verify which variables to save.
	 *
	 * @var string
	 */
	protected $settings_slug = 'wpmu_client_network_config';

	protected $plugin_name;

	public function __construct(string $plugin_name)
	{
		$this->plugin_name = $plugin_name;
		add_action('network_admin_menu', [$this, 'wpmu_config_add_plugin_page']);
		add_action('admin_init', [$this, 'wpmu_config_page_init']);
		add_action('network_admin_edit_' . $this->settings_slug . '-update', [$this, 'wpmu_network_save_settings'], 50);
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

	public function wpmu_network_save_settings()
	{

		// Array of posted values
		$posted = $_POST[$this->settings_slug . '-page'];

		// Our fields hard-coded defaults
		$options = [
			"local_path" => './static',
			"ftp_user" => '',
			"ftp_host" => '',
			"ftp_pass" => '',
			"ftp_port" => 21,
			"remote_ftp_path" => '',
			"clear_path"  => 0,
			"clear_local_path" => 0,
			"export_dir_created" => 0
		];


		// Loop our options and match posted values to update records later
		foreach ($options as $option => $value) {
			if (isset($posted[$option])) {

				// Add posted values to our array
				$options[$option] = $posted[$option];

				// set checkbox values
				if ($option == 'clear_path' || $option == 'clear_local_path') $options[$option] = 1;

				// local path corrections
				if ($option == 'local_path') {
					if (substr($posted['local_path'], 0, 1) !== '/') {
						$options[$option] = ABSPATH . $posted[$option];
					} else if ( substr($posted['local_path'], 0, 2) == './' ) {
						$options[$option] = ABSPATH . substr($posted[$option], 2, strlen($posted[$option]));
					}
				}
			}
		}

		// if dir is not created
		if (!is_dir($options['local_path'])) {
			$output = null;
			exec('mkdir -v -p ' . $options['local_path'], $output); // create dir
			if (isset($output[0]) && strstr($output[0], 'created directory')) {
				$options['export_dir_created'] = 1;
			}
		} else {
			// Dir already created
			$options['export_dir_created'] = 1;
		}

		$updated = update_site_option($this->settings_slug, $options);

		wp_safe_redirect(
			add_query_arg(
				array(
					'page' => $this->settings_slug . '-page',
					'updated' => $updated,
				),
				network_admin_url('settings.php')
			)
		);
		exit;
	}

	public function check_typed_directory()
	{
		$path = $_POST['path'];

		if ($_POST['is_client']) {
			$local_path = get_site_option($this->settings_slug, ['local_path' => './static'])['local_path'];
			$path = $local_path . '/' . $path;
		}

		if (is_dir($path)) {
			exec('ls ' . $path, $out);
			if (count($out) == 0) $out[0] = "Diretório vazio";
			echo "Cuidado. O diretório ou arquivo " . $path . " já existe. Tem certeza que deseja exportar para esta pasta? O conteúdo é o seguinte: <br>";
			foreach ($out as $key => $val) {
				echo $val . "<br>";
			}
		} else {
			echo "O diretório " . $path . " NÃO existe e SERÁ CRIADO quando salvar as opções.";
		}
	}

	public function wpmu_config_create_admin_page()
	{
		if (isset($_GET['updated'])) : ?>
			<div id="message" class="updated notice is-dismissible">
				<p><?php esc_html_e('Options Saved', $this->plugin_name); ?></p>
			</div>
		<?php endif; ?>

		<div class="wrap">
			<h2><?php echo esc_attr(get_admin_page_title()); ?></h2>
			<p>Página de configurações para o Gerador de Sites da DRB.MKT</p>
			<?php settings_errors(); ?>

			<form method="post" action="edit.php?action=<?php echo esc_attr($this->settings_slug); ?>-update">
				<?php
				settings_fields($this->settings_slug . '-page');
				do_settings_sections($this->settings_slug . '-page');
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
			'wpmu-client-config', // option_name
			[$this, 'wpmu_config_sanitize'] // sanitize_callback
		);

		add_settings_section(
			'wpmu_config_setting_section', // id
			'Configurações de rede para exportação de todos os sites', // title
			[$this, 'wpmu_config_section_info'], // callback
			$this->settings_slug . '-page' // page
		);

		add_settings_field(
			'local_path', // id
			'Pasta padrão para exportação dos sites', // title
			[$this, 'local_path_callback'], // callback
			$this->settings_slug . '-page', // page
			'wpmu_config_setting_section' // section
		);

		// add_settings_field(
		// 	'ftp_user', // id
		// 	'Usuário FTP para conexão com servidor remoto', // title
		// 	[$this, 'ftp_user_callback'], // callback
		// 	$this->settings_slug . '-page', // page
		// 	'wpmu_config_setting_section' // section
		// );

		// add_settings_field(
		// 	'ftp_host', // id
		// 	'Endereço FTP remoto', // title
		// 	[$this, 'ftp_host_callback'], // callback
		// 	$this->settings_slug . '-page', // page
		// 	'wpmu_config_setting_section' // section
		// );

		// add_settings_field(
		// 	'ftp_pass', // id
		// 	'Senha de usuário FTP (em branco)', // title
		// 	[$this, 'ftp_pass_callback'], // callback
		// 	$this->settings_slug . '-page', // page
		// 	'wpmu_config_setting_section' // section
		// );

		// add_settings_field(
		// 	'ftp_port', // id
		// 	'Porta de FTP (21 por padrão)', // title
		// 	[$this, 'ftp_port_callback'], // callback
		// 	$this->settings_slug . '-page', // page
		// 	'wpmu_config_setting_section' // section
		// );

		// add_settings_field(
		// 	'remote_ftp_path', // id
		// 	'Caminho FTP para exportação remota', // title
		// 	[$this, 'remote_ftp_path_callback'], // callback
		// 	$this->settings_slug . '-page', // page
		// 	'wpmu_config_setting_section' // section
		// );

		add_settings_field(
			'clear_path', // id
			'Limpar diretório remoto antes da exportação?', // title
			[$this, 'clear_path_callback'], // callback
			$this->settings_slug . '-page', // page
			'wpmu_config_setting_section' // section
		);

		add_settings_field(
			'clear_local_path', // id
			'Limpar diretório local antes da exportação?', // title
			[$this, 'clear_local_path_callback'], // callback
			$this->settings_slug . '-page', // page
			'wpmu_config_setting_section' // section
		);
	}

	public function wpmu_config_sanitize($input)
	{
		$sanitary_values = array();

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

		if (isset($input['clear_local_path'])) {
			$sanitary_values['clear_local_path'] = $input['clear_local_path'];
		}


		return $sanitary_values;
	}

	public function wpmu_config_section_info()
	{
	}

	public function local_path_callback()
	{
		$opts = get_site_option($this->settings_slug, ['local_path' => './']);
		printf(
			'<input class="regular-text" type="text" name="' . $this->settings_slug . '-page[local_path]" id="local_path" placeholder="./" value="%s"><pre id="return">%s</pre>',
			$opts['local_path'],
			''
		);
	}

	public function ftp_user_callback()
	{
		printf(
			'<input class="regular-text" type="text" name="' . $this->settings_slug . '-page[ftp_user]" id="ftp_user" value="%s">',
			get_site_option($this->settings_slug, ['ftp_user' => ''])['ftp_user']
		);
	}

	public function ftp_host_callback()
	{
		printf(
			'<input class="regular-text" type="text" name="' . $this->settings_slug . '-page[ftp_host]" id="ftp_host" value="%s">',
			get_site_option($this->settings_slug, ['ftp_host' => ''])['ftp_host']
		);
	}

	public function ftp_pass_callback()
	{
		printf(
			'<input class="regular-text" type="password" name="' . $this->settings_slug . '-page[ftp_pass]" id="ftp_pass" value="%s">',
			get_site_option($this->settings_slug, ['ftp_pass' => ''])['ftp_pass']
		);
	}

	public function ftp_port_callback()
	{
		printf(
			'<input class="regular-text" type="number" name="' . $this->settings_slug . '-page[ftp_port]" id="ftp_port" value="%s">',
			get_site_option($this->settings_slug, ['ftp_port' => ''])['ftp_port']
		);
	}

	public function remote_ftp_path_callback()
	{
		printf(
			'<input class="regular-text" type="text" name="' . $this->settings_slug . '-page[remote_ftp_path]" id="remote_ftp_path" value="%s">',
			get_site_option($this->settings_slug, ['remote_ftp_path' => ''])['remote_ftp_path']
		);
	}

	public function clear_path_callback()
	{
		printf(
			'<input type="checkbox" name="' . $this->settings_slug . '-page[clear_path]" id="clear_path" %s>',
			get_site_option($this->settings_slug, ['clear_path' => ''])['clear_path']
		);
	}

	public function clear_local_path_callback()
	{
		printf(
			'<input type="checkbox" name="' . $this->settings_slug . '-page[clear_local_path]" id="clear_local_path" %s>',
			get_site_option($this->settings_slug, ['clear_local_path' => false])['clear_local_path']
		);
	}
}

?>