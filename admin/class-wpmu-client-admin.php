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
	 * Save our custom field's values to the database.
	 *
	 * @param int|WP_Site $wp_site Site ID or object.
	 * @param array $args Arguments to modify the initialization behavior.
	 * 
	 * @return bool  True if the value was updated, false otherwise.
	 * 
	 */
	public function save_custom_site_fields( $wp_site, array $args)
	{

		// Use a default value here if the field was not submitted.
		$new_field_value = 'test';

		if (!empty($_POST['blog']['client'])) {
			$new_field_value = $_POST['blog']['client'];
		}

		// save option into the database
		return update_blog_option($wp_site->blog_id, 'client', $new_field_value);
	}

	/**
	 * Display client site field on WPMU Site Info screen
	 *
	 * @param mixed $id
	 * 
	 * @return void
	 * 
	 */
	public function show_client_site_field($id)
	{

		$client = get_blog_option($id, 'client', "");

?>
		<table class="form-table" role="presentation">
			<tr class="form-field form-required">
				<th scope="row"><label for="client"><?php _e('Cliente'); ?></label></th>
				<td>
					<input class="wpmu-client" name="blog[client]" type="text" id="client" value="<?php echo $client ?>" />
					<p><?php echo __("Digite o nome do cliente para quem produzirá este novo site. <br><b>ATENÇÃO:</b> Verifique bem o nome, pois o mesmo será usado apra criar diretórios para exportação do código-fonte.", "wpmu-client");  ?></p>
				</td>
			</tr>
		</table>
	<?php
	}

	public function show_ftp_credentials_fields(int $id)
	{

		$ftp_host 		= get_blog_option($id, 'ftp_host', "");
		$ftp_user		= get_blog_option($id, 'ftp_user', "");
		$ftp_password 	= get_blog_option($id, 'ftp_password', "");
		$ftp_port 		= get_blog_option($id, 'ftp_port', "");

	?>
		<table class="form-table" role="presentation">
			<tr class="form-field form-required">
				<th scope="row"><label for="ftp_host"><?php _e('Endereço FTP'); ?></label></th>
				<td><input class="wpmu-client" name="blog[ftp_host]" type="text" id="ftp_host" placeholder="drbmarketing.com.br" value="<?php echo $ftp_host ?>" /></td>
			</tr>
			<tr class="form-field form-required">
				<th scope="row"><label for="ftp_user"><?php _e('Usuário FTP'); ?></label></th>
				<td><input class="wpmu-client" name="blog[ftp_user]" type="text" id="ftp_user" placeholder="user" value="<?php echo $ftp_user ?>" /></td>
			</tr>
			<tr class="form-field form-required">
				<th scope="row"><label for="ftp_password"><?php _e('Senha FTP'); ?></label></th>
				<td><input class="wpmu-client" name="blog[ftp_password]" type="password" id="ftp_password" placeholder="******" value="<?php echo $ftp_password ?>" /></td>
			</tr>
			<tr class="form-field form-required">
				<th scope="row"><label for="ftp_port"><?php _e('Porta FTP'); ?></label></th>
				<td><input class="wpmu-client" name="blog[ftp_port]" type="password" id="ftp_port" placeholder="21" value="<?php echo $ftp_port ?>" /></td>
			</tr>
		</table>
<?php
	}

	/**
	 * Set Simply Static plugin options to our defaults
	 *
	 * @param int $new_blog_id
	 * @param int $prev_blog_id
	 * @param string $context
	 * 
	 * @return void
	 * 
	 */
	public function set_ss_options(int $new_blog_id, int $prev_blog_id, string $context)
	{
		// If Simply Static Plugin is not present abort
		if (!class_exists('Simply_Static\Plugin')) {
			$message = 'O plugin Simply Static não existe ou não está ativo.';
			error_log($message);
			$notice = __($message, 'wpmu-client');
			new Wpmu_Client_Admin_Notice($notice, 'error', true);
			return;
		}

		//Check if this blog ID has already a folder created
		$created = get_blog_option($new_blog_id, "wpmu_folder_created", false);

		//This blog ID has a folder already, skipping creation
		if ($created) return;

		// Folder was not created, continue and get client name for folder
		$client = sanitize_title(get_blog_option($new_blog_id, "client", false));
		
		// If no client name defined, get blog id
		if (!$client) {
			$client = 'blog-' . $new_blog_id;
		}

		// Get blog name sanitized for folder name creation
		$blogname = sanitize_title(get_blog_details($new_blog_id)->blogname);

		// Set path to create folders
		// TODO: Criar uma opção no plugin para definir dinamicamente no painel do WP.
		$path = "/var/www/gen.drb.marketing/static/";

		// TODO: Verificar antes se o caminho existe
		// Verify if we has write permissons
		$can_create_dir = self::has_right($path);

		// Abort if no permissions
		if (!$can_create_dir) {
			$message = 'O plugin WPMU-Client não tem permissão para escrever novos diretórios. Verifique seu servidor.';
			error_log($message);
			$notice = __($message, 'wpmu-client');
			new Wpmu_Client_Admin_Notice($notice, 'error', true);
			return;
		}

		// Check if client folder exists
		$client_dir_exists = self::check_dir_exists($path . $client);

		//If no client folder, create it
		if (!$client_dir_exists) $client_dir_exists = self::create_directory($path . $client);

		// If client folder exists, check if blog folder exists and create it, if needed
		if ($client_dir_exists) {
			$project_dir_exists = self::check_dir_exists($path . $client . '/' . $blogname);

			if (!$project_dir_exists) {
				self::create_directory($path . $client . '/' . $blogname);
			}
		}

		//Set this blog option as created, so we dont need to create it again
		$folder_option = update_blog_option($new_blog_id, "wpmu_folder_created", true);

		//Could not update blog option, logging
		if (!$folder_option) {
			$message = 'O plugin WPMU-Client não conseguiu definir uma opção do blog. Verifique o código.';
			error_log($message);
			$notice = _($message, 'wpmu-client');
			new Wpmu_Client_Admin_Notice($notice, 'error', true);
			return;
		}

		// Initialize SImply Static Options instance and set options
		$ss = Simply_Static\Options::instance();
		$ss->set('clear_directory_before_export', true);
		$ss->set('delivery_method', 'local');
		$ss->set('local_dir', $path . $client  . '/'  . $blogname);
		$ss->save();
	}

	private function wpmu_init_export()
	{

		//$blog_id = intval($_POST['blog_id']);

		$options = get_option('wpmu_client_configuraes_option_name');

		$ftp_host 		= $options['ftp_host'];
		$ftp_user		= $options['ftp_user'];
		$ftp_password 	= $options['ftp_password'];
		$ftp_port 		= $options['ftp_port'];
		$ftp_folder		= $options['default_local_export_path'];

		// @TO-DO Adicionar caminho local e remoto para sincronização
		// @TODO Podemos até definir se é pra sincronizar ou pra uppar apenas.

		$cmd = 'lftp -u "' . $ftp_user . '","' . $ftp_password . '" -p ' . $ftp_port . ' ' . $ftp_host . ' -e "set ftp:ssl-allow no; mirror -R"';

		self::liveExecuteCommand($cmd);

		while (@ob_end_flush()); // end all output buffers if any

		$proc = popen($cmd, 'r');
		while (!feof($proc)) {
			echo fread($proc, 4096);
			@flush();
		}

		wp_die(); // this is required to terminate immediately and return a proper response
	}

	/**
	 * Execute the given command by displaying console output live to the user.
	 *  @param  string  cmd          :  command to be executed
	 *  @return array   exit_status  :  exit status of the executed command
	 *                  output       :  console output of the executed command
	 */
	private function liveExecuteCommand($cmd)
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
		$blog_id = intval($_POST['blog_id']);

		$options = get_option('wpmu_client_configuraes_option_name');

		$ftp_host 		= $options['ftp_host'];
		$ftp_user		= $options['ftp_user'];
		$ftp_password 	= $options['ftp_password'];
		$ftp_port 		= $options['ftp_port'];
		$ftp_folder		= $options['default_local_export_path'];

		$cmd = 'lftp -u "' . $ftp_user . '","' . $ftp_password . '" -p ' . $ftp_port . ' ' . $ftp_host . ' -e "set ftp:ssl-allow no; ls"';

		self::liveExecuteCommand($cmd);
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
			$message = _($notice, 'wpmu-client');
			new Wpmu_Client_Admin_Notice($message, 'error', true);
		}

		$output = null;
		$retval = null;

		exec('mkdir -p ' . $path);
		return true;
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
}
