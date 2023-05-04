<?php
require_once(__DIR__ . '/class-wpmu-client-admin-notices.php');
/**
 * The admin-specific functionality of the plugin.
 *
 * @link       https://santoro.studio
 * @since      1.0.0
 *
 * @package    Wpmu_Client
 * @subpackage Wpmu_Client/admin
 */

/**
 * The admin-specific functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the admin-specific stylesheet and JavaScript.
 *
 * @package    Wpmu_Client
 * @subpackage Wpmu_Client/admin
 * @author     Edson Del Santoro <edsonsantoro@gmail.com>
 */
class Wpmu_Client_Admin
{

	/**
	 * The ID of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $plugin_name    The ID of this plugin.
	 */
	private $plugin_name;

	/**
	 * The version of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $version    The current version of this plugin.
	 */
	private $version;

	/**
	 * Initialize the class and set its properties.
	 *
	 * @since    1.0.0
	 * @param      string    $plugin_name       The name of this plugin.
	 * @param      string    $version    The version of this plugin.
	 */
	public function __construct($plugin_name, $version)
	{

		$this->plugin_name = $plugin_name;
		$this->version = $version;
		//add_action('network_admin_edit_update-site', [$this, 'wpmu_save_settings']);

		add_filter('network_edit_site_nav_links', [$this, 'wpmu_new_siteinfo_tab']);
		add_action('network_admin_edit_genupdate',  [$this, 'wpmu_save_settings'], 50);
		add_action('network_admin_menu', [$this, 'wpmu_new_page']);
		add_filter('pre_update_option_wpmu_client_config', [$this, 'myplugin_update_field_foo'], 10, 2);
	}

	public function wpmu_new_siteinfo_tab($tabs)
	{

		$tabs['site-gen'] = array(
			'label' => 'GEN',
			'url' => add_query_arg('page', 'genpage', 'sites.php'),
			'cap' => 'manage_sites'
		);
		return $tabs;
	}

	public function wpmu_new_page()
	{
		add_submenu_page(null, 'Editar site', 'Editar site', 'manage_network_options', 'genpage', [$this, 'wpmu_page_callback']);
	}

	public function wpmu_page_callback()
	{

		// do not worry about that, we will check it too
		$id = absint($_REQUEST['id']);

		$site = get_site($id);
?>
		<div class="wrap">
			<h1 id="edit-site">Editar site: <?php echo $site->blogname ?></h1>
			<p class="edit-site-actions">
				<a href="<?php echo esc_url(get_home_url($id, '/')) ?>">Visitar</a> | <a href="<?php echo esc_url(get_admin_url($id)) ?>">Painel</a>
			</p>
			<?php
			// navigation tabs
			network_edit_site_nav(
				array(
					'blog_id'  => $id,
					'selected' => 'site-gen' // current tab
				)
			);
			?>
			<form method="post" action="edit.php?action=genupdate">
				<?php wp_nonce_field('gen-check' . $id); ?>
				<input type="hidden" name="id" value="<?php echo $id ?>" />
				<?php

				$this->show_client_site_field($id);
				$this->show_ftp_credentials_fields($id);

				?>
				<?php submit_button(); ?>
			</form>
		</div>
	<?php

	}

	public function save_custom_site_fields($new_site, $args = [])
	{
		$id = absint($new_site->blog_id);

		$options = [
			"client" => "",
			"ftp_host" => "",
			"ftp_user" => "",
			"ftp_pass" => "",
			"ftp_port" => "21",
			"ftp_path" => "./mirror",
			"local_path" => "./site",
			"client_dir" => 0
		];

		foreach ($options as $key => $value) {
			if (array_key_exists($key, $_POST['blog'])) {
				if (isset($_POST['blog'][$key]) && !empty($_POST['blog'][$key])) {
					$options[$key] = $_POST['blog'][$key];
				}
			}
		}

		$setup = $this->setup_folder($id);

		$up = update_blog_option($id, 'wpmu_client_config', $options);
	}

