<?php
/**
* Administration class
* 
* @package Page_Generator_Pro
* @author Tim Carr
* @version 1.0
*/
class Page_Generator_Pro_Admin {

    /**
     * Holds the class object.
     *
     * @since   1.1.3
     *
     * @var     object
     */
    public static $instance;

    /**
     * Holds the base object.
     *
     * @since   1.2.1
     *
     * @var     object
     */
    public $base;

    /**
     * Holds success and error notices
     *
     * @since   1.3.8
     *
     * @var     array
     */
    public $notices = array(
        'success'   => array(),
        'error'     => array(),
    );

    /**
    * Constructor
    *
    * @since 1.0
    */
    public function __construct() {

        // Admin CSS, JS and Menu
        add_action( 'admin_enqueue_scripts', array( $this, 'admin_scripts_css' ) );
        add_action( 'admin_menu', array( $this, 'admin_menu' ), 8 );
        add_action( 'parent_file', array( $this, 'admin_menu_hierarchy_correction' ), 999 );

        // Localization
        add_action( 'plugins_loaded', array( $this, 'load_language_files' ) );

    }

    /**
     * Enqueues CSS and JS
     *
     * @since   1.0.0
     */
    public function admin_scripts_css() {

        // Get base instance
        $this->base = ( class_exists( 'Page_Generator_Pro' ) ? Page_Generator_Pro::get_instance() : Page_Generator::get_instance() );

        // Get current screen
        $screen = get_current_screen();

        // CSS - always load
        add_editor_style( $this->base->plugin->url . 'assets/css/admin.css' );
        wp_enqueue_style( $this->base->plugin->name . '-admin', $this->base->plugin->url . 'assets/css/admin.css', array(), $this->base->plugin->version );

        // JS - always load
        
        // Don't load anything else if we're not on a Plugin screen
        if ( strpos( $screen->base, $this->base->plugin->name ) === false && $screen->post_type != Page_Generator_Pro_PostType::get_instance()->post_type_name ) {
            return;
        }
    
        // Plugin Admin
        // These scripts are registered in _modules/dashboard/dashboard.php
        wp_enqueue_script( 'wpzinc-admin-conditional' );
        wp_enqueue_script( 'wpzinc-admin-inline-search' );
        wp_enqueue_script( 'wpzinc-admin-tags' );
        wp_enqueue_script( 'wpzinc-admin' );
        
        // JS
        wp_enqueue_script( 'jquery-ui-progressbar' );
        wp_enqueue_script($this->base->plugin->name . '-synchronous-ajax', $this->base->plugin->url . 'assets/js/synchronous-ajax.js', array( 'jquery' ), $this->base->plugin->version, true );
        wp_enqueue_script($this->base->plugin->name . '-admin', $this->base->plugin->url . 'assets/js/admin.js', array( 'jquery' ), $this->base->plugin->version, true );
 
        // CSS
        if ( class_exists( 'Page_Generator' ) ) {
            // Hide 'Add New' if a Group exists
            $number_of_groups = Page_Generator_Pro_Groups::get_instance()->get_count();
            if ( $number_of_groups > 0 ) {
                ?>
                <style type="text/css">body.post-type-page-generator-pro a.page-title-action { display: none; }</style>
                <?php
            }
        }
        
    }

    /**
     * Add the Plugin to the WordPress Administration Menu
     *
     * @since   1.0.0
     */
    public function admin_menu() {

        global $submenu;
        
        // Get base instance
        $this->base = ( class_exists( 'Page_Generator_Pro' ) ? Page_Generator_Pro::get_instance() : Page_Generator::get_instance() );

        // Main Menu
        add_menu_page( $this->base->plugin->displayName, $this->base->plugin->displayName, 'manage_options', $this->base->plugin->name . '-keywords', array( $this, 'keywords_screen' ), 'dashicons-format-aside' );
        
        // Sub Menu
        $keywords_page = add_submenu_page( $this->base->plugin->name . '-keywords', __( 'Keywords', $this->base->plugin->name ), __( 'Keywords', $this->base->plugin->name ), 'manage_options', $this->base->plugin->name . '-keywords', array( $this, 'keywords_screen' ) );    
        $groups_page   = add_submenu_page( $this->base->plugin->name . '-keywords', __( 'Generate', $this->base->plugin->name ), __( 'Generate', $this->base->plugin->name ), 'manage_options', 'edit.php?post_type=' . Page_Generator_Pro_PostType::get_instance()->post_type_name );    
        $generate_page = add_submenu_page( $this->base->plugin->name . '-keywords', __( 'Generate', $this->base->plugin->name ), __( 'Generate', $this->base->plugin->name ), 'manage_options', $this->base->plugin->name . '-generate', array( $this, 'generate_screen' ) );    

        // Menus
        $upgrade_page = add_submenu_page( $this->base->plugin->name . '-keywords', __( 'Upgrade', $this->base->plugin->name ), __( 'Upgrade', $this->base->plugin->name ), 'manage_options', $this->base->plugin->name . '-upgrade', array( $this, 'upgrade_screen' ) );

    }

