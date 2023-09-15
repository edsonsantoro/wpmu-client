<?php

namespace Wpmu_Client;

use WP_Query;
use Wpmu_Client\Custom_List_Table;

/**
 * Provide a redirects settings page for this plugin
 *
 * @link       https://santoro.studio
 * @since      1.1.0
 *
 * @package    Wpmu_Client
 * @subpackage Wpmu_Client/admin/partials
 */

class Admin_Redirect_Settings_Page {

	/**
	 * The plugin name
	 *
	 * @var string
	 */
	public $plugin_name;

	/**
	 * The blog settings slug
	 *
	 * @var string
	 */
	protected $blog_settings_slug;

	/**
	 * The network settings slug
	 *
	 * @var string
	 */
	protected $network_settings_slug;

	/**
	 * Class constructor
	 *
	 * @param string $plugin_name The plugin name
	 * @param string $network_settings_slug The network settings slug;
	 * @param string $blog_settings_slug The blog settings slug
	 * 
	 */
	public function __construct( string $plugin_name, string $network_settings_slug, string $blog_settings_slug ) {
		if ( empty( $plugin_name ) || empty( $network_settings_slug ) || empty( $blog_settings_slug ) )
			return;

		$this->set_plugin_name( $plugin_name );
		$this->set_network_setting_slug( $network_settings_slug );
		$this->set_blog_settings_slug( $blog_settings_slug );

	}

	/**
	 * Sets the plugin name
	 *
	 * @param string $plugin_name The plugin name
	 * 
	 * @return void
	 * 
	 */
	protected function set_plugin_name( string $plugin_name ) {
		$this->plugin_name = $plugin_name;
	}

	/**
	 * Sets the network settings slug
	 *
	 * @param string $network_settings_slug The network settings slug
	 * 
	 * @return void
	 * 
	 */
	protected function set_network_setting_slug( string $network_settings_slug ) {
		$this->network_settings_slug = $network_settings_slug;
	}

	/**
	 * Sets the blog settings slug
	 *
	 * @param string $blog_settings_slug The blog settings slug
	 * 
	 * @return void
	 * 
	 */
	protected function set_blog_settings_slug( string $blog_settings_slug ) {
		$this->blog_settings_slug = $blog_settings_slug;
	}

	/**
	 * Get the plugin name
	 *
	 * @return string The plugin name
	 * 
	 */
	public function get_plugin_name() {
		return $this->plugin_name;
	}

	/**
	 * Get network settings slug
	 *
	 * @return string The network settings slug
	 * 
	 */
	public function get_network_settings_slug() {
		return $this->network_settings_slug;
	}

	/**
	 * Get blog settings slug
	 *
	 * @return string The blog settings slug
	 * 
	 */
	public function get_blog_settings_slug() {
		return $this->blog_settings_slug;
	}

	/**
	 * Add single site menu item
	 * 
	 * @return void
	 */
	public function wpmu_client_add_plugin_page() {

		add_submenu_page(
			'wpmu-client-config',
			'DRB.MKT | Redirecionamentos',
			'Redirecionamentos',
			'manage_options',
			'wpmu-client-redirects',
			[ $this, 'wpmu_client_create_redirects_page' ]
		);

	}


	/**
	 * Render the redirects page
	 * 
	 * @return void
	 */
	public function wpmu_client_create_redirects_page() {
		?>
		<div class="wrap">
			<h2>Redirecionamentos</h2>
			<p>Página de configurações de redirecionamentos para o Gerador de Sites da DRB.MKT</p>


			<form method="post" action="options.php">
				<?php
				settings_fields( $this->blog_settings_slug . '_redirects' );
				do_settings_sections( $this->blog_settings_slug . '-redirects' );
				//submit_button('Salvar Configurações');
				?>
				<button type="button" class="button button-primary" id="add_redirect">Adicionar Redirecionamento</button>
			</form>
		</div>
	<?php }


