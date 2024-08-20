<?php

namespace Wpmu_Client;

class Notice {
	const TRANSIENT_NAME = 'wpmu_client_admin_notice_message';
	const MESSAGE_DURATION = 300; // 5 minutos


	public function displayAdminNotice() {
		$messages = get_transient( self::TRANSIENT_NAME );

		if ( $messages ) {
			foreach ( $messages as $key => $message ) {
				if ( ( time() - $message['displayed-at'] ) < $message['duration'] ) {
					echo '<div class="notice ' . $message['notice-level'] . ' is-dismissible" data-nonce="'.$message['nonce'].'" data-displayed-at="' . $message['displayed-at'] . '" data-duration="' . $message['duration'] . '"><p>' . $message['message'] . '</p><button type="button" class="notice-dismiss"></button></div>';
				} else {
					unset( $messages[ $key ] );
				}

				if ( empty ( $messages ) ) {
					delete_transient( self::TRANSIENT_NAME );
				} else {
					self::updateTransient( $messages );
				}
			}
		}
	}

	public static function addError( string $message, int $duration = self::MESSAGE_DURATION ) {
		if ( empty ( $message ) )
			return;

		$messages = self::getMessages();
		$messages[] = array(
			'message' => $message,
			'notice-level' => 'notice-error',
			'displayed-at' => time(),
			'duration' => $duration,
			'nonce' => wp_create_nonce( 'wpmu_client_dismiss_message' )
		);

		self::updateTransient( $messages );
	}

	public static function addWarning( string $message, int $duration = self::MESSAGE_DURATION ) {
		if ( empty ( $message ) )
			return;

		$messages = self::getMessages();
		$messages[] = array(
			'message' => $message,
			'notice-level' => 'notice-warning',
			'displayed-at' => time(),
			'duration' => $duration,
			'nonce' => wp_create_nonce( 'wpmu_client_dismiss_message' )
		);

		self::updateTransient( $messages );
	}

	public static function addInfo( string $message, int $duration = self::MESSAGE_DURATION ) {
		if ( empty ( $message ) )
			return;

		$messages = self::getMessages();
		$messages[] = array(
			'message' => $message,
			'notice-level' => 'notice-info',
			'displayed-at' => time(),
			'duration' => $duration,
			'nonce' => wp_create_nonce( 'wpmu_client_dismiss_message' ) 
		);

		self::updateTransient( $messages );
	}


	public static function getMessages() {
		$messages = get_transient( self::TRANSIENT_NAME );

		return $messages ? $messages : array();
	}

	protected static function updateTransient( $messages ) {
		set_transient( self::TRANSIENT_NAME, $messages );
	}

	public function deleteTransient() {

		if ( ! wp_verify_nonce( $_POST['nonce'], 'wpmu_client_dismiss_message' ) ) {
			wp_send_json_error( 'Nonce inválido!' );
			return;
		}
        
		$displayedAt = $_POST['displayedAt'];

		$messages = get_transient( self::TRANSIENT_NAME );

		if ( $messages && $displayedAt ) {
			foreach ( $messages as $key => $message ) {
				if ( $message['displayed-at'] == $displayedAt ) {
					unset( $messages[ $key ] );
					break;
				}
			}

			if ( empty ( $messages ) ) {
				delete_transient( self::TRANSIENT_NAME );
			} else {
				set_transient( self::TRANSIENT_NAME, $messages );
			}
		}

		wp_send_json_success();
	}
}
