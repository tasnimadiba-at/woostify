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
 * * ABSPATH
 *
 * @link https://wordpress.org/support/article/editing-wp-config-php/
 *
 * @package WordPress
 */

// ** Database settings - You can get this info from your web host ** //
/** The name of the database for WordPress */
define( 'DB_NAME', 'woostify' );

/** Database username */
define( 'DB_USER', 'root' );

/** Database password */
define( 'DB_PASSWORD', '' );

/** Database hostname */
define( 'DB_HOST', 'localhost' );

/** Database charset to use in creating database tables. */
define( 'DB_CHARSET', 'utf8mb4' );

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
define( 'AUTH_KEY',         '>F5b M.?d//wt}Ji}V:~E/k|0}=%5.ZuR{-qh _~nT]W]gS)Oh}!`C[Xl1[a3q]x' );
define( 'SECURE_AUTH_KEY',  'wOOl. a^oRS=k%U k845Py>XK/?/.*SJ^`2A#nAA{ykbF3PszzBl+_A1W.zxhqx.' );
define( 'LOGGED_IN_KEY',    'P|2gf4n.<NhdRo|!cj,?C$mD,dqqG]y-Db-<7uUD K4o03Qa7xqLC@grgVfQ8OFB' );
define( 'NONCE_KEY',        'lc`+{wRqm7Q}fgn!,? ufLBHiV;1$UjbKBX0cZ6iOoK(v0(R-ZO0y{`s)USJyL8Q' );
define( 'AUTH_SALT',        '[CS%>DAZ^S$.d4/u!CL.y1q@g, t+xFR?4DnNQ?iAb5`0=FWn:Upr%|,rn6^4(Je' );
define( 'SECURE_AUTH_SALT', 'j3uVFdl0|7j%r9RM9V;ILO4%B5m-|-XHa8A~iQ3@GmT=&:N]+>]+N-#-e2G~Y~|S' );
define( 'LOGGED_IN_SALT',   'hxGEnKzVT>rs6:z`ny5{1%:d[iLe[nt2x10-%E]a/+k`hc#ox_dbRBOU[^5@xN&;' );
define( 'NONCE_SALT',       '8<X;3ku#?Y6h@kL5uN]hcyxNhZRGb#I7J3_p,Al[PP3*[gN2vfp~Ys7/kPU$HL+G' );

/**#@-*/

/**
 * WordPress database table prefix.
 *
 * You can have multiple installations in one database if you give each
 * a unique prefix. Only numbers, letters, and underscores please!
 */
$table_prefix = 'woostify_';

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
define( 'WP_DEBUG', false );

/* Add any custom values between this line and the "stop editing" line. */



/* That's all, stop editing! Happy publishing. */

/** Absolute path to the WordPress directory. */
if ( ! defined( 'ABSPATH' ) ) {
	define( 'ABSPATH', __DIR__ . '/' );
}

/** Sets up WordPress vars and included files. */
require_once ABSPATH . 'wp-settings.php';
