<?php

namespace Wpmu_Client;

use ActionScheduler;
use ActionScheduler_Store;
use DateTime;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use WP_Admin_Bar;
use WP_Error;
use WP_Site;
use Wpmu_Client\Notice;

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


class Admin_Functions
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
	 * The ID of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      array    $options    The options array for this site.
	 */
	private $options;

	/**
	 * Network wide plugin config.
	 * @since    1.0.0
	 * @access   private
	 * @var string
	 */
	private $network_settings_slug;

	/**
	 * The blog settings slug
	 *
	 * @since		1.0.0
	 * @var 		string
	 * @access		protected
	 */
	private $blog_settings_slug;

	/**
	 * Initialize the class and set its properties.
	 *
	 * @since    1.0.0
	 * @param      string    $plugin_name       The name of this plugin.
	 * @param      string    $version    The version of this plugin.
	 */
	public function __construct(string $plugin_name, string $version, string $network_settings_slug, string $blog_settings_slug)
	{
		$this->plugin_name = $plugin_name;
		$this->version = $version;
		$this->blog_settings_slug = $blog_settings_slug;
		$this->network_settings_slug = $network_settings_slug;
	}

	/**
	 * Method to remove query string from
	 * all Wordpress scripts
	 *
	 * @return void
	 */
	public function remove_scripts_query_string()
	{
		global $wp_scripts;
		if (!is_a($wp_scripts, 'WP_Scripts'))
			return;
		foreach ($wp_scripts->registered as $handle => $script)
			$wp_scripts->registered[$handle]->ver = null;
	}

	/**
	 * Method to remove query string from
	 * all Wordpress styles
	 *
	 * @return void
	 */
	public function remove_styles_query_strings()
	{
		global $wp_styles;
		if (!is_a($wp_styles, 'WP_Styles'))
			return;
		foreach ($wp_styles->registered as $handle => $style)
			$wp_styles->registered[$handle]->ver = null;
	}

	/**
	 * Get single blog options
	 * @param int $blog_id	The blog id
	 * @return array|false Array of options or false
	 */
	public function get_single_site_options(int $blog_id)
	{
		return get_blog_option($blog_id, $this->blog_settings_slug);
	}

	/**
	 * Save this blog options
	 * @param int $blog_id		The blog id
	 * @param array $data		The options array containing client, ftp_host, ftp_user, ftp_pass, ftp_port, ftp_path, local_path, client_dir
	 * @return bool				True if updated and false it not
	 */
	public function save_single_site_options(int $blog_id, array $data)
	{

		// Our options hard-coded for security.
		$options = [
			"client" => "",
			"ftp_host" => "",
			"ftp_user" => "",
			"ftp_pass" => "",
			"ftp_port" => "",
			"ftp_path" => "",
			"local_path" => "",
			"client_dir" => 0
		];

		$updated = [];
		// For each posted field, set it to the array
		foreach ($options as $key => $value) {
			if (array_key_exists($key, $data)) {
				if (isset($data[$key]) && !empty($data[$key])) {
					$updated[$key] = update_blog_option($blog_id, $this->blog_settings_slug . "_" . $key, $data[$key]);
				}

				if ($key == "client_dir")
					$updated[$key] = update_blog_option($blog_id, $this->blog_settings_slug . "_" . $key, 1);
			}
		}

		// If one of the updated keys are false, so return false, because one of the update_blog_option has failed.
		foreach ($updated as $key => $status) {
			if (!$status) {
				$notice = __("Erro ao atualizar a opção " . $key . " no banco de dados. Por favor, Verifique!", $this->plugin_name);
				new Notice($notice, "error", true, true);
				error_log($notice);
				wp_mail(wp_get_current_user()->data->user_email, "WPMU-Client", $notice);
			}
		}

		return true;
	}


	/**
	 * Ajax function to check directory names
	 */
	public function check_typed_directory()
	{
		// Filter bad character for folder name
		$path = sanitize_user($_POST['path']);
		$path = str_replace(" ", "-", $path);

		if ($_POST['is_client'] == 'true') {
			$local_path = get_site_option($this->network_settings_slug, ['local_path' => './static'])['local_path'];
			$path = $local_path . '/' . $path;
		}

		if (!path_is_absolute($path))
			$path = ABSPATH . $path;

		if (is_dir($path)) {
			exec('ls ' . $path, $out);
			if (count($out) == 0)
				$out[0] = "Diretório vazio";
			echo "O diretório ou arquivo " . $path . " já existe. O conteúdo é o seguinte: <br>";
			foreach ($out as $key => $val) {
				echo $val . "<br>";
			}
		} else {
			echo "O diretório " . $path . " NÃO existe e será criado quando salvar as opções.";
		}
	}

	/**
	 * Save our custom fields data when site is initialized
	 * @param WP_Site $new_site The new site object
	 * @param array $args Arguments for the initialization
	 */
	public function save_new_blog_options(WP_Site $new_site, array $args = [])
	{
		$id = absint($new_site->blog_id);
		$posted = absint($_POST['blog']);

		// Setup client and or project folders for exporting static sites
		$setup = $this->setup_folder($id);

		// Save data
		$this->save_single_site_options($id, $posted);
	}

	/**
	 * Register the stylesheets for the admin area.
	 * @since    1.0.0
	 */
	public function enqueue_styles()
	{

		wp_enqueue_style($this->plugin_name, plugin_dir_url(__FILE__) . 'css/wpmu-client-admin.css', array(), $this->version, 'all');
	}

	/**
	 * Register the JavaScript for the admin area.
	 * @since    1.0.0
	 */
	public function enqueue_scripts()
	{

		$action = null;
		$reference = '';
		$timestamp = '';
		if (as_has_scheduled_action('wpmu_schedule_export')) {
			$next_action = as_get_scheduled_actions(['hook' => 'wpmu_schedule_export', 'order' => 'DESC']);
			if ($next_action) {
				$lk = array_keys($next_action, max($next_action))[0];
				$action = $next_action[$lk];
				if ($action) {
					$action_args = $action->get_args();
					$reference = (isset($action_args['reference'])) ? $action_args['reference'] : '';
					$timestamp = (isset($action_args['timestamp'])) ? $action_args['timestamp'] : '';
				}
			}
		} else {
			$past_actions = as_get_scheduled_actions(['hook' => 'wpmu_schedule_export', 'order' => 'DESC']);
			if ($past_actions) {
				$lk = array_keys($past_actions, max($past_actions))[0];
				$action = $past_actions[$lk];
				if ($action) {
					$action_args = $action->get_args();
					$reference = (isset($action_args['reference'])) ? $action_args['reference'] : '';
					$timestamp = (isset($action_args['timestamp'])) ? $action_args['timestamp'] : '';
				}
			}
		}

		$export_path = get_option($this->blog_settings_slug . '_export_path') . '/logs/transfer-';
		$export_rel_path = "/" . str_replace(get_home_path(), '', $export_path);
		wp_register_script($this->plugin_name, plugin_dir_url(__FILE__) . 'js/wpmu-client-admin.js', array('jquery'), $this->version, false);
		wp_enqueue_script($this->plugin_name, plugin_dir_url(__FILE__) . 'js/wpmu-client-admin.js', array('jquery'), $this->version, false);
		wp_localize_script($this->plugin_name, 'server_ref', [
			'blog_id' => get_current_blog_id(),
			'export_path' => $export_path,
			'export_root' => $export_rel_path,
			'reference' => $reference,
			'timestamp' => $timestamp
		]);
	}

	/**
	 * Method that responds to javascript with
	 * the latest log file to read.
	 *
	 * @return void
	 */
	public function get_new_log()
	{
		//$blog_id = absint($_POST['blog_id']);
		//$reference = sanitize_text_field($_POST['reference']);

		if (true === as_has_scheduled_action('wpmu_schedule_export')) {
			$actions = as_get_scheduled_actions(['hook' => 'wpmu_scheduled_action', 'status' => ActionScheduler_Store::STATUS_PENDING]);
			if ($actions) {
				$lk = array_keys($actions, max($actions))[0];
				$action = $actions[$lk];
				if ($action) {
					$args = $action->get_args();
					$reference = (isset($args['reference'])) ? $args['reference'] : '';
					wp_send_json_success($reference);
				}
			}
		}
	}

	/**
	 * Function to replace strings on selected files inside chosen directory.
	 *
	 * This function is called after a successful simply-static export
	 * This function scans HTML, CSS and JS files for URLs that was not converted
	 * from static site to destination site URL. Simply Static not always make it
	 * correct, so we need to manually check it again.
	 *
	 * @param string $directory The directory we will work on
	 * @param string $search The string we are searching for
	 * @param string $replace What we will search for
	 * @param array  $allowedExtensions Filetypes to work on
	 *
	 */
	public function replace_strings(string $status, string $directory = '', string $search = '', string $replace = '', array $allowedExtensions = ['html', 'css', 'js'])
	{
		error_log($status);
		if($status != "success") {
			return;
		}

		if (empty($directory)) {
			$directory = get_option($this->blog_settings_slug . '_export_path');
			$directory = realpath($directory);
			if(!$this->check_dir_exists($directory)) $this->create_directory($directory);
		}
		if (empty($search))
			$search = network_home_url();

		$ss_options = get_option('simply-static', false);
		if (empty($replace)) {
			$scheme = (isset($ss_options['destination_scheme'])) ? $ss_options['destination_scheme'] : "https://";
			$replace = $scheme . $ss_options['destination_host'];
			$replace = trailingslashit($replace);
		}

		$reference = current_time('j-M-Y-H\h-i\m-s\s');

		$log_folder = $directory . "/logs";

		if (!$this->check_dir_exists($log_folder)){
			$this->create_directory($log_folder);
		}

		$log_file = $directory . '/logs/replace-' . $reference . '.log';

		if (!file_exists($log_file)) {
			exec('touch ' . $log_file);
		}
		
		$iterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($directory));
		$count = 0;
		foreach ($iterator as $file) {
			if ($file->isFile()) {
				$filePath = $file->getPathname();
				$extension = strtolower(pathinfo($filePath, PATHINFO_EXTENSION));
				if (in_array($extension, $allowedExtensions)) {
					$content = file_get_contents($filePath);
					$newContent = str_replace($search, $replace, $content);
					if ($content !== $newContent) {
						$count++;
						$htmlContent = preg_replace("/\?ver=[^\s&\"]+/", '', $newContent);
                        if(!file_exists($filePath)) {
                        	$fileHandler = fopen($filePath, 'w');
							fclose($fileHandler);
                        }
						file_put_contents($filePath, $htmlContent);
						file_put_contents($log_file, "Substituição feita em " . $filePath . "\n", FILE_APPEND);
					}
				}
			}
		}

		if ($count > 0) {
			$message = $count . " substituições realizadas nos arquivos";
			$datetime = current_time('Y-m-d H:i:s');
			$ss_options['archive_status_messages']['replaces'] = ['message' => $message, 'datetime' => $datetime];
			$updated = update_option('simply-static', $ss_options);
			if (!$updated)
				error_log("Não pude atualizar as mensages de status do simply-static");
		}

	}

	public function check_export_status()
	{
		// Try to get POSTED ID, if not, we work with args
		if (!isset($_POST['schedule_id'])) {

			if (!isset($_POST['blog_id']) || !isset($_POST['timestamp']) || !isset($_POST['reference'])) {
				error_log("Não recebemos todos os parâmetros para consulta. Função 'check_export_status', class-wpmu-client-admin.php linha " . __LINE__);
				wp_send_json_error('Não recebemos todos os parâmetros para consulta');
				return;
			}

			$blog_id = absint($_POST['blog_id']);
			$reference = sanitize_text_field($_POST['reference']);
			$timestamp = sanitize_text_field($_POST['timestamp']);

			$args = [
				'blog_id' => $blog_id,
				'timestamp' => $timestamp,
				'reference' => $reference
			];

			/**
			 * Since we don't have the ID, we need
			 * to check the action by args reference,
			 * timestamp and blog id.
			 */
			$actions = as_get_scheduled_actions(['hook' => 'wpmu_schedule_export', 'args' => $args, 'order' => 'DESC', 'status' => ActionScheduler_Store::STATUS_PENDING]);
			if (!empty($actions)) {
				$async_request_lock_expiration = ActionScheduler::lock()->get_expiration('async-request-runner');
				$expiration = $async_request_lock_expiration - time();
				wp_send_json_success(['status' => "pending", 'args' => $args, 'expiration' => $expiration]);
				wp_die();
				return;
			}

			$actions = as_get_scheduled_actions(['hook' => 'wpmu_schedule_export', 'args' => $args, 'order' => 'DESC', 'status' => ActionScheduler_Store::STATUS_RUNNING]);
			if (!empty($actions)) {
				wp_send_json_success(['status' => "running", 'args' => $args]);
				wp_die();
				return;
			}

			$actions = as_get_scheduled_actions(['hook' => 'wpmu_schedule_export', 'args' => $args, 'order' => 'DESC', 'status' => ActionScheduler_Store::STATUS_COMPLETE]);
			if (!empty($actions)) {
				wp_send_json_success(['status' => "finished", 'args' => $args]);
				wp_die();
				return;
			}
		} else {

			/**
			 * When working with Schedule id, it is simple.
			 */
			$schedule_id = absint($_POST['schedule_id']);

			// Try to get a pending action
			$action = as_get_scheduled_actions(['hook' => 'wpmu_schedule_export', 'order' => 'DESC', 'status' => ActionScheduler_Store::STATUS_PENDING]);
			if (isset($action[$schedule_id])) {
				$args = $action[$schedule_id]->get_args();
				$async_request_lock_expiration = ActionScheduler::lock()->get_expiration('async-request-runner');
				$expiration = $async_request_lock_expiration - time();
				wp_send_json_success(['status' => "pending", 'args' => $args, 'expiration' => $expiration]);
				wp_die();
				return;
			}

			// If not pending, maybe it is running
			$action = as_get_scheduled_actions(['hook' => 'wpmu_schedule_export', 'order' => 'DESC', 'status' => ActionScheduler_Store::STATUS_RUNNING]);
			if (isset($action[$schedule_id])) {
				$args = $action[$schedule_id]->get_args();
				wp_send_json_success(['status' => "running", 'args' => $args]);
				wp_die();
				return;
			}

			// Or it may be finished already
			$action = as_get_scheduled_actions(['hook' => 'wpmu_schedule_export', 'order' => 'DESC', 'status' => ActionScheduler_Store::STATUS_COMPLETE]);
			if (isset($action[$schedule_id])) {
				$args = $action[$schedule_id]->get_args();
				wp_send_json_success(['status' => "finished", 'args' => $args]);
				wp_die();
				return;
			}
		}
	}

	/**
	 * Verify client name when creating new site
	 * @param WP_Error $errors Error object, passed by reference. Will contain validation errors if any occurred.
	 * @param array $data Associative array of complete site data.
	 * @param mixed $old_site The old site object if the data belongs to a site being updated, or null if it is a new site being inserted. Defaults to null
	 */
	public function check_new_blog_fields(WP_Error $errors, array $data, $old_site)
	{
		if (empty($_POST['blog']['client'])) {
			$errors->add('site_empty_client', __("O campo de cliente não pode estar vazio", $this->plugin_name));
		}

		if (!empty($_POST['blog']['ftp_host'])) {
			if (!$this->is_valid_domain_name($_POST['blog']['ftp_host'])) {
				$errors->add('site_ftp_host_invalid', __("Endereço de FTP Host inválido", $this->plugin_name));
			}
		}
	}

	/**
	 * Display client site field on WPMU New Site screen
	 */
	public function show_client_field()
	{
		get_option($this->blog_settings_slug . "_folder_created")
			?>
		<div class="wrap">
			<h2>
				<?php echo __("Dados Locais do Clientes", $this->plugin_name); ?>
			</h2>
			<p>
				<?php echo __("Dados do cliente e arquivos locais.", $this->plugin_name); ?>
			</p>
		</div>
		<table class="form-table" role="presentation">
			<tr class="form-field form-required">
				<th scope="row"><label for="client">
						<?php echo __('Cliente ', $this->plugin_name); ?>
					</label><span class="required">*</span></th>
				<td>
					<input class="wpmu-client" name="blog[client]" type="text" id="client" required autocomplete="on"
						value="" />
					<p>
						<?php echo __("Digite o nome do cliente para quem produzirá este novo site. <br><b>ATENÇÃO:</b> Verifique bem o nome, pois o mesmo será usado apra criar diretórios para exportação do código-fonte.", $this->plugin_name); ?>
					</p>
					<pre id='return'></pre>
				</td>
			</tr>
		</table>
		<?php
	}

	/**
	 * Display FTP credentials field on WPMU New Site screen
	 */
	public function show_ftp_credentials_fields()
	{

		?>
		<div class="wrap">
			<h2>
				<?php echo __('Credenciais FTP', $this->plugin_name); ?>
			</h2>
			<p>
				<?php echo __('Podem ser definidos depois. Estes dados serão usados para definir as configurações de exportação do site do cliente para seu próprio servidor.', $this->plugin_name); ?>
			</p>
		</div>
		<table class="form-table" role="presentation">
			<tr class="form-field form-required">
				<th scope="row"><label for="ftp_host">
						<?php echo __('Endereço FTP', $this->plugin_name); ?>
					</label></th>
				<td><input class="wpmu-client" name="blog[ftp_host]" type="text" id="ftp_host" placeholder="drbmarketing.com.br"
						value="" />
					<p>
						<?php echo __('Endereço do servidor FTP.', $this->plugin_name); ?>
					</p>
				</td>
			</tr>
			<tr class="form-field form-required">
				<th scope="row"><label for="ftp_user">
						<?php echo __('Usuário FTP', $this->plugin_name); ?>
					</label></th>
				<td><input class="wpmu-client" name="blog[ftp_user]" type="text" id="ftp_user" placeholder="user" value="" />
				</td>
			</tr>
			<tr class="form-field form-required">
				<th scope="row"><label for="ftp_pass">
						<?php echo __('Senha FTP', $this->plugin_name); ?>
					</label></th>
				<td><input class="wpmu-client" name="blog[ftp_pass]" type="password" id="ftp_pass" placeholder="******"
						value="" /></td>
			</tr>
			<tr class="form-field form-required">
				<th scope="row"><label for="ftp_port">
						<?php echo __('Porta FTP', $this->plugin_name); ?>
					</label></th>
				<td><input class="wpmu-client" name="blog[ftp_port]" type="number" id="ftp_port" placeholder="21" value="" />
					<p>
						<?php echo __('Geralmente porta 21.', $this->plugin_name); ?>
					</p>
				</td>
			</tr>
			<tr class="form-field form-required">
				<th scope="row"><label for="ftp_path">
						<?php echo __('Caminho FTP', $this->plugin_name); ?>
					</label></th>
				<td><input class="wpmu-client" name="blog[ftp_path]" type="text" id="ftp_path" placeholder="./caminho"
						value="" />
					<p>
						<?php echo __('Pasta remota onde será sincronizado os arquivos do site estático WordPress.', $this->plugin_name); ?>
					</p>
				</td>
			</tr>
		</table>
		<?php
	}

	/**
	 * Create local folder for site/project export
	 * @param int $blog_id The blog id
	 * @return bool If process was done correctly or not
	 */
	public function setup_folder(int $blog_id)
	{

		//Check if this blog ID has already a folder created
		$created = get_blog_option($blog_id, $this->blog_settings_slug . "_folder_created", false);

		//This blog ID has a folder already, skipping creation
		if ($created)
			return;

		// Folder was not created, continue and get client name for folder
		$client = get_blog_option($blog_id, $this->blog_settings_slug . "_client", "");

		// We don't have a client name for this project, so abort execution
		if (empty($client)) {
			return;
		}

		// Sanitize the client name to avoid bad folder names
		$client = sanitize_user($client);

		// Get blog name sanitized for project folder creation
		$blogname = sanitize_user(get_blog_details($blog_id)->blogname);

		// Set path to create folders from options or set to default
		$opts = get_site_option($this->network_settings_slug, ['local_path' => './static']);
		(isset($opts['local_path'])) ? $path = $opts['local_path'] : $path = './static';

		// If the pathname is relative, we need to convert it back to absolute from WP
		if (substr($path, 0, 2) == "./") {
			$path = ABSPATH . substr($path, 2);
		}

		// Check if the folder exists
		$is_path_ready = $this->check_dir_exists($path);

		// If not, create it
		if (!$is_path_ready) {
			$this->create_directory($path);
		}

		// Check if we can create dirs
		$can_create_dir = $this->has_right($path);

		// Abort if no permissions
		if (!$can_create_dir) {
			$message = 'O plugin WPMU-Client não tem permissão para escrever novos diretórios. Verifique seu servidor.';
			error_log($message);
			new Notice($message, 'error', true);
			wp_mail(wp_get_current_user()->data->user_email, "WPMU-Client", $message);
			return;
		}

		// Check if client folder exists
		$client_dir_exists = $this->check_dir_exists($path . '/' . $client);

		//If no client folder, create it
		if (!$client_dir_exists)
			$client_dir_exists = $this->create_directory($path . '/' . $client);

		// If client folder exists, check if project folder exists and create it, if needed
		$updated = false;
		if ($client_dir_exists) {
			$project_dir_exists = $this->check_dir_exists($path . '/' . $client . '/' . $blogname);

			// If ok, set option. It will block new folder creation if project or client name changes.
			if (!$project_dir_exists) {
				$created = $this->create_directory($path . '/' . $client . '/' . $blogname);
				if ($created) {
					$updated = update_blog_option($blog_id, $this->blog_settings_slug . '_folder_created', true);
				}
			} else {
				// Maybe the directory was already created, so check if we have write permission and set option
				$created = $this->has_right($path . '/' . $client . '/' . $blogname);
				if ($created) {
					$updated = update_blog_option($blog_id, $this->blog_settings_slug . '_folder_created', true);
				}
			}
		}

		//Could not update blog option, logging and displaying message
		if (!$updated) {
			$message = 'O plugin WPMU-Client não conseguiu definir uma opção do blog. Verifique o código.';
			error_log($message);
			new Notice($message, 'error', true);
			wp_mail(wp_get_current_user()->data->user_email, "WPMU-Client", $message);
			return;
		}

		// Now, save the full export path
		$new_export_path = update_blog_option($blog_id, $this->blog_settings_slug . "_export_path", $path . '/' . $client . '/' . $blogname);
		if ($new_export_path != false)
			get_blog_option($blog_id, $this->blog_settings_slug . "_export_path", false);
		if ($new_export_path)
			$this->set_ss_options($blog_id, 0, "switch");

		return true;
	}

	/**
	 *  Set Simply Static options to our defaults when changing blogs
	 *  if this was selected on settings page
	 * 
	 * @param int $new_blog_id 		New blog ID.
	 * @param int $prev_blog_id		Previous blog ID.
	 * @param string $context		Additional context. Accepts 'switch' when called from switch_to_blog() or 'restore' when called from restore_current_blog() .
	 */
	public function set_ss_options($new_blog_id, $prev_blog_id, $context)
	{
		// Check if we can overwrite this blog's simply static options
		$overwrite = get_blog_option($new_blog_id, $this->blog_settings_slug . "_ss_overwrite");
		if ($overwrite == '' || $overwrite == false)
			return;

		// Working only when switching blogs
		if ($context == "restore")
			return;

		// Get export path
		$path = get_blog_option($new_blog_id, $this->blog_settings_slug . "_export_path", "");
		// Get Simply Static options
		$options = get_blog_option($new_blog_id, 'simply-static');

		$urls_to_exclude = $options['urls_to_exclude'];

		if( !isset($urls_to_exclude) || empty($urls_to_exclude) ) {

			$exclude_feed = "wp-json\nwp-login";

			if (is_array($urls_to_exclude)) {
				$urls_to_exclude = array_merge($urls_to_exclude, [$exclude_feed]);
			} else {
				$urls_to_exclude = $exclude_feed;
			}
		}

		$options['force_replace_url'] = 'on';
		$options['urls_to_exclude'] = $urls_to_exclude;
		$options['clear_directory_before_export'] = 'on';
		$options['delivery_method'] = 'local';
		$options['local_dir'] = $path;
		update_blog_option($new_blog_id, 'simply-static', $options);
	}

	/**
	 * Creates clients custom post type
	 */
	public function wpmu_client_cpt()
	{
		$args = [
			'label' => esc_html__('Clientes', 'text-domain'),
			'labels' => [
				'menu_name' => esc_html__('Clientes', $this->plugin_name),
				'name_admin_bar' => esc_html__('Cliente', $this->plugin_name),
				'add_new' => esc_html__('Add Cliente', $this->plugin_name),
				'add_new_item' => esc_html__('Add new Cliente', $this->plugin_name),
				'new_item' => esc_html__('New Cliente', $this->plugin_name),
				'edit_item' => esc_html__('Edit Cliente', $this->plugin_name),
				'view_item' => esc_html__('View Cliente', $this->plugin_name),
				'update_item' => esc_html__('View Cliente', $this->plugin_name),
				'all_items' => esc_html__('All Clientes', $this->plugin_name),
				'search_items' => esc_html__('Search Clientes', $this->plugin_name),
				'parent_item_colon' => esc_html__('Parent Cliente', $this->plugin_name),
				'not_found' => esc_html__('No Clientes found', $this->plugin_name),
				'not_found_in_trash' => esc_html__('No Clientes found in Trash', $this->plugin_name),
				'name' => esc_html__('Clientes', $this->plugin_name),
				'singular_name' => esc_html__('Cliente', $this->plugin_name),
			],
			'public' => false,
			'exclude_from_search' => false,
			'publicly_queryable' => false,
			'show_ui' => true,
			'show_in_nav_menus' => true,
			'show_in_admin_bar' => true,
			'show_in_rest' => false,
			'capability_type' => 'post',
			'hierarchical' => false,
			'has_archive' => true,
			'query_var' => false,
			'can_export' => true,
			'rewrite_no_front' => false,
			'show_in_menu' => true,
			'menu_icon' => 'dashicons-businessman',
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

	/**
	 * Schedule an action with the hook 'wpmu_schedule_export' to run as soon as possible.
	 */
	public function schedule_next_export()
	{
		if (false === as_has_scheduled_action('wpmu_schedule_export')) {
			$blog_id = absint($_POST['blog_id']);
			$timestamp = sanitize_text_field($_POST['timestamp']);

			$reference = new DateTime($timestamp);
			$reference = $reference->format('j-M-Y-H\h-i\m-s\s');


			$async_request_lock_expiration = ActionScheduler::lock()->get_expiration('async-request-runner');
			$expiration = $async_request_lock_expiration - time();
			$id = as_enqueue_async_action('wpmu_schedule_export', ['blog_id' => $blog_id, 'timestamp' => $timestamp, 'reference' => $reference], true);
			$result = ['message' => "Processo de envio agendado com id: " . $id . " e referência: " . $reference . ".\nAguardando início em " . $expiration . " segundos.", 'reference' => $reference, 'id' => $id];
			wp_send_json_success($result, 200);
		}
		wp_send_json_success(["message" => "Export já na fila, aguarde..."]);
	}

	/**
	 * The function responsible for exporting static generated sites to remote FTPs
	 */
	public function wpmu_init_export(int $blog_id, string $timestamp)
	{
		// Let's try to get this blog by id
		$site = get_site($blog_id);
		if ($site == null) {
			// The blog does not exist, log and abort
			$notice = __("WPMU-CLIENT: Erro na função wpmu_init_export, blog_id inválido", $this->plugin_name);
			error_log($notice);
			return false;
		}

		$reference = new DateTime($timestamp);
		$reference = $reference->format('j-M-Y-H\h-i\m-s\s');

		// Set reusable variables
		$blog_id = $site->blog_id;
		$blog_settings_slug = $this->blog_settings_slug;

		// Get ftp credentials, client name, and export path
		$client = get_blog_option($blog_id, $blog_settings_slug . "_client", false);
		$ftp_host = get_blog_option($blog_id, $blog_settings_slug . "_ftp_host", false);
		$ftp_user = get_blog_option($blog_id, $blog_settings_slug . "_ftp_user", "anonymous");
		$ftp_pass = (false != get_blog_option($blog_id, $blog_settings_slug . "_ftp_pass")) ? ',"' . get_blog_option($blog_id, $blog_settings_slug . "_ftp_pass") . '" ' : ' '; // Do not remove whitespaces
		$ftp_port = (false != get_blog_option($blog_id, $blog_settings_slug . "_ftp_port")) ? '-p ' . get_blog_option($blog_id, $blog_settings_slug . "_ftp_port") . ' ' : '-p 21 '; // Do not remove whitespaces
		$ftp_path = (false != get_blog_option($blog_id, $blog_settings_slug . "_ftp_path")) ? get_blog_option($blog_id, $blog_settings_slug . "_ftp_path") : './';
		$ftp_sync_new_only = (false != get_blog_option($blog_id, $blog_settings_slug . "_ftp_sync_new_only")) ? "-n " : "";
		$export_path = get_blog_option($blog_id, $blog_settings_slug . "_export_path", false);

		// If no FTP credentials, abort
		if (!$ftp_host) {
			$notice = $this->plugin_name . ": Credenciais de FTP não registrados. Abortando.";
			error_log($notice);
			new Notice($notice, 'error', true);
			wp_mail(wp_get_current_user()->data->user_email, "WPMU-Client", $notice);
			return false;
		}

		// If no client name, abort
		if (!$client) {
			$notice = "WPMU-CLIENT: Nome de cliente não definido. Abortando.";
			error_log($notice);
			new Notice($notice, 'error', false);
			wp_mail(wp_get_current_user()->data->user_email, "WPMU-Client", $notice);
			return false;
		}

		// If no export path, abort
		if (empty($export_path)) {
			$notice = "WPMU-CLIENT: Caminho de exportação não definido. Abortando.";
			error_log($notice);
			new Notice($notice, 'error', false);
			wp_mail(wp_get_current_user()->data->user_email, "WPMU-Client", $notice);
			return false;
		}

		/**
		 *  The command. Note that we depend of LFTP command to sync folder.
		 *  If command is not available, we need to stop
		 */
		$lftp = $this->command_exists("lftp");

		if (!$lftp) {
			$notice = $this->plugin_name . ": Erro: O sistema não contém o comando LFTP. Abortando.";
			error_log($notice);
			new Notice($notice, 'error', false);
			wp_mail(wp_get_current_user()->data->user_email, "WPMU-Client", $notice);
			return false;
		}

		// Get export path to create our logs
		$export_path = get_blog_option($blog_id, $this->blog_settings_slug . "_export_path");

		if (!$this->check_dir_exists($export_path . '/logs')) {
			$this->create_directory($export_path . '/logs');
		}

		if (!is_writable($export_path . '/logs')) {
			error_log('nao posso gravar');
		}

		$log_path = $export_path . '/logs/transfer-' . $reference . '.log';
		$xfer_log_path = $export_path . '/logs/transfer-xfer-' . $reference . '.log';

		// Lets build the command argument
		$cmd = 'lftp -u "' . $ftp_user . '"' . $ftp_pass . $ftp_port . $ftp_host . ' -e "set ftp:ssl-allow no;set log:enabled yes;set log:show-time yes;set log:file ' . $xfer_log_path . ';mirror --exclude=logs/ --transfer-all -P 5 -v ' . $ftp_sync_new_only . '-R ' . $export_path . '/ ' . $ftp_path . ';exit;" > ' . $log_path;

		exec($cmd);
		return true;

	}

	public function read_export_log($blog_id = '', $timestamp = '')
	{
		if (empty($blog_id) && empty($timestamp)) {
			$blog_id = absint($_POST['blog_id']);
			$reference = $_POST['exportRef'];
		}

		$export_path = get_blog_option($blog_id, $this->blog_settings_slug . "_export_path");
		$log_path = $export_path . '/logs/transfer-' . $reference . '.log';

		if (!$log_path) {
			$message = __("Não foi possível encontrar o site ou o caminho de exportação.", $this->plugin_name);
			error_log($message);
			wp_send_json_error($message);
			wp_die();
		}

		if (!file_exists($log_path)) {
			wp_send_json_error("Arquivo de log não encontrado.");
			wp_die();
		}

		$log = fopen($log_path, 'r');
		//		wp_send_json_success($log);

		if ($log) {
			while (!feof($log)) {
				$line[] = fgets($log);
			}
			fclose($log);
		}

		//wp_send_json_success($line);

		if (in_array('Closing control socket', $line)) {
			wp_send_json_success(['message' => $line, 'stop' => true]);
		} else {
			wp_send_json_success(['message' => $line, 'stop' => false]);
		}
		wp_die();
	}

	/**
	 * Checks if a command exist on a typical Linux system
	 * @param mixed $command_name
	 * @return bool
	 */
	private function command_exists(string $command_name = '')
	{
        if(empty($command_name)) return false;
		return (null === shell_exec("command -v $command_name")) ? false : true;
	}

	/**
	 * Check if user typed domain name is valid
	 * @param string $url The user supplied domain name
	 * @return bool
	 */
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
	private function execute_command($cmd, $export_path, $log_path)
	{

		$out = null;

		exec($cmd . " > " . $log_path, $out);

	}

	/**
	 * Create directories
	 *
	 * @param string $path The path to create directories
	 *
	 * @return bool True if created or not
	 *
	 */
	private function create_directory(string $path)
	{

		if (!$path) {
			$message = __("Caminho não definido.", $this->plugin_name);
			error_log($message);
			new Notice($message, 'error', true);
			wp_mail(wp_get_current_user()->data->user_email, "WPMU-Client", $message);
			return false;
		}

		$output = null;

		// Creat it only if folder does not exists yet.
		if (!is_dir($path))
			exec('mkdir -p -v ' . $path, $output);

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
			$message = __("Função has_right precisa de um caminho e um cliente.", $this->plugin_name);
			error_log($message);
			new Notice($message, 'error', true);
			wp_mail(wp_get_current_user()->data->user_email, "WPMU-Client", $message);
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
			$message = __("Função check_dir_exists precisa de um caminho e um cliente.", $this->plugin_name);
			error_log($message);
			new Notice($message, 'error', true);
			wp_mail(wp_get_current_user()->data->user_email, "WPMU-Client", $message);
			return false;
		}

		return is_dir($path);
	}

	/**
	 * Register our network plugin page
	 *
	 */
	public function register_menu_page()
	{
		include_once(plugin_dir_path(__FILE__) . 'partials/wpmu-client-admin-display.php');
	}

	/**
	 * Add export button to the top bar
	 * @param WP_Admin_Bar 	$wp_admin_bar The WP_Admin_Bar instance, passed by reference.
	 */
	public function add_admin_button(WP_Admin_Bar $wp_admin_bar)
	{

		if (is_network_admin())
			return $wp_admin_bar;

		$site = get_blog_details();

		$args = [
			'id' => 'wpmu-client-gen',
			'title' => 'Enviar ' . $site->blogname . ' para Remoto',
			'href' => $site->siteurl . '/wp-admin/options-general.php?page=wpmu-client-config',
			'meta' => ['class', 'wpmu-button-class'],
		];

		$wp_admin_bar->add_node($args);
	}
}