	public function wpmu_save_settings()
	{
		$id = absint($_POST['id']);
		$check = check_admin_referer('gen-check' . $id); // nonce check

		$options = [
			"client" => "",
			"ftp_host" => "",
			"ftp_user" => "",
			"ftp_pass" => "",
			"ftp_port" => "21",
			"ftp_path" => "./mirror",
			"client_dir" => 0
		];

		$posted = $_POST['blog'];

		foreach ($options as $option => $value) {

			// local path corrections
			// if ($option == 'ftp_path') {
			// 	if (substr($posted['ftp_path'], 0, 1) !== '/') {
			// 		$options[$option] = ABSPATH . $posted[$option];
			// 	} else if (substr($posted['ftp_path'], 0, 2) == './') {
			// 		$options[$option] = ABSPATH . substr($posted[$option], 2, strlen($posted[$option]));
			// 	}
			// }

			if (isset($posted[$option]) && !empty($posted[$option])) {
				delete_blog_option($id, $this->plugin_name . '_' . $option);
				update_blog_option($id, $this->plugin_name . "_" . $option, $posted[$option]);
			}
		}

		$setup = $this->setup_folder($id);

		wp_safe_redirect(
			add_query_arg(
				array(
					'page' => 'genpage',
					'id' => $id,
					'updated' => 'true'
				),
				network_admin_url('sites.php')
			)
		);
		exit;
	}

	public function myplugin_update_field_foo($val, $old)
	{
		error_log('val: ' . print_r($val, 1));
		error_log('old: ' . print_r($old, 1));
	}

	/**
	 * Register the stylesheets for the admin area.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_styles()
	{

		/**
		 * This function is provided for demonstration purposes only.
		 *
		 * An instance of this class should be passed to the run() function
		 * defined in Wpmu_Client_Loader as all of the hooks are defined
		 * in that particular class.
		 *
		 * The Wpmu_Client_Loader will then create the relationship
		 * between the defined hooks and the functions defined in this
		 * class.
		 */