	/**
	 * Function to save the redirects to the DB.
	 *
	 * @return void
	 * 
	 */
	public function save_redirects() {
		$blog_settings_slug = $this->get_blog_settings_slug();
		$source_url = ( isset( $_POST[ $blog_settings_slug ]['source_url'] ) ) ? sanitize_text_field( $_POST[ $blog_settings_slug ]['source_url'] ) : '';
		$target_url = ( isset( $_POST[ $blog_settings_slug ]['target_url'] ) ) ? sanitize_text_field( $_POST[ $blog_settings_slug ]['target_url'] ) : '';

		$source_url = ( isset( $_POST['source_url'] ) ) ? sanitize_text_field( $_POST['source_url'] ) : '';
		$target_url = ( isset( $_POST['target_url'] ) ) ? sanitize_text_field( $_POST['target_url'] ) : '';


		if ( ! empty( $source_url ) && ! empty( $target_url ) ) {

			$existing = get_option( $blog_settings_slug . "_redirects", [] );
			$existing[ $source_url ] = $target_url;
			$updated = update_option( $blog_settings_slug . "_redirects", $existing );

			if ( $updated ) {
				$this->build_htaccess( 'success' );
				wp_send_json_success();
                wp_die();
			} else {
				wp_send_json_error();
                wp_die();
			}
		}
	}

	public function delete_redirect() {
		$blog_settings_url = $this->get_blog_settings_slug();

		if ( ! isset( $_POST['source_url'] ) || ! isset( $_POST['target_url'] ) ) {
			wp_send_json_error( 'Não recebi a chave do redirecionamento para excluir.' );
            wp_die();
		}

		$source_url = sanitize_text_field( $_POST['source_url'] );
		$target_url = sanitize_text_field( $_POST['target_url'] );

		$redirects = get_option( $blog_settings_url . "_redirects", false );

		if ( ! $redirects ) {
			wp_send_json_error( 'Não pude obter a lista de redirecionamentos, ou ela está vazia.' );
            wp_die();
        }

		if ( array_key_exists( $source_url, $redirects ) ) {
			unset( $redirects[ $source_url ] );
			$updated = update_option( $blog_settings_url . "_redirects", $redirects );
			if ( ! $updated ) {
				wp_send_json_error( 'Não fui capaz de atualizar a opção.' );
                wp_die();
			}

			$this->build_htaccess( 'success' );
			wp_send_json_success( 'Redirecionamento removido.' );
            wp_die();
		}
		wp_send_json_error( 'Esse redirecionamento não existe' );
        wp_die();

	}

	public function build_htaccess( string $status ) {
		if ( $status != "success" ) {
			return;
		}

		$directory = get_option( $this->blog_settings_slug . '_export_path' );
		$directory = realpath( $directory );
		$htaccessFile = $directory . "/.htaccess";

		$redirects = get_option( $this->get_blog_settings_slug() . "_redirects", false );
		$https = "RewriteCond %{HTTPS} !=on\nRewriteRule ^ https://%{HTTP_HOST}%{REQUEST_URI} [L,R=301]\nRewriteEngine On\n";

		if ( $fileHandle = fopen( $htaccessFile, 'w' ) ) {
			fwrite( $fileHandle, $https );
			foreach ( $redirects as $source => $destination ) {
				$redirectRule = "Redirect 301 $source $destination\n";
				fwrite( $fileHandle, $redirectRule );
			}
			fclose( $fileHandle );
		}
	}


	/**
	 * Function to create a section info
	 *
	 * @param array $args The arguments to the section
	 * 
	 * @return void
	 * 
	 */
	public function wpmu_client_section_info( array $args ) {
		if ( ! empty( $args['description'] ) ) {
			printf(
				'<p>%s</p>',
				$args['description']
			);
		}
	}

	/**
	 * Function to sanitize inputs
	 *
	 * @param string $input The source URL
	 * 
	 * @return string The sanitized input URL
	 * 
	 */
	public function sanitize( string $input ) {
		if ( is_array( $input ) ) {
			return serialize( $input ); // Serialize a array antes de salvar
		}
		return $input;
	}

