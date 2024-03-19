<?php

namespace Wpmu_Client;

class Notice {

    /**
     * The message of the notice.
     *
     * @var string
     */
    private $message;

    /**
     * The type of the message, can be error, info, warning or success.
     *
     * @var string 
     */
    private $type;

    /**
     * If the notice can be dismissible or not.
     *
     * @var bool
     */
    private $dismissible;

    /**
     * Constructor.
     *
     * @param string|null $message The message of the notice.
     * @param string $type The type of notice (error, info, warning or success).
     * @param bool $dismissible If the message is dismissible or not.
     * @param bool $network True if the message is for network admins, false for site admins.
     */
    public function __construct(?string $message = null, string $type = 'info', bool $dismissible = true, bool $network = false) {
        if ($message !== null) {
            $this->message = $message;
        }
        $this->type = $type;
        $this->dismissible = $dismissible;

        if ($network) {
            add_action('network_admin_notices', [$this, 'render'], 10);    
        } else {
            add_action('admin_notices', [$this, 'render'], 10);
        }
    }

    /**
     * Render the notice.
     */
    public function render() {
        if (!empty($this->message)) {
            $dismiss = ($this->dismissible) ? 'is-dismissible' : '';
            echo '<div class="notice notice-' . $this->type . ' ' . $dismiss . '"><p>' . $this->message . '</p></div>';
        }
    }
}