		wp_enqueue_style($this->plugin_name, plugin_dir_url(__FILE__) . 'css/wpmu-client-admin.css', array(), $this->version, 'all');
	}

	/**
	 * Register the JavaScript for the admin area.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_scripts()
	{

		/**
		 * This function is provided for demonstration purposes only.
		 *
		 * An instance of this class should be passed to the run() function
		 * defined in Wpmu_Client_Loader as all of the hooks are defined
		 * in that particular class.
		 *
		 * The Wpmu_Client_Loader will then create the relationship
		 * between the defined hooks and the functions defined in this
		 * class.
		 */

		wp_enqueue_script($this->plugin_name, plugin_dir_url(__FILE__) . 'js/wpmu-client-admin.js', array('jquery'), $this->version, false);
	}

	/**
	 * Verify new site form if client field is
	 *
	 * @param WP_Error $errors Error object, passed by reference. Will contain validation errors if any occurred.
	 * @param array $data Associative array of complete site data.
	 * @param mixed $old_site The old site object if the data belongs to a site being updated, or null if it is a new site being inserted. Defaults to null
	 *
	 * @return void
	 *
	 */
	public function verify_custom_site_fields(WP_Error $errors, array $data, $old_site)
	{
		if (empty($_POST['blog']['client'])) {
			$errors->add('site_empty_client', __("O campo de cliente não pode estar vazio", $this->plugin_name));
		}

		if (!empty($_POST['blog']['ftp_host'])) {
			if (!$this->is_valid_domain_name($_POST['blog']['ftp_host'])) {
				$errors->add('site_ftp_host_invelid', __("Endereço de FTP Host inválido", $this->plugin_name));
			}
		}
	}

	/**
	 * Display client site field on WPMU New Site screen
	 *
	 * @return void
	 *
	 */
	public function show_client_site_field($id)
	{
		$client = get_blog_option($id, "wpmu-client_client", "");
	?>
		<div class="wrap">
			<h2>Dados Locais do Clientes</h2>
			<p><?php echo __("Dados do cliente e arquivos locais.", $this->plugin_name);  ?></p>
		</div>
		<table class="form-table" role="presentation">
			<tr class="form-field form-required">
				<th scope="row"><label for="client"><?php echo __('Cliente ', $this->plugin_name); ?></label><span class="required">*</span></th>
				<td>
					<input class="wpmu-client" name="blog[client]" type="text" id="client" required autocomplete="on" value="<?php echo $client ?>" />
					<p><?php echo __("Digite o nome do cliente para quem produzirá este novo site. <br><b>ATENÇÃO:</b> Verifique bem o nome, pois o mesmo será usado apra criar diretórios para exportação do código-fonte.", $this->plugin_name);  ?>
					</p>
					<pre id='return'></pre>
				</td>
			</tr>
		</table>
	<?php
	}

	/**
	 * Display FTP credentials field on WPMU New Site screen
	 *
	 * @return void
	 *
	 */
	public function show_ftp_credentials_fields($id)
	{

		$ftp_host = get_blog_option($id, "wpmu-client_ftp_host", "");
		$ftp_user = get_blog_option($id, "wpmu-client_ftp_user", "");
		$ftp_pass = get_blog_option($id, "wpmu-client_ftp_pass", "");
		$ftp_port = get_blog_option($id, "wpmu-client_ftp_port", "");
		$ftp_path = get_blog_option($id, "wpmu-client_ftp_path", "");

	?>
		<div class="wrap">
			<h2><?php echo __('Credenciais FTP', $this->plugin_name); ?></h2>
			<p><?php echo __('Podem ser definidos depois. Estes dados serão usados para definir as configurações de exportação do site do cliente para seu próprio servidor.', $this->plugin_name); ?></p>
		</div>
		<table class="form-table" role="presentation">
			<tr class="form-field form-required">
				<th scope="row"><label for="ftp_host"><?php echo __('Endereço FTP', $this->plugin_name); ?></label></th>
				<td><input class="wpmu-client" name="blog[ftp_host]" type="text" id="ftp_host" placeholder="drbmarketing.com.br" value="<?php echo $ftp_host ?>" />
					<p><?php echo __('Endereço do servidor FTP.', $this->plugin_name); ?></p>
				</td>
			</tr>
			<tr class="form-field form-required">
				<th scope="row"><label for="ftp_user"><?php echo __('Usuário FTP', $this->plugin_name); ?></label></th>
				<td><input class="wpmu-client" name="blog[ftp_user]" type="text" id="ftp_user" placeholder="user" value="<?php echo $ftp_user ?>" /></td>
			</tr>
			<tr class="form-field form-required">
				<th scope="row"><label for="ftp_pass"><?php echo __('Senha FTP', $this->plugin_name); ?></label></th>
				<td><input class="wpmu-client" name="blog[ftp_pass]" type="password" id="ftp_pass" placeholder="******" value="<?php echo $ftp_pass ?>" /></td>
			</tr>
			<tr class="form-field form-required">
				<th scope="row"><label for="ftp_port"><?php echo __('Porta FTP', $this->plugin_name); ?></label></th>
				<td><input class="wpmu-client" name="blog[ftp_port]" type="number" id="ftp_port" placeholder="21" value="<?php echo $ftp_port ?>" />
					<p><?php echo __('Geralmente porta 21.', $this->plugin_name); ?></p>
				</td>
			</tr>
			<tr class="form-field form-required">
				<th scope="row"><label for="ftp_path"><?php echo __('Caminho FTP', $this->plugin_name); ?></label></th>
				<td><input class="wpmu-client" name="blog[ftp_path]" type="text" id="ftp_path" placeholder="./caminho" value="<?php echo $ftp_path ?>" />
					<p><?php echo __('Pasta remota onde será sincronizado os arquivos do site estático WordPress.', $this->plugin_name); ?></p>
				</td>
			</tr>
		</table>
<?php
	}

	public function setup_folder($blog_id)
	{

		//Check if this blog ID has already a folder created
		$created = get_blog_option($blog_id, "wpmu_folder_created", false);

		//This blog ID has a folder already, skipping creation
		//if ($created) return;

		// Folder was not created, continue and get client name for folder
		$client = get_blog_option($blog_id, "wpmu-client_client", "");

		// If no client name defined, get blog id
		if (empty($client)) {
			$client = 'blog-' . $blog_id;
		}

		$client = sanitize_title($client);

		// Get blog name sanitized for folder name creation
		$blogname = sanitize_title(get_blog_details($blog_id)->blogname);

		// Set path to create folders
		$opts = get_site_option('wpmu-client-network-config', ['local_path' => './static']);
		(isset($opts['local_path'])) ? $path = $opts['local_path'] : $path = './static';

		if (substr($path, 0, 2) == "./") {
			$path = ABSPATH . substr($path, 2);
		}

		$is_path_ready = self::check_dir_exists($path);

		if (!$is_path_ready) {
			self::create_directory($path);
		}

		$can_create_dir = self::has_right($path);

		// Abort if no permissions
		if (!$can_create_dir) {
			$message = 'O plugin WPMU-Client não tem permissão para escrever novos diretórios. Verifique seu servidor.';
			error_log($message);
			$notice = __($message, $this->plugin_name);
			new Wpmu_Client_Admin_Notice($notice, 'error', true);
			return;
		}

		// Check if client folder exists
		$client_dir_exists = self::check_dir_exists($path . '/' . $client);

		//If no client folder, create it
		if (!$client_dir_exists) $client_dir_exists = self::create_directory($path . '/' . $client);

		$updated = false;
		// If client folder exists, check if blog folder exists and create it, if needed
		if ($client_dir_exists) {
			$project_dir_exists = self::check_dir_exists($path . '/' . $client . '/' . $blogname);

			if (!$project_dir_exists) {
				$created = self::create_directory($path . '/' . $client . '/' . $blogname);
				if ($created) {
					$updated = update_blog_option($blog_id, 'wpmu_folder_created', true);
				}
			}
		}

		//Could not update blog option, logging
		if (!$updated) {
			$message = 'O plugin WPMU-Client não conseguiu definir uma opção do blog. Verifique o código.';
			error_log($message);
			$notice = __($message, $this->plugin_name);
			new Wpmu_Client_Admin_Notice($notice, 'error', true);
			return;
		}

		update_blog_option($blog_id, 'wpmu_export_path', $path . '/' . $client . '/' . $blogname);

		$this->set_ss_options($path . '/' . $client . '/' . $blogname, $client, $blogname);
	}

	/**
	 * Set Simply Static plugin options to our defaults
	 *
	 * @param int $new_blog_id 		New blog ID.
	 * @param int $prev_blog_id		Previous blog ID.
	 * @param string $context		Additional context. Accepts 'switch' when called from switch_to_blog() or 'restore' when called from restore_current_blog() .
	 *
	 * @return void
	 *
	 */
	public function set_ss_options(string $path, string $client, string $blogname)
	{
		// Initialize SImply Static Options instance and set options
		$path = get_option("wpmu_export_path", "");

		$ss = Simply_Static\Options::instance();
		$ss->set('clear_directory_before_export', true);
		$ss->set('delivery_method', 'local');
		$ss->set('local_dir', $path);
		$ss->save();
	}

	public function wpmu_client_cpt()
	{
		$args = [
			'label'  => esc_html__('Clientes', 'text-domain'),
			'labels' => [
				'menu_name'          => esc_html__('Clientes', $this->plugin_name),
				'name_admin_bar'     => esc_html__('Cliente', $this->plugin_name),
				'add_new'            => esc_html__('Add Cliente', $this->plugin_name),
				'add_new_item'       => esc_html__('Add new Cliente', $this->plugin_name),
				'new_item'           => esc_html__('New Cliente', $this->plugin_name),
				'edit_item'          => esc_html__('Edit Cliente', $this->plugin_name),
				'view_item'          => esc_html__('View Cliente', $this->plugin_name),
				'update_item'        => esc_html__('View Cliente', $this->plugin_name),
				'all_items'          => esc_html__('All Clientes', $this->plugin_name),
				'search_items'       => esc_html__('Search Clientes', $this->plugin_name),
				'parent_item_colon'  => esc_html__('Parent Cliente', $this->plugin_name),
				'not_found'          => esc_html__('No Clientes found', $this->plugin_name),
				'not_found_in_trash' => esc_html__('No Clientes found in Trash', $this->plugin_name),
				'name'               => esc_html__('Clientes', $this->plugin_name),
				'singular_name'      => esc_html__('Cliente', $this->plugin_name),
			],
			'public'              => false,
			'exclude_from_search' => false,
			'publicly_queryable'  => false,
			'show_ui'             => true,
			'show_in_nav_menus'   => true,
			'show_in_admin_bar'   => true,
			'show_in_rest'        => false,
			'capability_type'     => 'post',
			'hierarchical'        => false,
			'has_archive'         => true,
			'query_var'           => false,
			'can_export'          => true,
			'rewrite_no_front'    => false,
			'show_in_menu'        => true,
			'menu_icon'           => 'dashicons-businessman',
			'supports' => [
				'title',
				'author',
				'custom-fields',
				'revisions'
			],

			'rewrite' => true
		];

		register_post_type('clients', $args);
	}

	public function wpmu_init_export()
	{

		$net_options = get_site_option("wpmu-client-network-config", false);

		$client				= get_option("wpmu-client_client", false);
		$ftp_host 			= get_option("wpmu-client_ftp_host", false);
		$ftp_user			= get_option("wpmu-client_ftp_user", "anonymous");
		$ftp_pass	 		= (false != get_option("wpmu-client_ftp_pass")) ? ',"' . get_option("wpmu-client_ftp_pass") . '" ' : ' '; // Do not remove whitespaces
		$ftp_port 			= (false != get_option("wpmu-client_ftp_port")) ? '-p ' . get_option("wpmu-client_ftp_port") . ' ' : '-p 21 '; // Do not remove whitespaces
		$ftp_path			= (false != get_option("wpmu-client_ftp_path")) ? get_option("wpmu-client_ftp_path") : './';
		$export_path		= get_option("wpmu_export_path", false);

		if (!$ftp_host) {
			$notice = "Credenciais de FTP não registrados. Abortando.";
			error_log($notice);
			$message = _($notice, $this->plugin_name);
			new Wpmu_Client_Admin_Notice($message, 'error', true);
			wp_die($message);
		}

		if (!$client) {
			$notice = "Nome de cliente não definido. Abortando.";
			error_log($notice);
			$message = _($notice, $this->plugin_name);
			new Wpmu_Client_Admin_Notice($message, 'error', true);
			wp_die($message);
		}

		if (!$export_path) {
			$notice = "Nome de cliente não definido. Abortando.";
			error_log($notice);
			$message = _($notice, $this->plugin_name);
			new Wpmu_Client_Admin_Notice($message, 'error', true);
			wp_die($message);
		}
		
		$cmd = 'lftp -u "' . $ftp_user . '"' . $ftp_pass . $ftp_port . $ftp_host . ' -e "set ftp:ssl-allow no; mirror -R ' . $export_path  . ' ' . $ftp_path . '"';

		self::execute_command($cmd);

		while (@ob_end_flush()); // end all output buffers if any

		$proc = popen($cmd, 'r');
		while (!feof($proc)) {
			echo fread($proc, 4096);
			@flush();
		}

		wp_die(); // this is required to terminate immediately and return a proper response
	}

	private function is_valid_domain_name(string $url)
	{

		$validation = FALSE;
		/*Parse URL*/
		$urlparts = parse_url(filter_var($url, FILTER_SANITIZE_URL));
		/*Check host exist else path assign to host*/
		if (!isset($urlparts['host'])) {
			$urlparts['host'] = $urlparts['path'];
		}

		if ($urlparts['host'] != '') {
			/*Add scheme if not found*/
			if (!isset($urlparts['scheme'])) {
				$urlparts['scheme'] = 'http';
			}
			/*Validation*/
			if (checkdnsrr($urlparts['host'], 'A') && in_array($urlparts['scheme'], array('http', 'https')) && ip2long($urlparts['host']) === FALSE) {
				$urlparts['host'] = preg_replace('/^www\./', '', $urlparts['host']);
				$url = $urlparts['scheme'] . '://' . $urlparts['host'] . "/";

				if (filter_var($url, FILTER_VALIDATE_URL) !== false && @get_headers($url)) {
					$validation = TRUE;
				}
			}
		}

		if (!$validation) {
			return false;
		} else {
			return true;
		}
	}

	/**
	 * Execute the given command by displaying console output live to the user.
	 *  @param  string  cmd          :  command to be executed
	 *  @return array   exit_status  :  exit status of the executed command
	 *                  output       :  console output of the executed command
	 */
	private function execute_command($cmd)
	{

		while (@ob_end_flush()); // end all output buffers if any

		$proc = popen("$cmd 2>&1 ; echo Exit status : $?", 'r');

		$live_output     = "";
		$complete_output = "";

		while (!feof($proc)) {
			$live_output     = fread($proc, 4096);
			$complete_output = $complete_output . $live_output;
			print_r($live_output);
			@flush();
		}

		pclose($proc);

		// get exit status
		preg_match('/[0-9]+$/', $complete_output, $matches);

		// return exit status and intended output
		return array(
			'exit_status'  => intval($matches[0]),
			'output'       => str_replace("Exit status : " . $matches[0], '', $complete_output)
		);
	}

	private function wpmu_ftp_connect(int $blog_id)
	{

		if (empty($blog_id)) $blog_id = get_current_blog_id();

		$options = get_blog_option($blog_id, 'wpmu_client_config', false);

		if (!$options) {
			$notice = "Credenciais de FTP não registradas";
			error_log($notice);
			$message = _($notice, $this->plugin_name);
			new Wpmu_Client_Admin_Notice($message, 'error', true);
			wp_die($message);
			return;
		}

		$ftp_host 		= $options['ftp_host'];
		$ftp_user		= $options['ftp_user'];
		$ftp_pass 		= $options['ftp_pass'];
		$ftp_port 		= $options['ftp_port'];
		$ftp_folder		= $options['remote_path'];

		$cmd = 'lftp -u "' . $ftp_user . '","' . $ftp_pass . '" -p ' . $ftp_port . ' ' . $ftp_host . ' -e "set ftp:ssl-allow no; ls"';

		self::execute_command($cmd);
	}

	/**
	 * Create directories for clients or blogs with a predefined path
	 *
	 * @param string $path The path to create directories
	 *
	 * @return bool Always returns true
	 *
	 */
	private function create_directory(string $path)
	{

		if (!$path) {
			$notice = "Cliente ou nome de blog não definidos para criação da pasta";
			error_log($notice);
			$message = _($notice, $this->plugin_name);
			new Wpmu_Client_Admin_Notice($message, 'error', true);
		}

		$output = null;

		if (!is_dir($path)) exec('mkdir -p -v ' . $path, $output);

		if (is_array($output) && !empty($output[0]) && strstr($output[0], 'created directory')) {
			return true;
		} else {
			return false;
		}
	}

	/**
	 * Check if php has write permisson to folder
	 *
	 * @param string $path The path to check permissions
	 *
	 * @return bool True if php has write permisson to folder
	 *
	 */
	private function has_right(string $path)
	{

		// If string is empty, log error.
		if (!$path) {
			error_log("Função check_dir_exists precisa de um caminho e um cliente");
			return false;
		}

		return is_writable($path);
	}

	/**
	 * Check if folder exists
	 *
	 * @param string $path The path to check
	 *
	 * @return bool Wheter the folder exists or not
	 *
	 */
	private function check_dir_exists(string $path)
	{
		if (!$path) {
			error_log("Função check_dir_exists precisa de um caminho e um cliente");
			return false;
		}

		return  is_dir($path);
	}

	public function register_menu_page()
	{
		include_once(plugin_dir_path(__FILE__) . 'partials/wpmu-client-admin-display.php');
	}

	public function add_admin_button($wp_admin_bar)
	{

		$site = get_bloginfo();

		$args = [
			'id' 		=> 'wpmu-client-gen',
			'title'		=> 'Enviar ' . $site . ' para Remoto',
			'href'		=> '/' . $site . '/wp-admin/options-general.php?page=wpmu-client-configuraes',
			'meta'		=> ['class', 'wpmu-button-class'],
		];

		$wp_admin_bar->add_node($args);
	}

	public function maybe_create_dir(string $option, mixed $old_value, mixed $value)
	{
		if ($option == 'wpmu_client_config') {
			if (is_array($value) && isset($value['local_path']) && !empty($value['local_path'])) {
				if ($this->is_dir($value['local_path'])) {
					$created = $this->check_dir_exists($value['local_path']);
					if (!$created) {
						$this->create_dir($value['local_path']);
					}
				}
			}
		}
	}
}
