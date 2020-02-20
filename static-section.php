<?php
/**
 * Plugin Name: Disciple Tools Extension - Static Section
 * Plugin URI: https://github.com/ZumeProject/disciple-tools-static-section
 * Description: This DT extension adds either a top tab of section to metrics and allows you to build iframe or html content into pages.
 * Version:  0.1.0
 * Author URI: https://github.com/DiscipleTools
 * GitHub Plugin URI: https://github.com/ZumeProject/disciple-tools-static-section
 * Requires at least: 4.7.0
 * (Requires 4.7+ because of the integration of the REST API at 4.7 and the security requirements of this milestone version.)
 * Tested up to: 5.3
 *
 * @package Disciple_Tools
 * @link    https://github.com/DiscipleTools
 * @license GPL-2.0 or later
 *          https://www.gnu.org/licenses/gpl-2.0.html
 */

if ( ! defined( 'ABSPATH' ) ) { exit; } // Exit if accessed directly

add_action( 'after_setup_theme', function (){
    $required_dt_theme_version = '0.22.0';
    $wp_theme = wp_get_theme();
    $version = $wp_theme->version;
    /*
     * Check if the Disciple.Tools theme is loaded and is the latest required version
     */
    $is_theme_dt = strpos( $wp_theme->get_template(), "disciple-tools-theme" ) !== false || $wp_theme->name === "Disciple Tools";
    if ( !$is_theme_dt || version_compare( $version, $required_dt_theme_version, "<" ) ) {
        if ( ! is_multisite() ) {
            add_action('admin_notices', function () {
                ?>
                <div class="notice notice-error notice-static_section is-dismissible" data-notice="static_section">
                    Disciple Tools Theme not active or not latest version for Static Section plugin.
                </div><?php
            });
        }

        return false;
    }
    /**
     * Load useful function from the theme
     */
    if ( !defined( 'DT_FUNCTIONS_READY' ) ){
        require_once get_template_directory() . '/dt-core/global-functions.php';
    }
    /*
     * Don't load the plugin on every rest request. Only those with the 'sample' namespace
     */
    $is_rest = dt_is_rest();
    if ( !$is_rest || strpos( dt_get_url_path(), 'sample' ) != false ){
        return Static_Section::instance();
    }
    return false;
} );


/**
 * Class Static_Section
 */
class Static_Section {

    public $token = 'static_section';
    public $title = 'Static Section';
    public $permissions = 'manage_dt';

    /**  Singleton */
    private static $_instance = null;
    public static function instance() {
        if ( is_null( self::$_instance ) ) {
            self::$_instance = new self();
        }
        return self::$_instance;
    }
    /**
     * Constructor function.
     * @access  public
     * @since   0.1.0
     */
    public function __construct() {

        if ( is_admin() ) {
            add_action( "admin_menu", [ $this, "register_menu" ] );
        }
    } // End __construct()


    /**
     * Loads the subnav page
     * @since 0.1
     */
    public function register_menu() {
        add_menu_page( 'Extensions (DT)', 'Extensions (DT)', $this->permissions, 'dt_extensions', [ $this, 'extensions_menu' ], 'dashicons-admin-generic', 59 );
        add_submenu_page( 'dt_extensions', $this->title, $this->title, $this->permissions, $this->token, [ $this, 'content' ] );
    }

    /**
     * Menu stub. Replaced when Disciple Tools Theme fully loads.
     */
    public function extensions_menu() {}

    /**
     * Builds page contents
     * @since 0.1
     */
    public function content() {

        if ( !current_user_can( $this->permissions ) ) { // manage dt is a permission that is specific to Disciple Tools and allows admins, strategists and dispatchers into the wp-admin
            wp_die( 'You do not have sufficient permissions to access this page.' );
        }

        ?>
        <div class="wrap">
            <h2><?php echo esc_html( $this->title ) ?></h2>
            <div class="wrap">
                <div id="poststuff">
                    <div id="post-body" class="metabox-holder columns-2">
                        <div id="post-body-content">
                            <!-- Main Column -->

                            <?php $this->main_column(); ?>

                            <!-- End Main Column -->
                        </div><!-- end post-body-content -->
                        <div id="postbox-container-1" class="postbox-container">
                            <!-- Right Column -->

                            <?php $this->right_column(); ?>

                            <!-- End Right Column -->
                        </div><!-- postbox-container 1 -->
                        <div id="postbox-container-2" class="postbox-container">
                        </div><!-- postbox-container 2 -->
                    </div><!-- post-body meta box container -->
                </div><!--poststuff end -->
            </div><!-- wrap end -->
        </div><!-- End wrap -->

        <?php
    }

