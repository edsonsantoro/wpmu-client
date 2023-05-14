<?php

namespace Wpmu_Client;

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
class Network_Settings_Page
{

	/**
	 * This will be used for the SubMenu URL in the settings page and to verify which variables to save.
	 *
	 * @var string
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
	 * Plugin name
	 * @var string
	 */
	protected $plugin_name;

	/**
	 * The version of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $version    The current version of this plugin.
	 */
	private $version;

	/**
	 * The options of the network
	 * @since    1.0.0
	 * @access   private
	 * @var      array    $options    Options of the network
	 */
	protected $network_options;

	public function __construct(string $plugin_name, string $version, string $network_settings_slug, string $blog_settings_slug)
	{
		$this->plugin_name = $plugin_name;
		$this->version = $version;
		$this->network_settings_slug = $network_settings_slug;
		$this->blog_settings_slug = $blog_settings_slug;
		$this->network_options = [
			"local_path" => "./static",
			"clear_path"  => 0,
			"clear_local_path" => 0,
			"export_dir_created" => 0,
			"version" => $this->version
		];
	}

	/**
	 * Add our network plugin menu item to the network admin
	 */
	public function add_network_menu_item()
	{

		add_submenu_page(
			'settings.php',
			'DRB.MKT GEN',
			'DRB.MKT GEN',
			'manage_network_options',
			$this->network_settings_slug . '-page',
			[$this, 'render_network_settings_page'],
		);
	}

	/**
	 * Render network wide plugin settings screen
	 */
	public function render_network_settings_page()
	{
		if (isset($_GET['updated'])) : ?>
			<div id="message" class="updated notice is-dismissible">
				<p><?php esc_html_e('Opções Salvas', $this->plugin_name); ?></p>
			</div>
		<?php endif; ?>

		<div class="wrap">
			<h2><?php echo esc_attr(get_admin_page_title()); ?></h2>
			<p><?php __("Página de configurações para o Gerador de Sites da DRB.MKT", $this->plugin_name); ?></p>
			<?php settings_errors(); ?>

			<form method="post" action="edit.php?action=site_update">
				<?php
				settings_fields($this->network_settings_slug . '-page');
				do_settings_sections($this->network_settings_slug . '-page');
				submit_button();
				?>
			</form>
		</div>
<?php
	}

	/**
	 * Save network screen options
	 */
	public function update_network_settings()
	{

		// Array of posted values
		$posted = $_POST[$this->network_settings_slug];

		// Our fields hard-coded defaults
		$options = $this->network_options;

		// Loop our options and match posted values to update records later
		foreach ($options as $option => $value) {
			if (isset($posted[$option])) {

				// Add posted values to our array
				$options[$option] = $posted[$option];

				// set checkbox values
				if ($option == 'clear_path' || $option == 'clear_local_path') $options[$option] = 1;

				// local path corrections
				if ($option == 'local_path') {

					// Se o caminho for absoluto
					if (path_is_absolute($posted[$option])) {
						$options[$option] = $posted[$option];
					} else if (!path_is_absolute($posted[$option]) && substr($posted[$option], 0, 2) == "./") {
						// Se for relativo, então trocamos pela raíz do WP.
						$options[$option] = ABSPATH . substr($posted[$option], 2);
					} else if (substr($posted[$option], 0, 2) != "./") {
						$options[$option] = ABSPATH . $posted[$option];
					}

					// Se terminar com / precisamos remover para não ter / duplicados e erros de caminho.
					if (substr($options[$option], strlen($options[$option]) - 1, 1) == "/") {
						$options[$option] = substr($options[$option], 0, strlen($options[$option]) - 1);
					}

					// Tratando nome de pasta, usamos o sanitize_user pois ele permite alguns caracteres necessários como /
					$options[$option] = sanitize_user($options[$option]);
				}

				//update_site_option($this->network_settings_slug . "_" . $option, $options[$option]);
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

		$updated = update_site_option($this->network_settings_slug, $options);

		$prof = get_site_option($this->network_settings_slug);

		if($prof != $options){
			update_site_option($this->network_settings_slug, $options);
		}

		wp_safe_redirect(
			add_query_arg(
				array(
					'page' => $this->network_settings_slug . '-page',
					'updated' => $updated,
				),
				network_admin_url('settings.php')
			)
		);
		exit;
	}





	/**
	 * Html after the new section title.
	 *
	 * @return void
	 */
	public function section_info()
	{
		esc_html_e('Intro text to the first section.', 'multisite-settings');
	}





	/**
	 * Add fields and sections to the page
	 */
	public function init_network_page()
	{
		register_setting(
			$this->network_settings_slug . '_page',
			'wpmu_client_config',
			[$this, 'sanitize_fields']
		);

		add_settings_section(
			'wpmu_config_setting_section',
			__('Configurações de rede para exportação de todos os sites', $this->plugin_name),
			[$this, 'section_info'],
			$this->network_settings_slug . '-page'
		);

		add_settings_field(
			'local_path',
			__('Pasta padrão para exportação dos sites', $this->plugin_name),
			[$this, 'local_path_callback'],
			$this->network_settings_slug . '-page',
			'wpmu_config_setting_section'
		);

		add_settings_field(
			'clear_path',
			__('Limpar diretório remoto antes da exportação?', $this->plugin_name),
			[$this, 'clear_path_callback'],
			$this->network_settings_slug . '-page',
			'wpmu_config_setting_section'
		);

		add_settings_field(
			'clear_local_path',
			__('Limpar diretório local antes da exportação?', $this->plugin_name),
			[$this, 'clear_local_path_callback'],
			$this->network_settings_slug . '-page',
			'wpmu_config_setting_section'
		);
	}

	/**
	 * Sanitize user inputs
	 * @param array $input The inputs
	 * @return array Sanitized inputs
	 */
	public function sanitize_fields(array $input)
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

	public function local_path_callback()
	{
		$opts = get_site_option($this->network_settings_slug);
		$local_path = (isset($opts['local_path']) && !empty($opts['local_path'])) ? $opts['local_path'] : "";
		printf(
			'<input class="regular-text" type="text" name="' . $this->network_settings_slug . '[local_path]" id="local_path" placeholder="./" value="%s"><pre id="return">%s</pre>',
			$local_path,
			''
		);
	}

	public function clear_local_path_callback()
	{
		$option = get_site_option($this->network_settings_slug, ['clear_local_path' => false]);
		$checked = (isset($option['clear_local_path']) && $option['clear_local_path'] == true) ? "checked" : "";
		printf(
			'<input type="checkbox" name="' . $this->network_settings_slug . '[clear_local_path]" id="clear_local_path" %s>',
			$checked
		);
	}

	public function clear_path_callback()
	{
		$option = get_site_option($this->network_settings_slug, ['clear_local_path' => false]);
		$checked = (isset($option['clear_path']) && $option['clear_path'] == true) ? "checked" : "";
		printf(
			'<input type="checkbox" name="' . $this->network_settings_slug . '[clear_path]" id="clear_path" %s>',
			$checked
		);
	}
}

?>