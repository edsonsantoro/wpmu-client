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

	public function my_admin_scripts()
	{
		wp_enqueue_script($this->plugin_name, plugin_dir_url(__FILE__) . 'js/wpmu-client-admin.js', array('jquery'), $this->version, false);
	}


	/**
	 * Add a 'client' field to new site on WPMU new site screen
	 *
	 * @param mixed $wp_site
	 * @param mixed $args
	 * 
	 * @return [type]
	 * 
	 */
	public function add_new_site_field($wp_site, $args)
	{

		// Use a default value here if the field was not submitted.
		$new_field_value = 'test';

		if (!empty($_POST['blog']['client'])) {
			$new_field_value = $_POST['blog']['client'];
		}

		// save option into the database
		update_blog_option($wp_site->blog_id, 'client', $new_field_value);
	}

	/**
	 * Display client site field on WPMU Site Info screen
	 *
	 * @param mixed $id
	 * 
	 * @return [type]
	 * 
	 */
	public function show_client_site_field($id)
	{

		$client = get_blog_option($id, 'client', true);

?>
		<table class="form-table" role="presentation">
			<tr class="form-field form-required">
				<th scope="row"><label for="client"><?php _e('Cliente'); ?></label></th>
				<td><input name="blog[client]" type="text" id="client" value="<?php echo $client ?>" /></td>
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
		if (!class_exists('Simply_Static\Plugin')) {
			error_log('function simply static does not exist');
			return;
		}

		$ss = Simply_Static\Options::instance();
		$ss->set('clear_directory_before_export', false);
		$ss->set('delivery_method', 'local');
		$client = sanitize_title(get_blog_option($new_blog_id, "client", false));

		if (!$client) {
			$client = 'blog-' . $new_blog_id;
		}

		$blogname = sanitize_title(get_blog_details($new_blog_id)->blogname);

		$path = "/var/www/static-sites/";

		$client_dir_exists = self::check_dir_exists($path . $client);

		if (!$client_dir_exists) {
			$client_dir_exists = self::create_directory($path . $client);
		}

		if ($client_dir_exists) {
			$project_dir_exists = self::check_dir_exists($path . $client .'/' . $blogname);

			if (!$project_dir_exists) {
				self::create_directory($path . $client . '/' . $blogname);
			}
		}

		$ss->set('local_dir', $path . $client  . '/'  . $blogname);
		$ss->save();
	}

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

	private function check_dir_exists(string $path)
	{
		if (!$path) {
			error_log("Função check_dir_exists precisa de um caminho e um cliente");
			return false;
		} 

		return  is_dir($path);

	}
}
