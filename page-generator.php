<?php
/**
* Plugin Name: Page Generator
* Plugin URI: http://www.wpzinc.com/plugins/page-generator-pro
* Version: 1.4.2
* Author: WP Zinc
* Author URI: http://www.wpzinc.com
* Description: Generate multiple Pages using dynamic content.
*/

/**
 * Page Generator Class
 * 
 * @package   Page_Generator
 * @author    Tim Carr
 * @version   1.0.0
 * @copyright WP Zinc
 */
class Page_Generator {

    /**
     * Holds the class object.
     *
     * @since 1.1.3
     *
     * @var object
     */
    public static $instance;

    /**
     * Holds the plugin information object.
     *
     * @since 1.0.0
     *
     * @var object
     */
    public $plugin = '';

    /**
     * Holds the dashboard class object.
     *
     * @since 1.1.6
     *
     * @var object
     */
    public $dashboard = '';

    /**
     * Constructor. Acts as a bootstrap to load the rest of the plugin
     *
     * @since 1.0.0
     */
    public function __construct() {

        // Plugin Details
        $this->plugin = new stdClass;
        $this->plugin->name         = 'page-generator';
        $this->plugin->displayName  = 'Page Generator';
        $this->plugin->version      = '1.4.2';
        $this->plugin->buildDate    = '2017-03-06 12:00:00';
        $this->plugin->requires     = 3.6;
        $this->plugin->tested       = '4.7.2';
        $this->plugin->folder       = plugin_dir_path( __FILE__ );
        $this->plugin->url          = plugin_dir_url( __FILE__ );
        $this->plugin->documentation_url= 'https://www.wpzinc.com/documentation/page-generator-pro';
        $this->plugin->support_url      = 'https://www.wpzinc.com/support';
        $this->plugin->upgrade_url      = 'https://www.wpzinc.com/plugins/page-generator-pro';
        $this->plugin->review_name      = 'page-generator';
        $this->plugin->review_notice    = sprintf( __( 'Thanks for using %s to generate content!', $this->plugin->name ), $this->plugin->displayName );

        // Upgrade Reasons
        $this->plugin->upgrade_reasons = array(
            array(
                __( 'Generate Unlimited, Unique Posts, Pages and Custom Post Types', $this->plugin->name ), 
                __( 'Create as many Generation Groups as you wish, each with different settings.', $this->plugin->name ),
            ),
            array(
                __( 'Automatically Generate Nearby Cities Keywords', $this->plugin->name ), 
                __( 'Enter a city name, country and radius to automatically build a keyword containing all nearby cities.', $this->plugin->name ),
            ),
            array(
                __( 'Full Spintax Support', $this->plugin->name ), 
                __( 'Insert spintax and nested spintax, with keywords, into any field, and Page Generator Pro will spin it.', $this->plugin->name ),
            ),
            array(
                __( 'Full Page Builder Support', $this->plugin->name ), 
                __( 'Works with Avada, Beaver Builder, BeTheme, Divi, Fusion Builder, Muffin Page Builder, SiteOrigin Page Builder and Visual Composer.', $this->plugin->name ),
            ),
            array(
                __( 'Custom Fields', $this->plugin->name ), 
                __( 'Define any number of Custom Meta Fields', $this->plugin->name ),
            ),
            array(
                __( 'Generate SEO Metadata', $this->plugin->name ), 
                __( 'Define custom field key/value pairs found in our Documentation, to ensure you generated Pages are SEO ready.  Supports AIOSEO and Yoast.', $this->plugin->name ),
            ),
            array(
                __( 'Advanced Scheduling Functionality', $this->plugin->name ), 
                __( 'Generate Pages to be published in the future. Each generated page can be scheduled relative to the previous page, to drip feed content.', $this->plugin->name ),
            ),
            array(
                __( 'Powerful Generation Methods', $this->plugin->name ), 
                __( 'Pro provides All, Sequential and Random generation methods when cycling through Keywords, as well as a Resume Index to generate Pages in smaller batches.', $this->plugin->name ),
            ),
            array(
                __( 'Page Attribute Support', $this->plugin->name ), 
                __( 'Define the Page Parent for your generated Pages.', $this->plugin->name ),
            ),
            array(
                __( 'Taxonomies', $this->plugin->name ), 
                __( 'Choose taxonomy terms for Posts and Custom Post Types.', $this->plugin->name ),
            ),
            array(
                __( 'Embed Rich Content', $this->plugin->name ), 
                __( 'Shortcodes are supplied to insert content from 500px, Google Maps, Wikipedia, Yelp! and YouTube.', $this->plugin->name ),
            ),
            array(
                __( 'WP-CLI Support', $this->plugin->name ), 
                __( 'Generate Pages faster using WP-CLI, if installed on your web host.', $this->plugin->name ),
            ),
        );

        // Dashboard Submodule
        if ( ! class_exists( 'WPZincDashboardWidget' ) ) {
            require_once( $this->plugin->folder . '_modules/dashboard/dashboard.php' );
        }
        $this->dashboard = new WPZincDashboardWidget( $this->plugin );

        // Admin
        if ( is_admin() ) {
            // Required class
            require_once( $this->plugin->folder . 'includes/admin/admin.php' );
            require_once( $this->plugin->folder . 'includes/admin/ajax.php' );
            require_once( $this->plugin->folder . 'includes/admin/common.php' );
            require_once( $this->plugin->folder . 'includes/admin/generate.php' );
            require_once( $this->plugin->folder . 'includes/admin/install.php' );
            require_once( $this->plugin->folder . 'includes/admin/groups.php' );
            require_once( $this->plugin->folder . 'includes/admin/keywords.php' );
            require_once( ABSPATH . 'wp-admin/includes/class-wp-list-table.php' );
            require_once( $this->plugin->folder . 'includes/admin/keywords-table.php' );

            // Init non-static classes
            $ajax = Page_Generator_Pro_AJAX::get_instance();
            $admin = Page_Generator_Pro_Admin::get_instance();
            $groups = Page_Generator_Pro_Groups::get_instance();

            // Init upgrade routine
            add_action( 'init', array( $this, 'upgrade' ) );
        }
 
        // Global
        require_once( $this->plugin->folder . 'includes/global/posttype.php' );
        require_once( $this->plugin->folder . 'includes/global/settings.php' );

        // Init non-static classes
        $posttype = Page_Generator_Pro_PostType::get_instance();

    }

    /**
     * Runs the upgrade routine once the plugin has loaded
     *
     * @since 1.1.7
     */
    public function upgrade() {

        // Run upgrade routine
        Page_Generator_Pro_Install::get_instance()->upgrade();

    }

    /**
     * Returns the singleton instance of the class.
     *
     * @since 1.1.6
     *
     * @return object Class.
     */
    public static function get_instance() {

        if ( ! isset( self::$instance ) && ! ( self::$instance instanceof self ) ) {
            self::$instance = new self;
        }

        return self::$instance;

    }

}

// Initialise class
$page_generator = Page_Generator::get_instance();

// Register activation hooks
register_activation_hook( __FILE__, array( 'Page_Generator_Pro_Install', 'activate' ) );
add_action( 'activate_wpmu_site', array( 'Page_Generator_Pro_Install', 'activate_wpmu_site' ) );