    /**
     * Upgrade Screen
     *
     * @since 1.4.0
     */
    public function upgrade_screen() {   
        // We never reach here, as we redirect earlier in the process
    }

    /**
     * Ensures this Plugin's top level Admin menu remains open when the user clicks on Groups
     * in the Menu, to access the Groups CPT
     *
     * @since   1.2.3
     *
     * @param   string  $parent_file    Parent Admin Menu File Name
     * @return  string                  Parent Admin Menu File Name
     */
    public function admin_menu_hierarchy_correction( $parent_file ) {

        global $current_screen;

        // Get base instance
        $this->base = ( class_exists( 'Page_Generator_Pro' ) ? Page_Generator_Pro::get_instance() : Page_Generator::get_instance() );

        // If we're creating or editing a Group, set the $parent_file to this Plugin's registered menu name
        if ( $current_screen->base == 'post' && $current_screen->post_type == Page_Generator_Pro_PostType::get_instance()->post_type_name ) {
            $parent_file = Page_Generator_Pro_PostType::get_instance()->post_type_name;
        }

        return $parent_file;

    }

    /**
     * Outputs the Keywords Screens
     *
     * @since 1.0.0
     */
    public function keywords_screen() {

        // Get Page
        $page = ( isset( $_REQUEST['page'] ) ? sanitize_text_field( $_REQUEST['page'] ) : '' );

        // Get command
        $cmd = ( ( isset($_GET['cmd'] ) ) ? $_GET['cmd'] : '' );
        switch ( $cmd ) {

            /**
            * Add / Edit Keyword
            */
            case 'form':
                // Get keyword from POST data or DB
                if ( isset( $_POST['keyword'] ) ) {
                    // Get keyword from POST data
                    $keyword = $_POST;
                } else if ( isset( $_GET['id'] ) ) {
                    // Get keyword from DB
                    $keyword = Page_Generator_Pro_Keywords::get_instance()->get_by_id( $_GET['id'] );
                }

                // Save keyword
                $result = $this->save_keyword();
                if ( is_wp_error( $result ) ) {
                    $this->notices['error'][] = $result->get_error_message();
                } else if ( is_numeric( $result ) ) {
                    $this->notices['success'][] = __( 'Keyword saved successfully.', $this->base->plugin->name ); 
                    $keyword['keywordID'] = absint( $result );
                }

                // View
                $view = 'views/admin/keywords-form.php';
                
                break;

            /**
            * Index Table
            */
            case 'delete':
            default: 
                // Delete keywords
                $result = $this->delete_keywords();
                if ( is_string( $result ) ) {
                    // Error - add to array of errors for output
                    $this->notices['error'][] = $result;
                } elseif ( $result === true ) {
                    // Success
                    $this->notices['success'][] = __( 'Keyword(s) deleted successfully.', $this->base->plugin->name ); 
                }
                
                // View
                $view = 'views/admin/keywords-table.php';
                
                break;   

        }

        // Load View
        include_once( $this->base->plugin->folder . $view ); 

    }

