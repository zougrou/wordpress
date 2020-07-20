<?php
/**
 * La configuration de base de votre installation WordPress.
 *
 * Ce fichier est utilisé par le script de création de wp-config.php pendant
 * le processus d’installation. Vous n’avez pas à utiliser le site web, vous
 * pouvez simplement renommer ce fichier en « wp-config.php » et remplir les
 * valeurs.
 *
 * Ce fichier contient les réglages de configuration suivants :
 *
 * Réglages MySQL
 * Préfixe de table
 * Clés secrètes
 * Langue utilisée
 * ABSPATH
 *
 * @link https://fr.wordpress.org/support/article/editing-wp-config-php/.
 *
 * @package WordPress
 */

// ** Réglages MySQL - Votre hébergeur doit vous fournir ces informations. ** //
/** Nom de la base de données de WordPress. */
define( 'DB_NAME', 'bdsite' );

/** Utilisateur de la base de données MySQL. */
define( 'DB_USER', 'root' );

/** Mot de passe de la base de données MySQL. */
define( 'DB_PASSWORD', '' );

/** Adresse de l’hébergement MySQL. */
define( 'DB_HOST', 'localhost' );

/** Jeu de caractères à utiliser par la base de données lors de la création des tables. */
define( 'DB_CHARSET', 'utf8mb4' );

/**
 * Type de collation de la base de données.
 * N’y touchez que si vous savez ce que vous faites.
 */
define( 'DB_COLLATE', '' );

/**#@+
 * Clés uniques d’authentification et salage.
 *
 * Remplacez les valeurs par défaut par des phrases uniques !
 * Vous pouvez générer des phrases aléatoires en utilisant
 * {@link https://api.wordpress.org/secret-key/1.1/salt/ le service de clés secrètes de WordPress.org}.
 * Vous pouvez modifier ces phrases à n’importe quel moment, afin d’invalider tous les cookies existants.
 * Cela forcera également tous les utilisateurs à se reconnecter.
 *
 * @since 2.6.0
 */
define( 'AUTH_KEY',         'oFG>[]}(IBu%09Fc/ENk>ZaHS&w^Sp3ubfa,U^;B|mdv@oc&mTv):6}yan^f4DBF' );
define( 'SECURE_AUTH_KEY',  'uApe-kRxEE26q!eOu{A=>8KZT,m`%u2(2v_-J;`|#K=P><u*j$@=W5uDE$8e17Q[' );
define( 'LOGGED_IN_KEY',    'R3_<)DvfXT72eq`@g7*R e4K&4o;cXd9Q#rY~n;N4715xr,fT4xTWX85+JgpQHqD' );
define( 'NONCE_KEY',        'FWQQNI[wog*GO3.{2,_R/O5w~nO8&;@o3UjTh7{9nLjm>O%suP@=Hi/dZf;ldWA*' );
define( 'AUTH_SALT',        '@pcwx4xe8^Q~B$L_rK<g=LO5m@I2#+Q+=~@,&qAz#o={ykHhs)$Q@ZgPwU>{8/>q' );
define( 'SECURE_AUTH_SALT', '1B4mlLo*]|EqPS7$CwBNSSJTB7+V^>a,<}udpmhFh>]^C2c:~v%v?/TQ(]T~yI.m' );
define( 'LOGGED_IN_SALT',   './0?{dL#8!|O>s$`Pfo#!Xf|rsI7&<a!(I(Lhn#gya8<LCvP9J{4LCr5y*ErgzvD' );
define( 'NONCE_SALT',       ':I==sDG^D, l{ @*V)pk!^sjT_%tG}(@tMA?HTBldscZm$V)FgiACT ~Ef}U4bx1' );
/**#@-*/

/**
 * Préfixe de base de données pour les tables de WordPress.
 *
 * Vous pouvez installer plusieurs WordPress sur une seule base de données
 * si vous leur donnez chacune un préfixe unique.
 * N’utilisez que des chiffres, des lettres non-accentuées, et des caractères soulignés !
 */
$table_prefix = 'wp_';

/**
 * Pour les développeurs : le mode déboguage de WordPress.
 *
 * En passant la valeur suivante à "true", vous activez l’affichage des
 * notifications d’erreurs pendant vos essais.
 * Il est fortemment recommandé que les développeurs d’extensions et
 * de thèmes se servent de WP_DEBUG dans leur environnement de
 * développement.
 *
 * Pour plus d’information sur les autres constantes qui peuvent être utilisées
 * pour le déboguage, rendez-vous sur le Codex.
 *
 * @link https://fr.wordpress.org/support/article/debugging-in-wordpress/
 */
define( 'WP_DEBUG', false );

/* C’est tout, ne touchez pas à ce qui suit ! Bonne publication. */

/** Chemin absolu vers le dossier de WordPress. */
if ( ! defined( 'ABSPATH' ) )
  define( 'ABSPATH', dirname( __FILE__ ) . '/' );

/** Réglage des variables de WordPress et de ses fichiers inclus. */
require_once( ABSPATH . 'wp-settings.php' );
