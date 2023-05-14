<?php

namespace Wpmu_Client;

class Notice {

    /**
     * The message of the notice
     *
     * @var string
     */
    private $_message = '';

    /**
     * The type of the message, can be error, info, warning or success
     *
     * @var string 
     */
    private $_type = 'info';

    /**
     * If the notice can be dismissible or not.
     *
     * @var bool
     */
    private $_dismissible = true;

    /**
     * [Description for __construct]
     *
     * @param string $message The message of the notice. Empty string my default.
     * @param string $type The type of notice (error, info, warning or success). Info by default
     * @param bool $dismissible If the message is dismissible or not. True by default
     * @param bool $network True if the message is for network admins, false for site admins.
     * 
     */
    function __construct( string $message = null, string $type = 'info', bool $dismissible = true, bool $network = false ) {
        $this->_message = $message;
        $this->_dismissible = $dismissible;
        $this->_type = $type;
        if($network) {
            add_action( 'network_admin_notices', [$this, 'render'], 5 );    
        } else {
            add_action( 'admin_notices', [$this, 'render'], 5  );
        }
        
    }

    function render() {
        $dismiss = '';
        if($this->_dismissible) $dismiss = 'is-dismissible';
        echo '<div class="notice notice-'.$this->_type.' '.$dismiss.'"><p>'.$this->_message.'</p></div>';
    }
}