    /**
     * Save Keyword
     *
     * @since   1.0.0
     *
     * @return  mixed   WP_Error | int
     */
    public function save_keyword() {

        // Check if a POST request was made
        if ( ! isset( $_POST['submit'] ) ) {
            return false;
        }

        // Run security checks
        // Missing nonce 
        if ( ! isset( $_POST[ $this->base->plugin->name . '_nonce' ] ) ) { 
            return new WP_Error( __( 'Nonce field is missing. Settings NOT saved.', $this->base->plugin->name ) );
        }

        // Invalid nonce
        if ( ! wp_verify_nonce( $_POST[ $this->base->plugin->name . '_nonce' ], 'save_keyword' ) ) {
            return new WP_Error( __( 'Invalid nonce specified. Settings NOT saved.', $this->base->plugin->name ) );
        }

        // Get ID
        $id = ( ( isset($_REQUEST['id'] ) && ! empty( $_REQUEST['id'] ) ) ? $_REQUEST['id'] : '' );

        // Get keywords instance
        $instance = Page_Generator_Pro_Keywords::get_instance();

        // Build keyword and import data
        $keyword = array();
        $keyword['keyword'] = $_POST['keyword'];
        $keyword['data'] = $instance->import_file_data( $_POST['data'] );

        // Save Keyword
        return $instance->save( $keyword, $id );

    }

    /**
     * Delete Keyword(s), if commands to do this have been sent in the request
     *
     * @since   1.0.0
     *
     * @return  mixed   WP_Error | true
     */
    public function delete_keywords() {

        // Check an action exists
        if ( ! isset( $_POST['action'] ) && ! isset( $_POST['action2'] ) && ! isset( $_GET['cmd'] ) ) {
            return false;
        }

        // Set flag
        $deleted = false;

        // Get instance
        $instance = Page_Generator_Pro_Keywords::get_instance();

        // Bulk Delete
        if ( ( isset( $_POST['action2'] ) && $_POST['action2'] != '-1' ) ||
             ( isset( $_POST['action'] ) && $_POST['action'] != '-1' ) ) {

            $result = $instance->delete( $_POST['ids'] );
            if ( is_wp_error( $result ) ) {
                return $result;
            }

            $deleted = true;

        }

        // Single Delete
        if ( isset( $_GET['cmd'] ) && $_GET['cmd'] == 'delete' ) {
            $result = $instance->delete( $_GET['id'] );
            if ( is_wp_error( $result ) ) {
                return $result;
            }

            $deleted = true;

        }

        // If a delete happened, return true
        return $deleted;

    }

    /**
     * Generates content for the given Group
     *
     * @since 1.2.3
     */
    public function generate_screen() {

        // Get base instance
        $this->base = ( class_exists( 'Page_Generator_Pro' ) ? Page_Generator_Pro::get_instance() : Page_Generator::get_instance() );

        // Check a Group ID was supplied
        if ( ! isset( $_REQUEST['id'] ) ) {
            return;
        }
        $id = absint( $_REQUEST['id'] );

        // Get group settings
        $settings = Page_Generator_Pro_Groups::get_instance()->get_settings( $id );
        if ( ! $settings ) {
            return;
        }

        // Calculate how many pages could be generated
        $number_of_pages_to_generate = Page_Generator_Pro_Generate::get_instance()->get_max_number_of_pages( $id );
          
        // Check that the number of posts doesn't exceed the maximum that can be generated
        if ( $settings['numberOfPosts'] > $number_of_pages_to_generate ) {
            $settings['numberOfPosts'] = $number_of_pages_to_generate;
        }  

        // If no limit specified, set one now
        if ( $settings['numberOfPosts'] == 0 ) {
            if ( $settings['method'] == 'random' ) {
                $settings['numberOfPosts'] = 10;
            } else {
                $settings['numberOfPosts'] = $number_of_pages_to_generate;
            }
        }
        
        // Code which executes the process is in the view, as we use sync JS
        $view = 'views/admin/generate-run.php';

        // Load View
        include_once( $this->base->plugin->folder . $view );

    }

    /**
     * Loads plugin textdomain
     *
     * @since 1.0.0
     */
    public function load_language_files() {

        // Get base instance
        $this->base = ( class_exists( 'Page_Generator_Pro' ) ? Page_Generator_Pro::get_instance() : Page_Generator::get_instance() );

        load_plugin_textdomain( $this->base->plugin->name, false, $this->base->plugin->name . '/languages/' );

    } 

    /**
     * Returns the singleton instance of the class.
     *
     * @since 1.1.3
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