<?php
/**
 * The base configuration for WordPress
 *
 * The wp-config.php creation script uses this file during the installation.
 * You don't have to use the web site, you can copy this file to "wp-config.php"
 * and fill in the values.
 *
 * This file contains the following configurations:
 *
 * * Database settings
 * * Secret keys
 * * Database table prefix
 * * Localized language
 * * ABSPATH
 *
 * @link https://wordpress.org/support/article/editing-wp-config-php/
 *
 * @package WordPress
 */

// ** Database settings - You can get this info from your web host ** //
/** The name of the database for WordPress */
define( 'DB_NAME', 'wordpress' );

/** Database username */
define( 'DB_USER', 'wordpress' );

/** Database password */
define( 'DB_PASSWORD', 'wordpress' );

/** Database hostname */
define( 'DB_HOST', 'database' );

/** Database charset to use in creating database tables. */
define( 'DB_CHARSET', 'utf8' );

/** The database collate type. Don't change this if in doubt. */
define( 'DB_COLLATE', '' );

/**#@+
 * Authentication unique keys and salts.
 *
 * Change these to different unique phrases! You can generate these using
 * the {@link https://api.wordpress.org/secret-key/1.1/salt/ WordPress.org secret-key service}.
 *
 * You can change these at any point in time to invalidate all existing cookies.
 * This will force all users to have to log in again.
 *
 * @since 2.6.0
 */
define( 'AUTH_KEY',          'cGc)U0!u,YfLuBz}Yz9!NfF~`Ym[HK}mZlo`)>oRNa*Llaryh5h^RWW2l&)+]0tj' );
define( 'SECURE_AUTH_KEY',   'sXEMrWvae5&zI Y @A:2fH$<8j!-,Qq`WFC8gD2>?z9tp7k?7hN_Sc8$k_Lw2voB' );
define( 'LOGGED_IN_KEY',     '-Szh^o1j%},|/=k7nS(Vgd(^=A=;#}A}.*^gkJWz?x%17XyQ#B;Ga_CpV}!rxz1j' );
define( 'NONCE_KEY',         '2q9i#&d;M*q}vjE/0|R]y_i}i_MI4**P74ehaiI:xuxVpK6N#Q=?{KP}iu(E>`*Y' );
define( 'AUTH_SALT',         'a7#:/_2+z~3enseYp8+y|1JH(c[QtA.Q4hg4?pzA13l/_u3!e2vy|IeL|&`z)7ec' );
define( 'SECURE_AUTH_SALT',  'EP7.g{=H,k+Z*0;<]w&}K|(P-N=>Di4ylStlf4lgx1wixnqxxXqMH!kx1|oT|6uV' );
define( 'LOGGED_IN_SALT',    '@#MIsQq:<1rKa)B(cbSbttF@q.^Oj3G2-NAYP5yfCK1.T7{C(LKMU YR&288~z@^' );
define( 'NONCE_SALT',        'WILjOVL]M[auXu,UK)}jcAMOV&.:ZTH|0 GaQ.7|Ql|&?=|jCDrMpJK$<m0vgJOW' );
define( 'WP_CACHE_KEY_SALT', ':bon`b;[P6x,X_&6ZqOO$?GK&(L+Y$Be{4|r6Bb@OpErp@u-}yiyC6.A72,*T&wj' );


/**#@-*/

/**
 * WordPress database table prefix.
 *
 * You can have multiple installations in one database if you give each
 * a unique prefix. Only numbers, letters, and underscores please!
 */
$table_prefix = 'wp_';


/* Add any custom values between this line and the "stop editing" line. */

define( 'WP_DEBUG', true );
define( 'WP_DEBUG_LOG', true );

/**
 * For developers: WordPress debugging mode.
 *
 * Change this to true to enable the display of notices during development.
 * It is strongly recommended that plugin and theme developers use WP_DEBUG
 * in their development environments.
 *
 * For information on other constants that can be used for debugging,
 * visit the documentation.
 *
 * @link https://wordpress.org/support/article/debugging-in-wordpress/
 */
if ( ! defined( 'WP_DEBUG' ) ) {
	define( 'WP_DEBUG', false );
}



define( 'WP_ALLOW_MULTISITE', true );
define( 'MULTISITE', true );
define( 'SUBDOMAIN_INSTALL', false );
$base = '/';
define( 'DOMAIN_CURRENT_SITE', 'gen.lndo.site' );
define( 'PATH_CURRENT_SITE', '/' );
define( 'SITE_ID_CURRENT_SITE', 1 );
define( 'BLOG_ID_CURRENT_SITE', 1 );

/* That's all, stop editing! Happy publishing. */

/** Absolute path to the WordPress directory. */
if ( ! defined( 'ABSPATH' ) ) {
	define( 'ABSPATH', __DIR__ . '/' );
}

/** Sets up WordPress vars and included files. */
require_once ABSPATH . 'wp-settings.php';
