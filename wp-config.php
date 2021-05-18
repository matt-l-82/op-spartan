
<<<<<<< HEAD
 require_once dirname(__FILE__) . '/vendor/autoload.php';

$dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
$dotenv->load();


// ** MySQL settings - You can get this info from your web host ** //
/** The name of the database for WordPress */
define( 'DB_NAME', $_ENV["DB_NAME"] );

/** MySQL database username */
define( 'DB_USER', $_ENV["DB_USER"] );

/** MySQL database password */
define( 'DB_PASSWORD', $_ENV["DB_PASSWORD"] );

/** MySQL hostname */
define( 'DB_HOST', $_ENV["DB_HOST"] );

/** Database Charset to use in creating database tables. */
define( 'DB_CHARSET', 'utf8' );

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
define('AUTH_KEY',         'R`Gf!n2:SWu?|[hD=`,kkI|QngZ2Pqn&6EP+qUAc>|ik-k ze$M?[8RW{DRU5T2i');
define('SECURE_AUTH_KEY',  'j#obu2~16n?:y,Cm@Lcy/!@=F-ruO^8d$y78S>%0S*#s;PIkH7j.X<>qFg=).@5G');
define('LOGGED_IN_KEY',    '$(z0u|6-h]I?Fve-O)RBI+k`G|S(CX!/edRQh-[x(l_%~0AF l<RVHZg).Zij2:_');
define('NONCE_KEY',        'Dow7KsX,^`bEDwViI}dhYy>V(>gq]||D8jtY@#t&~jxarJKzIyP6=YMHtO-~>f8@');
define('AUTH_SALT',        '%ukvOm*PFHE`$It7-Ef9N+R{9UTqdTac2uWu];kGvknK?[w(ZIKLQrE{|}3OF)Hv');
define('SECURE_AUTH_SALT', '^PF>-p:RJ7/wQ_o+`OW(}VRzj2ZW|wY6<$|8j[{|$80(p{J3aKN1?@iW`l#=B;%[');
define('LOGGED_IN_SALT',   '7T;ru&bJyZX5Poj&#r[pb@(k}Rl}Q*XoK;Q09=tgwp[Zi49|j8B+[[lG]NQbHm+S');
define('NONCE_SALT',       'P_cT4u0[lINWmqZ E;ju%nY_>-n-q*XuktEytmL|n:1T2^-~DhzcqJ[)@-u,Q86^');


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
 * visit the documentation.
 *
 * @link https://wordpress.org/support/article/debugging-in-wordpress/
 */
define( 'WP_DEBUG', false );

/* That's all, stop editing! Happy publishing. */

/** Absolute path to the WordPress directory. */
if ( ! defined( 'ABSPATH' ) ) {
	define( 'ABSPATH', __DIR__ . '/' );
}

/** Sets up WordPress vars and included files. */
require_once ABSPATH . 'wp-settings.php';
=======
>>>>>>> ee8f1997e0568e51ce2491a7fb4f807dcccb0bfb