    public function main_column() {
        $this->process_postback();
        ?>
        <form method="post">
            <?php wp_nonce_field('static-section' . get_current_user_id(), 'static-section-nonce', true, true  ) ?>
            <!-- Title -->
            <table class="widefat striped">
                <thead>
                <tr>
                    <th>Tab Title</th>
                    <th></th>
                </tr>
                </thead>
                <tbody>
                <tr>
                    <td>
                        <input style="width:100%" />
                    </td>
                    <td style="text-align:right;"><button class="button">save</button></td>
                </tr>
                </tbody>
            </table>
            <br>
            <!-- End Box -->

            <!-- Menu Items -->
            <table class="widefat striped">
                <thead>
                <tr>
                    <th>Menu</th>
                    <th style="text-align:right;"><button class="button" onclick="add_new_section()">add</button></th>
                </tr>
                </thead>
                <tbody>
                <tr>
                    <td colspan="2" id="menu-box-wrapper"><!-- Menu Items --></td>
                </tr>
                </tbody>
            </table>
            <br>
        </form>
        <!-- End Box -->
        <script>
            function add_new_section() {
                jQuery('#menu-box-wrapper').append(`
                    <table class="widefat striped">
                        <tbody>
                        <tr>
                            <td>
                                Navigation Title<br>
                                <input style="width:100%" />
                            </td>
                        </tr>
                        <tr>
                            <td>
                                Page Content<br>
                                <textarea style="width:100%; height:100px;"></textarea>
                            </td>
                        </tr>
                        <tr style="text-align:right;">
                            <td>
                                <button class="button">save</button> <button class="button">delete</button>
                            </td>
                        </tr>
                        </tbody>
                    </table>
                    <br>
                `)
            }
        </script>
        <?php
    }

    public function right_column() {
        ?>
        <!-- Box -->
        <table class="widefat striped">
            <thead>
            <tr><th>Instructions</th></tr>
            </thead>
            <tbody>
            <tr>
                <td>
                    <ul>
                        <li>Purpose:</li>
                        <li>What can be done in boxes:</li>
                    </ul>
                </td>
            </tr>
            </tbody>
        </table>
        <br>
        <!-- End Box -->
        <?php
    }

    public function process_postback() {
        if ( isset( $_POST ) ) {

        }

    }

    /**
     * Method that runs only when the plugin is activated.
     *
     * @since  0.1
     * @access public
     * @return void
     */
    public static function activation() {

    }

    /**
     * Method that runs only when the plugin is deactivated.
     *
     * @since  0.1
     * @access public
     * @return void
     */
    public static function deactivation() {

    }

    /**
     * Magic method to output a string if trying to use the object as a string.
     *
     * @since  0.1
     * @access public
     * @return string
     */
    public function __toString() {
        return $this->token;
    }

    /**
     * Magic method to keep the object from being cloned.
     *
     * @since  0.1
     * @access public
     * @return void
     */
    public function __clone() {
        _doing_it_wrong( __FUNCTION__, esc_html( 'Whoah, partner!' ), '0.1' );
    }

    /**
     * Magic method to keep the object from being unserialized.
     *
     * @since  0.1
     * @access public
     * @return void
     */
    public function __wakeup() {
        _doing_it_wrong( __FUNCTION__, esc_html( 'Whoah, partner!' ), '0.1' );
    }

    /**
     * Magic method to prevent a fatal error when calling a method that doesn't exist.
     *
     * @param string $method
     * @param array $args
     *
     * @return null
     * @since  0.1
     * @access public
     */
    public function __call( $method = '', $args = array() ) {
        // @codingStandardsIgnoreLine
        _doing_it_wrong( __FUNCTION__, esc_html('Whoah, partner!'), '0.1' );
        unset( $method, $args );
        return null;
    }
}

// Register activation hook.
register_activation_hook( __FILE__, [ 'Static_Section', 'activation' ] );
register_deactivation_hook( __FILE__, [ 'Static_Section', 'deactivation' ] );