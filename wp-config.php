<?php
/**
 * The base configuration for WordPress
 *
 * The wp-config.php creation script uses this file during the
 * installation. You don't have to use the web site, you can
 * copy this file to "wp-config.php" and fill in the values.
 *
 * This file contains the following configurations:
 *
 * * MySQL settings
 * * Secret keys
 * * Database table prefix
 * * ABSPATH
 *
 * @link https://codex.wordpress.org/Editing_wp-config.php
 *
 * @package WordPress
 */

// ** MySQL settings - You can get this info from your web host ** //
/** The name of the database for WordPress */
define( 'DB_NAME', 'wme-db' );

/** MySQL database username */
define( 'DB_USER', 'root' );

/** MySQL database password */
define( 'DB_PASSWORD', '' );

/** MySQL hostname */
define( 'DB_HOST', 'localhost' );

/** Database Charset to use in creating database tables. */
define( 'DB_CHARSET', 'utf8mb4' );

/** The Database Collate type. Don't change this if in doubt. */
define( 'DB_COLLATE', '' );

/**#@+
 * Authentication Unique Keys and Salts.
 *
 * Change these to different unique phrases!
 * You can generate these using the {@link https://api.wordpress.org/secret-key/1.1/salt/ WordPress.org secret-key service}
 * You can change these at any point in time to invalidate all existing cookies. This will force all users to have to log in again.
 *
 * @since 2.6.0
 */
define( 'AUTH_KEY',         '>u;]?:eorxJf$.j7~LsJ5W]Hh1vn.i|&BgX~rd[*P+WmufC)n`5M^^&,Dv*3pP;h' );
define( 'SECURE_AUTH_KEY',  '+@mA(RtK7MCY^_~SQ@Ov)-BVzA/)dS}o}<:=1yUr1B*COTolbP4?c_qI:j`x%V|1' );
define( 'LOGGED_IN_KEY',    '}Bz2bF/tjh$GlbZqXn1svR%V(/t]q~~W5F8erpTCX-}vPg8OB*f!Nk<<qPZu@DK!' );
define( 'NONCE_KEY',        'GyN-@Bwqq9.w0h{XIs{CL?Pd%gXT:Kyj$|yT+,1~niT#o^O=*mfAeJA&m;!?}9c@' );
define( 'AUTH_SALT',        'd?tPos^spvs1_i{ejcz9DA[(.<-oqGf42FY&ywo2%mgR@`aqMS?u1&TQlEiQ1SR0' );
define( 'SECURE_AUTH_SALT', '{B}D)f.MJ}%3CXvx8?89dE_r:E}IA3nu-dC*wToEB2m6#vqE.=h+R?P(lN/HDiYB' );
define( 'LOGGED_IN_SALT',   'Vt={`f7d#=p5p|Yz0;V/E$[sv1#pv94$XZpbGv]A:=*dyztg$%+3>yx#FraC(5!`' );
define( 'NONCE_SALT',       '+Os_27/z`8PX3AG)6:S1Bv#d:g?%j5rS`$3-rc-|lDf4Z=t/V-<E=;3ngHGs483X' );

/**#@-*/

/**
 * WordPress Database Table prefix.
 *
 * You can have multiple installations in one database if you give each
 * a unique prefix. Only numbers, letters, and underscores please!
 */
$table_prefix = 'wp_';

/**
 * For developers: WordPress debugging mode.
 *
 * Change this to true to enable the display of notices during development.
 * It is strongly recommended that plugin and theme developers use WP_DEBUG
 * in their development environments.
 *
 * For information on other constants that can be used for debugging,
 * visit the Codex.
 *
 * @link https://codex.wordpress.org/Debugging_in_WordPress
 */
define( 'WP_DEBUG', false );

/* That's all, stop editing! Happy publishing. */

/** Absolute path to the WordPress directory. */
if ( ! defined( 'ABSPATH' ) ) {
	define( 'ABSPATH', dirname( __FILE__ ) . '/' );
}

/** Sets up WordPress vars and included files. */
require_once( ABSPATH . 'wp-settings.php' );

define( 'UPLOADS', ''.'images' );
