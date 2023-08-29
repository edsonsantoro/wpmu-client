<?php

namespace Wpmu_Client;
use WP_List_Table;

// Importe a classe WP_List_Table se necessário
if (!class_exists('WP_List_Table')) {
    require_once(ABSPATH . 'wp-admin/includes/class-wp-list-table.php');
}

// Classe customizada que estende WP_List_Table
class Custom_List_Table extends WP_List_Table {
    // Nome da opção do plugin
    private $option = 'wpmu_client_blog_settings_redirects';

    // Método para obter os dados da opção
    private function get_option_data() {
        $options = get_option('wpmu_client_blog_settings_redirects', array());
        return $options;
    }

    // Métodos obrigatórios para WP_List_Table
    public function get_columns() {
        return array(
            'key'   => 'Chave',
            'value' => 'Valor',
            'edit'  => 'Editar',
            'delete'  => 'Excluir'
        );
    }

    public function prepare_items() {
        $data = $this->get_option_data();

        // Reestruture a array de dados para um formato que a WP_List_Table possa usar
        $formatted_data = array();
        foreach ($data as $key => $value) {
            $formatted_data[] = array(
                'key'   => $key,
                'value' => $value,
            );
        }

        $this->_column_headers = array($this->get_columns(), array(), array());
        $this->items = $formatted_data;
    }

    public function column_default($item, $column_name) {
        switch ($column_name) {
            case 'key':
                return $item['key'];
            case 'value':
                return $item['value'];
            case 'edit':
                return '<button class="table-buttons" data-key="'.$item['key'].'" data-value="'.$item['value'].'">Editar</button>';
            case 'delete':
                return '<button class="table-buttons" id="delete_redirect" data-key="'.$item['key'].'" data-value="'.$item['value'].'">Excluir</button>';
            default:
                return '';
        }
    }
}
