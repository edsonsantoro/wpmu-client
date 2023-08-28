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
class Network_Tab_Page
{
    protected $plugin_name;

    protected $blog_settings_slug;

    protected $network_settings_slug;

    protected $version;

    protected $options;

    public function __construct(string $plugin_name, string $version, string $network_settings_slug, string $blog_settings_slug)
    {
        $this->plugin_name = $plugin_name;
        $this->blog_settings_slug = $blog_settings_slug;
        $this->network_settings_slug = $network_settings_slug;
        $this->version = $version;
    }

    /**
     * Add a GEN tab to site info screen
     * @param 	array 	$tabs An array of link data representing individual network admin pages.
     * @return 	array 	An array of link data representing individual network admin pages.
     */
    public function add_network_site_tab($tabs)
    {

        $tabs['site-gen'] = array(
            'label' => 'GEN',
            'url' => add_query_arg('page', 'genpage', 'sites.php'),
            'cap' => 'manage_sites'
        );
        return $tabs;
    }

    /**
     * Add our custom page to the tab
     */
    public function add_network_tab_page()
    {
        add_submenu_page(null, 'Editar site', 'Editar site', 'manage_network_options', 'genpage', [$this, 'render_tab_page']);
    }

    /**
     * The function to render the page
     */
    public function render_tab_page()
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
            <form method="post" action="edit.php?action=gen_update">
                <?php wp_nonce_field('gen-check-' . $id); ?>
                <input type="hidden" name="id" value="<?php echo $id ?>" />
                <?php
                settings_fields($this->blog_settings_slug);
                do_settings_sections($this->blog_settings_slug . '-tab');
                submit_button(); ?>
            </form>
        </div>
<?php

    }

    /**
     * Init TAB page sections and fields
     */
    public function init_tab_page()
    {

        $blog_id = (isset($_GET['id'])) ? $_GET['id'] : 0;

        register_setting(
            $this->blog_settings_slug . '_page',
            $this->blog_settings_slug,
            [$this, 'wpmu_config_sanitize']
        );

        add_settings_section(
            $this->blog_settings_slug . "_client_section",
            __("Dados deste Projeto", $this->plugin_name),
            [$this, "client_section_info"],
            $this->blog_settings_slug . "-tab"
        );

        add_settings_field(
            "client",
            __('Cliente', $this->plugin_name),
            [$this, "client_callback"],
            $this->blog_settings_slug . "-tab",
            $this->blog_settings_slug . "_client_section",
            $blog_id
        );

        add_settings_section(
            $this->blog_settings_slug . "_ss_section",
            __("Opções do Gerador", $this->plugin_name),
            [$this, "ss_section_info"],
            $this->blog_settings_slug . "-tab"
        );

        add_settings_field(
            "ss_overwrite",
            __('Definir opções do Simply Static automaticamente?', $this->plugin_name),
            [$this, "ss_overwrite_callback"],
            $this->blog_settings_slug . "-tab",
            $this->blog_settings_slug . "_ss_section",
            $blog_id
        );

        add_settings_section(
            $this->blog_settings_slug . "_ftp_section",
            __("Credenciais FTP", $this->plugin_name),
            [$this, "ftp_section_info"],
            $this->blog_settings_slug . "-tab"
        );

        add_settings_field(
            "ftp_host",
            __('FTP Endereço', $this->plugin_name),
            [$this, "ftp_host_callback"],
            $this->blog_settings_slug . "-tab",
            $this->blog_settings_slug . "_ftp_section",
            $blog_id
        );

        add_settings_field(
            "ftp_user",
            __('FTP Usuário', $this->plugin_name),
            [$this, "ftp_user_callback"],
            $this->blog_settings_slug . "-tab",
            $this->blog_settings_slug . "_ftp_section",
            $blog_id
        );

        add_settings_field(
            "ftp_pass",
            __('FTP Senha', $this->plugin_name),
            [$this, "ftp_pass_callback"],
            $this->blog_settings_slug . "-tab",
            $this->blog_settings_slug . "_ftp_section",
            $blog_id
        );

        add_settings_field(
            "ftp_port",
            __('FTP Porta', $this->plugin_name),
            [$this, "ftp_port_callback"],
            $this->blog_settings_slug . "-tab",
            $this->blog_settings_slug . "_ftp_section",
            $blog_id
        );

        add_settings_field(
            "ftp_path",
            __('FTP Diretório de Upload', $this->plugin_name),
            [$this, "ftp_path_callback"],
            $this->blog_settings_slug . "-tab",
            $this->blog_settings_slug . "_ftp_section",
            $blog_id
        );

        add_settings_field(
            "ftp_sync_new_only",
            __('Sincronizar apenas novos arquivos', $this->plugin_name),
            [$this, "ftp_sync_new_only_callback"],
            $this->blog_settings_slug . "-tab",
            $this->blog_settings_slug . "_ftp_section",
            $blog_id
        );
        

        add_settings_field(
            "show_export_screen",
            __('Ir para sincronização', $this->plugin_name),
            [$this, "show_export_screen_callback"],
            $this->blog_settings_slug . "-tab",
            $this->blog_settings_slug . "_ftp_section",
            $blog_id
        );
    }

    /**
     * Render infos on section
     */
    public function client_section_info()
    {
    }

    public function client_callback(int $blog_id)
    {
        $folder_created = get_blog_option($blog_id, $this->blog_settings_slug . '_folder_created', 0);
        $disabled = ($folder_created == 1) ? 'disabled' : '';
        printf(
            '<input class="regular-text" type="text" name="%s[client]" id="client" placeholder="cliente" value="%s" %s>
			<p>%s</p>
			<pre id="return">%s</pre>',
            $this->blog_settings_slug,
            get_blog_option($blog_id, $this->blog_settings_slug . "_client", ""),
            $disabled,
            __("Digite o nome do cliente para quem produzirá este novo site. <br><b>ATENÇÃO:</b> Verifique bem o nome, pois o mesmo será usado apra criar diretórios para exportação do código-fonte.", $this->plugin_name),
            ""
        );
    }

    /**
     * Render infos on section
     */
    public function ftp_section_info()
    {
    }

        /**
     * Render infos on section
     */
    public function ss_section_info()
    {
    }

    public function ftp_host_callback(int $blog_id)
    {
        printf(
            '<input class="regular-text" type="text" name="' . $this->blog_settings_slug . '[ftp_host]" id="ftp_host" value="%s">',
            get_blog_option($blog_id, $this->blog_settings_slug . "_ftp_host", '')
        );
    }

    public function ftp_user_callback(int $blog_id)
    {
        printf(
            '<input class="regular-text" type="text" name="' . $this->blog_settings_slug . '[ftp_user]" id="ftp_user" value="%s">',
            get_blog_option($blog_id, $this->blog_settings_slug . "_ftp_user", '')
        );
    }

    public function ftp_pass_callback(int $blog_id)
    {
        printf(
            '<input class="regular-text" type="password" name="' . $this->blog_settings_slug . '[ftp_pass]" id="ftp_pass" value="%s">',
            get_blog_option($blog_id, $this->blog_settings_slug . "_ftp_pass", '')
        );
    }

    public function ftp_port_callback(int $blog_id)
    {
        printf(
            '<input class="regular-text" type="number" name="' . $this->blog_settings_slug . '[ftp_port]" id="ftp_port" value="%s">',
            get_blog_option($blog_id, $this->blog_settings_slug . "_ftp_port", '')
        );
    }

    public function ftp_path_callback(int $blog_id)
    {
        printf(
            '<input class="regular-text" type="text" name="' . $this->blog_settings_slug . '[ftp_path]" id="ftp_path" value="%s">',
            get_blog_option($blog_id, $this->blog_settings_slug . "_ftp_path", '')
        );
    }

    public function ftp_sync_new_only_callback(int $blog_id)
    {
        $checked = (get_blog_option($blog_id, $this->blog_settings_slug . "_ftp_sync_new_only", false)) ? 'checked' : '';
        printf(
            '<input type="checkbox" name="' . $this->blog_settings_slug . '[ftp_sync_new_only]" id="ftp_sync_new_only" %s>',
            $checked
        );
    }

    public function ss_overwrite_callback(int $blog_id)
    {
        $checked = (get_blog_option($blog_id, $this->blog_settings_slug . "_ss_overwrite", false)) ? 'checked' : '';
        printf(
            '<input type="checkbox" name="' . $this->blog_settings_slug . '[ss_overwrite]" id="ss_overwrite" %s>
            <label for="ss_overwrite">Marque esta caixa para definir automaticamente algumas opções importantes do Simply Static.</label>',
            $checked
        );
    }

    public function show_export_screen_callback()
    {   
        $id = $_GET['id'];
        $url = get_blogaddress_by_id($id);
        printf(
			'<a href="%s" class="button button-secondary" type="button" name="'.$this->blog_settings_slug.'[show_config]" id="show_config" value="">%s</a>',
			$url . "wp-admin/options-general.php?page=wpmu-client-config",
			__("Ir para Sincronização", $this->plugin_name)
		);
    }


    /**
     * Save our custom fields from the GEN tab on edit site screen
     */
    public function save_network_settings()
    {
        if (!current_user_can('manage_network_options')) return;
        $id = absint($_POST['id']);
        //check_admin_referer('gen-check-' . $id);

        $options = [
            "client" => "",
            "ftp_host" => "",
            "ftp_user" => "",
            "ftp_pass" => "",
            "ftp_port" => "",
            "ftp_path" => "",
            "ftp_sync_new_only" => 0,
            "ss_overwrite" => 0,
        ];

        $posted = $_POST[$this->blog_settings_slug];

        foreach ($options as $option => $value) {

            if (isset($posted[$option]) && !empty($posted[$option])) {
                delete_blog_option($id, $this->blog_settings_slug . '_' . $option);
                update_blog_option($id, $this->blog_settings_slug . "_" . $option, $posted[$option]);

                if ($option == "ftp_sync_new_only" && isset($posted[$option])) {
                    update_blog_option($id, $this->blog_settings_slug . "_" . $option, 1);
                }
            } else {
                delete_blog_option($id, $this->blog_settings_slug . '_' . $option);
            }
        }

        $admin = new Admin_Functions($this->plugin_name, $this->version, $this->blog_settings_slug, $this->blog_settings_slug);
        $admin->setup_folder($id);

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
}

?>