	/**
	 * Render single site settings page
	 */
	public function wpmu_client_redirects_page_init() {
		register_setting(
			$this->blog_settings_slug . '_redirects',
			'source_url',
			[ 
				'type' => 'string',
				'sanitize_callback' => [ $this, 'sanitize' ]
			]
		);

		register_setting(
			$this->blog_settings_slug . '_redirects',
			'target_url',
			[ 
				'type' => 'string',
				'sanitize_callback' => [ $this, 'sanitize' ]
			]
		);

		add_settings_section(
			'wpmu_client_redirects',
			'Redirecionamentos',
			array( $this, 'wpmu_client_section_info' ),
			$this->blog_settings_slug . '-redirects',
			[ "description" => "Adicionar um novo redirecionamento" ]
		);

		add_settings_field(
			'force_https',
			'Forçar HTTPS no site?',
			array( $this, 'redirects_field_force_https' ),
			$this->blog_settings_slug . '-redirects',
			'wpmu_client_redirects',
			[ "description" => "Marcando essa opção, uma regra de redirecionamento será adicionada ao .htaccess do servidor do cliente. Se houver problemas de loop de redirecionamento, tente desativar esta opção.", "title" => "Forçar HTTPS no site remoto" ]
		);

		add_settings_field(
			'source_url',
			'Página de origem',
			array( $this, 'redirects_field_source_url' ),
			$this->blog_settings_slug . '-redirects',
			'wpmu_client_redirects',
			[ 'title' => 'URL de Origem' ]
		);

		add_settings_field(
			'target_url',
			'Página de destino',
			array( $this, 'redirects_field_target_url' ),
			$this->blog_settings_slug . '-redirects',
			'wpmu_client_redirects',
			[ 'title' => 'URL de Destino' ]
		);

		add_settings_section(
			'wpmu_client_redirects2',
			'Redirecionamentos',
			array( $this, 'custom_list_table_section_callback' ),
			$this->blog_settings_slug . '-redirects',
			[ "description" => "Adicionar um novo redirecionamento" ]
		);


	}

	/**
	 * This function gets input of user and return
	 * a permalink of a possible internal page to the
	 * redirect page
	 *
	 * @return void
	 * 
	 */
	public function get_internal_permalink() {
		if ( ! isset( $_POST['partial_input'] ) || empty( $_POST['partial_input'] ) ) {
			wp_send_json_error();
            wp_die();
		}

		$search = sanitize_text_field( $_POST['partial_input'] );

		$args = array(
			's' => $search,
			'post_type' => 'any',
			'posts_per_page' => -1,
			// Mostrar todos os posts
		);

		$query = new WP_Query( $args );
		$links = [];
		if ( $query->have_posts() ) {
			while ( $query->have_posts() ) {
				$query->the_post();
				$permalink = get_permalink();
				$id = get_the_ID();
				$links[ $id ] = $permalink;
			}
			wp_reset_postdata();
			wp_send_json_success( $links );
            wp_die();
		} else {
			wp_send_json_error();
            wp_die();
		}
	}

	/**
	 * Add the redirect source URL
	 */
	public function redirects_field_source_url( array $args ) {
		printf(
			'<input class="long-field regular-text" type="text" title="%s" name="%s[source_url]" id="source_url" value="" />',
			$args['title'],
			$this->blog_settings_slug
		);
	}

	/**
	 * Add the redirect target URL
	 */
	public function redirects_field_target_url( array $args ) {
		printf(
			'<input class="long-field regular-text" type="text" title="%s" name="%s[target_url]" id="target_url" value="" /><ul class="target_selector" style="display:none"></ul> ',
			$args['title'],
			$this->blog_settings_slug
		);
	}

	/**
	 * Add the redirect target URL
	 */
	public function redirects_field_force_https( array $args ) {
		printf(
			'<label for="force_https">%s</label>
            <br><input type="checkbox" title="%s" name="%s[force_https]" id="force_https" />',
			$args['description'],
			$args['title'],
			$this->blog_settings_slug
		);
	}


	/**
	 * Render the table of redirects to the page
	 */
	public function custom_list_table_section_callback() {
		echo '<div class="wrap">';
		$list_table = new Custom_List_Table();
		$list_table->prepare_items();
		$list_table->display();
		echo '</div>';
	}
}