<?php
/**
 * Groups class
 * 
 * @package  Page_Generator_Pro
 * @author   Tim Carr
 * @version  1.2.1
 */
class Page_Generator_Pro_Groups {

    /**
     * Holds the class object.
     *
     * @since 1.2.1
     *
     * @var object
     */
    public static $instance;

    /**
     * Holds the base class object.
     *
     * @since 1.2.3
     *
     * @var object
     */
    public $base;

    /**
     * Holds the common class object.
     *
     * @since 1.2.3
     *
     * @var object
     */
    public $common;

    /**
     * Stores Keywords available to the Group
     *
     * @since 1.2.3
     *
     * @var array
     */
    public $keywords;

    /**
     * Stores a Group's settings
     *
     * @since 1.2.3
     *
     * @var array
     */
    public $settings;

    /**
     * Stores success and error messages
     *
     * @since 1.2.3
     *
     * @var array
     */
    public $notices = array(
        'success'   => array(),
        'error'     => array(),
    );

    /**
     * Constructor
     *
     * @since 1.2.3
     */
    public function __construct() {

        // Process any notices that need to be displayed
        add_action( 'admin_notices', array( $this, 'admin_notices' ) );

        // Before Title
        add_action( 'edit_form_top', array( $this, 'output_keywords_dropdown_before_title' ) );

        // Meta Boxes
        add_action( 'add_meta_boxes', array( $this, 'add_meta_boxes' ) );

        // Save Group
        add_action( 'save_post', array( $this, 'save_post' ) );

        // Page Generator
        if ( class_exists( 'Page_Generator' ) ) {
            add_action( 'init', array( $this, 'limit_admin' ) );
            add_filter( 'wp_insert_post_empty_content', array( $this, 'limit_xml_rpc' ), 10, 2 );
        }

    }

    /**
     * Creates a single Group, if none exist, when the Plugin is activated.
     *
     * @since   1.3.8
     *
     * @global  $wpdb   WordPress DB Object
     */
    public function activate() {

        // Bail if we already have at least one Group
        $number_of_groups = $this->get_count();
        if ( $number_of_groups > 0 ) {
            return;
        }

        // Create Group
        wp_insert_post( array(
            'post_type'     => Page_Generator_Pro_PostType::get_instance()->post_type_name,
            'post_status'   => 'publish',
            'post_title'    => __( 'Title' ),
            'post_content'  => __( 'Edit this content, replacing it with the content you want to generate. You can use {keywords} here too.  Need help? Visit <a href="https://www.wpzinc.com/documentation/page-generator-pro/generate/" target="_blank">https://www.wpzinc.com/documentation/page-generator-pro/generate/</a>' ),
        ) );

    }

    /**
     * Checks the transient to see if any admin notices need to be output now
     * i.e. if a Test or Delete call was made on this Group.
     *
     * @since   1.2.3
     */
    public function admin_notices() {

        global $post;

        // Don't do anything if we're not on this Plugin's CPT
        if ( empty( $post ) ) {
            return;
        }
        if ( get_post_type( $post ) != Page_Generator_Pro_PostType::get_instance()->post_type_name ) {
            return;
        }

        // Get current user
        $user = wp_get_current_user();
        
        // Check the transient for any notices
        $notices = get_transient( 'page_generator_pro_groups_notices_' . $post->ID . '_' . $user->ID );
        if ( empty( $notices ) ) {
            return;
        }

        // If here, notice(s) exist
        // Store them in the class variable and delete the transient
        if ( isset( $notices['success'] ) ) {
            $this->notices['success'] = $notices['success'];
        }
        if ( isset( $notices['error'] ) ) {
            $this->notices['error'] = $notices['error'];
        }
        delete_transient( 'page_generator_pro_groups_notices_' . $post->ID . '_' . $user->ID );

        // Output success notice(s)
        if ( is_array( $this->notices['success'] ) ) {
            foreach ( $this->notices['success'] as $message ) {
                ?>
                <div class="notice notice-success is-dismissible">
                    <p><?php echo $message; ?></p>
                </div>
                <?php
            }
        }

        // Output error notice(s)
        if ( is_array( $this->notices['error'] ) ) {
            foreach ( $this->notices['error'] as $message ) {
                ?>
                <div class="notice notice-error is-dismissible">
                    <p><?php echo $message; ?></p>
                </div>
                <?php
            }
        }

    }
   
    /**
     * Outputs the Keywords Dropdown before the Title field
     *
     * @since   1.2.3
     *
     * @param   WP_Post     $post   Custom Post Type's Post
     */
    public function output_keywords_dropdown_before_title( $post ) {

        // Don't do anything if we're not on this Plugin's CPT
        if ( get_post_type( $post ) !== Page_Generator_Pro_PostType::get_instance()->post_type_name ) {
            return;
        }

        // Get all available keywords, post types, taxonomies, authors and other settings that we might use on the admin screen
        $this->keywords = Page_Generator_Pro_Keywords::get_instance()->get_all( 'keyword', 'ASC', -1 );

        // Load view
        include( $this->base->plugin->folder . 'views/admin/generate-title-keywords.php' ); 

    }

    /**
     * Registers meta boxes for the Generate Custom Post Type
     *
     * @since 1.2.3
     */
    public function add_meta_boxes() {

        // Get instances
        $this->base   = ( class_exists( 'Page_Generator_Pro' ) ? Page_Generator_Pro::get_instance() : Page_Generator::get_instance() );
        $this->common = Page_Generator_Pro_Common::get_instance();
        $post_type_instance = Page_Generator_Pro_PostType::get_instance();

        // Remove all metaboxes
        $this->remove_all_meta_boxes();

        // Permalink
        add_meta_box( 
            $post_type_instance->post_type_name . '-permalink', 
            __( 'Permalink', $this->base->plugin->name ), 
            array( $this, 'output_meta_box_permalink' ), 
            $post_type_instance->post_type_name,
            'normal' 
        );

        // Author
        add_meta_box( 
            $post_type_instance->post_type_name . '-author', 
            __( 'Author', $this->base->plugin->name ), 
            array( $this, 'output_meta_box_author' ), 
            $post_type_instance->post_type_name,
            'normal'  
        );

        // Discussion
        add_meta_box( 
            $post_type_instance->post_type_name . '-discussion', 
            __( 'Discussion', $this->base->plugin->name ), 
            array( $this, 'output_meta_box_discussion' ), 
            $post_type_instance->post_type_name,
            'normal'  
        );

        // Upgrade
        if ( class_exists( 'Page_Generator' ) ) {
            add_meta_box( 
                $post_type_instance->post_type_name . '-upgrade', 
                __( 'Upgrade', $this->base->plugin->name ), 
                array( $this, 'output_meta_box_upgrade' ), 
                $post_type_instance->post_type_name,
                'normal'  
            );
        }

        /**
         * Sidebar
         */

        // Actions Top
        add_meta_box( 
            $post_type_instance->post_type_name . '-actions', 
            __( 'Actions', $this->base->plugin->name ), 
            array( $this, 'output_meta_box_actions' ), 
            $post_type_instance->post_type_name,
            'side'
        );

        // Publish
        add_meta_box( 
            $post_type_instance->post_type_name . '-publish', 
            __( 'Publish', $this->base->plugin->name ), 
            array( $this, 'output_meta_box_publish' ), 
            $post_type_instance->post_type_name,
            'side'
        );

        // Generation
        add_meta_box( 
            $post_type_instance->post_type_name . '-generation', 
            __( 'Generation', $this->base->plugin->name ), 
            array( $this, 'output_meta_box_generation' ), 
            $post_type_instance->post_type_name,
            'side'
        );

        // Attributes
        add_meta_box( 
            $post_type_instance->post_type_name . '-attributes', 
            __( 'Attributes', $this->base->plugin->name ), 
            array( $this, 'output_meta_box_attributes' ), 
            $post_type_instance->post_type_name,
            'side'
        );

    }

    /**
     * Defines the meta boxes which are permitted for display on the Groups screen.
     *
     * @since   1.2.3
     *
     * @return  array   Permitted Meta Boxes
     */
    public function permitted_meta_boxes() {

        // Filter permitted meta boxes
        $permitted_meta_boxes = apply_filters( 'page_generator_pro_groups_permitted_meta_boxes', array() );

        return $permitted_meta_boxes;

    }

    /**
     * Removes metaboxes added by most other Plugins and WordPress, so we can contrl
     * the UI better.
     *
     * @since   1.2.3
     *
     * @global  array   $wp_meta_boxes  Array of registered metaboxes.
     */
    public function remove_all_meta_boxes() {

        global $wp_meta_boxes;

        // Get permitted meta boxes
        $permitted_meta_boxes = $this->permitted_meta_boxes();

        // Bail if no meta boxes for this CPT exist
        if ( ! isset( $wp_meta_boxes['page-generator-pro'] ) ) {
            return;
        }
        
        // Iterate through all registered meta boxes, removing those that aren't permitted
        foreach ( $wp_meta_boxes['page-generator-pro'] as $position => $contexts ) {
            foreach ( $contexts as $context => $meta_boxes ) {
                foreach ( $meta_boxes as $meta_box_id => $meta_box ) {
                    // If this meta box isn't in the array of permitted meta boxes, remove it now
                    if ( empty( $permitted_meta_boxes) || ! in_array( $meta_box_id, $permitted_meta_boxes ) ) {
                        unset( $wp_meta_boxes['page-generator-pro'][ $position ][ $context ][ $meta_box_id ] );
                    }
                }
            }
        }

    }

    /**
     * Outputs the Permalink Meta Box
     *
     * @since 1.2.3
     *
     * @param   WP_Post     $post   Custom Post Type's Post
     */
    public function output_meta_box_permalink( $post ) {

        // Get all available keywords, post types, taxonomies, authors and other settings that we might use on the admin screen
        $this->keywords = Page_Generator_Pro_Keywords::get_instance()->get_all( 'keyword', 'ASC', -1 );

        // Load view
        include( $this->base->plugin->folder . 'views/admin/generate-meta-box-permalink.php' ); 

    }

    /**
     * Outputs the Author Meta Box
     *
     * @since   1.2.3
     *
     * @param   WP_Post     $post   Custom Post Type's Post
     */
    public function output_meta_box_author( $post ) {

        // Get options
        $authors = Page_Generator_Pro_Common::get_instance()->get_authors();

        // Load view
        include( $this->base->plugin->folder . 'views/admin/generate-meta-box-author.php' ); 

    }

    /**
     * Outputs the Discussion Meta Box
     *
     * @since   1.2.3
     *
     * @param   WP_Post     $post   Custom Post Type's Post
     */
    public function output_meta_box_discussion( $post ) {

        // Load view
        include( $this->base->plugin->folder . 'views/admin/generate-meta-box-discussion.php' ); 

    }

    /**
     * Outputs the Upgrade Meta Box
     *
     * @since   1.3.8
     *
     * @param   WP_Post     $post   Custom Post Type's Post
     */
    public function output_meta_box_upgrade( $post ) {

        // Load view
        include( $this->base->plugin->folder . '/_modules/dashboard/views/footer-upgrade-embedded.php' );

    }

    /**
     * Outputs the Actions Sidebar Meta Box
     *
     * @since   1.2.3
     *
     * @param   WP_Post     $post   Custom Post Type's Post
     */
    public function output_meta_box_actions( $post ) {

        // Get settings, as this is the first meta box to load
        $this->settings = $this->get_settings( $post->ID );

        // Load view
        include( $this->base->plugin->folder . 'views/admin/generate-meta-box-actions.php' ); 

    }

    /**
     * Outputs the Publish Sidebar Meta Box
     *
     * @since   1.2.3
     *
     * @param   WP_Post     $post   Custom Post Type's Post
     */
    public function output_meta_box_publish( $post ) {

        // Get options
        $statuses               = $this->common->get_post_statuses();
        
        // Load view
        include( $this->base->plugin->folder . 'views/admin/generate-meta-box-publish.php' ); 

    }

    /**
     * Outputs the Generation Sidebar Meta Box
     *
     * @since   1.2.3
     *
     * @param   WP_Post     $post   Custom Post Type's Post
     */
    public function output_meta_box_generation( $post ) {

        // Load view
        include( $this->base->plugin->folder . 'views/admin/generate-meta-box-generation.php' ); 

    }

    /**
     * Outputs the Attributes Sidebar Meta Box
     *
     * @since   1.2.3
     *
     * @param   WP_Post     $post   Custom Post Type's Post
     */
    public function output_meta_box_attributes( $post ) {

        // Get options
        $hierarchical_post_types = $this->common->get_hierarchical_post_types();

        // Build string of hierarchal post types to use as a selector class
        $hierarchical_post_types_class = '';
        if ( is_array( $hierarchical_post_types ) && count( $hierarchical_post_types ) > 0 ) {
            foreach ( $hierarchical_post_types as $type => $post_type ) {
                $hierarchical_post_types_class .= $type . ' ';
            }
        }

        // Load view
        include( $this->base->plugin->folder . 'views/admin/generate-meta-box-attributes.php' ); 

    }

    /**
     * Defines a default settings structure when creating a new group
     *
     * @since   1.2.0
     *
     * @return  array   Group
     */
    public function get_defaults() {

        // Define defaults
        $defaults = array(
            'title'         => '',
            'permalink'     => '',
            'content'       => '',
            'excerpt'       => '',
            'meta'          => array(),
            'rotateAuthors' => 0,
            'author'        => '',
            'comments'      => 0,
            'trackbacks'    => 0,
            'type'          => 'page',
            'status'        => 'publish',
            'date_option'   => 'now',
            'date_specific' => date( 'Y-m-d' ),
            'date_min'      => date( 'Y-m-d', strtotime( '-1 week' ) ),
            'date_max'      => date( 'Y-m-d' ),
            'schedule'      => 1,
            'scheduleUnit'  => 'hours',
            'method'        => 'all',
            'numberOfPosts' => 0,
            'resumeIndex'   => 0,
            'pageParent'    => '',
            'pageTemplate'  => '',
            'tax'           => '',
            'featured_image_source'     => '',
            'featured_image'            => '',
            'featured_image_location'   => '',
        ); 

        // Allow devs to filter defaults.
        $defaults = apply_filters( 'page_generator_pro_groups_get_defaults', $defaults );

        // Return.
        return $defaults;
        
    }

    /**
     * Returns a Group's Settings by the given Group ID
     *
     * @since   1.2.1
     *
     * @param   int    $id  ID
     * @return  mixed       false | array
     */
    public function get_settings( $id ) {

        // Get settings
        $settings = get_post_meta( $id, '_page_generator_pro_settings', true );

        // If the result isn't an array, we're getting settings for a new Group, so just use the defaults
        if ( ! is_array( $settings ) ) {
            $settings = $this->get_defaults();
        } else {
            // Store the Post's Title and Content in the settings, for backward compat
            $post               = get_post( $id );
            $settings['title']  = $post->post_title;
            $settings['content']= $post->post_content;

            // Merge with defaults, so keys are always set
            $settings = array_merge( $this->get_defaults(), $settings );  
        }

        // Inject Page Builder Data
        if ( class_exists( 'Page_Generator_Pro_PageBuilders' ) ) {
            $settings = Page_Generator_Pro_PageBuilders::get_instance()->inject_post_meta_in_settings( $id, $settings );
        }

        // Add the generated pages count
        $settings['generated_pages_count'] = $this->get_generated_count_by_id( $id );

        // Return settings
        return $settings;

    }

    /**
     * Returns an array of all Groups with their Settings
     *
     * @since   1.2.3
     *
     * @return  array   Groups
     */
    public function get_all() {

        // Groups
        $groups = new WP_Query( array(
            'post_type'     => Page_Generator_Pro_PostType::get_instance()->post_type_name,
            'post_status'   => 'any',
            'posts_per_page'=> -1,
        ) );

        if ( count( $groups->posts ) == 0 ) {
            return false;
        }

        $groups_arr = array();
        foreach ( $groups->posts as $group ) {
            // Get settings
            $groups_arr[ $group->ID ] = $this->get_settings( $group->ID );
        }

        return $groups_arr;

    }

    /**
     * Get the number of Groups
     *
     * @since   1.3.8
     *
     * @return  int             Number of Generated Pages / Posts / CPTs
     */
    public function get_count() {

        $posts = new WP_Query( array(
            'post_type'             => Page_Generator_Pro_PostType::get_instance()->post_type_name,
            'post_status'           => 'publish',
            'posts_per_page'        => 1,
            'update_post_term_cache'=> false,
            'update_post_meta_cache'=> false,
            'fields'                => 'ids',
        ) );

        return count( $posts->posts );

    }

    /**
     * Get the number of Pages / Posts / CPTs generated by the given Group ID
     *
     * @since   1.2.3
     *
     * @param   int     $id     Group ID
     * @return  int             Number of Generated Pages / Posts / CPTs
     */
    public function get_generated_count_by_id( $id ) {

        $posts = new WP_Query( array (
            'post_type'     => 'any',
            'post_status'   => 'publish',
            'posts_per_page'=> -1,
            'meta_query'    => array(
                array(
                    'key'   => '_page_generator_pro_group',
                    'value' => absint( $id ),
                ),
            ),
            'update_post_term_cache'=> false,
            'update_post_meta_cache'=> false,
            'fields'                => 'ids',
        ) );

        return count( $posts->posts );

    }

    /**
     * Called when a Group is saved.
     *
     * @since   1.2.3
     *
     * @param   int     $post_id
     */
    public function save_post( $post_id ) {

        // Get base instance
        $this->base = ( class_exists( 'Page_Generator_Pro' ) ? Page_Generator_Pro::get_instance() : Page_Generator::get_instance() );

        // Bail if this isn't a Page Generator Pro Group that's being saved
        if ( get_post_type( $post_id ) != Page_Generator_Pro_PostType::get_instance()->post_type_name ) {
            return;
        }

        // Run security checks
        // Missing nonce 
        if ( ! isset( $_POST[ $this->base->plugin->name . '_nonce' ] ) ) { 
            return;
        }

        // Invalid nonce
        if ( ! wp_verify_nonce( $_POST[ $this->base->plugin->name . '_nonce' ], 'save_generate' ) ) {
            return;
        }

        // Call the main save function
        $this->save( $_POST[ $this->base->plugin->name ], $post_id );

        // Check which submit action was given, as we may need to run a test or redirect to the generate screen now.
        $action = $this->get_action();
        if ( ! $action ) {
            return;
        }

        // If here, we have a specific action to carry out
        $generate = Page_Generator_Pro_Generate::get_instance();
        switch ( $action ) {

            /**
             * Test
             */
            case 'test':
                $result = $generate->generate( $post_id, 0, true );
                if ( is_wp_error( $result ) ) {
                    $notices['error'][] = $result->get_error_message();
                } else {
                    $notices['success'][] = __( 'Test Page Generated at ', $this->base->plugin->name ) . ': ' . '<a href="' . $result . '" target="_blank">' . $result . '</a>';
                }
                break;

            /**
             * Generate
             */
            case 'generate':
                wp_redirect( 'admin.php?page=' . $this->base->plugin->name . '-generate&id=' . $post_id );
                die();
                break;

            /**
             * Delete Generated Content
             */
            case 'delete':
                $result = $generate->delete( $post_id );
                if ( is_wp_error( $result ) ) {
                    $notices['error'][] = $result->get_error_message();
                } else {
                    $notices['success'][] = __( 'Generated content deleted successfully.', $this->base->plugin->name );
                }
                break;

        }

        // Store success and/or error notices in a transient, which we'll load on the Post Edit redirect
        // and display, before clearing the transient.
        if ( isset( $notices ) ) {
            $user = wp_get_current_user();
            set_transient( 'page_generator_pro_groups_notices_' . $post_id . '_' . $user->ID, $notices, 15 );
        }

    }

    /**
     * Determines which submit button was pressed on the Groups add/edit screen
     *
     * @since   1.2.3
     *
     * @return  string  Action
     */
    private function get_action() {

        if ( isset( $_POST['test'] ) ) {
            return 'test';
        }

        if ( isset( $_POST['generate'] ) ) {
            return 'generate';
        }

        if ( isset( $_POST['delete'] ) ) {
            return 'delete';
        }

        if ( isset( $_POST['save'] ) ) {
            return 'save';
        }

        // No action given
        return false;
  
    }

    /**
     * Adds or edits a record, based on the given settings array.
     *
     * @since   1.2.1
     * 
     * @param   array  $settings    Array of settings to save
     * @param   int    $id          Post ID
     */
    public function save( $settings, $post_id = '' ) {

        // Merge with defaults, so keys are always set
        $settings = array_merge( $this->get_defaults(), $settings );

        // Clear out blank meta
        if ( isset( $settings['meta'] ) && is_array( $settings['meta'] ) && count( $settings['meta'] ) > 0 ) {
            foreach ( $settings['meta']['key'] as $index => $value ) {
                if ( empty( $value ) ) {
                    unset( $settings['meta']['key'][ $index ] );
                    unset( $settings['meta']['value'][ $index ] );
                }
            }
        }

        // Update post meta
        update_post_meta( $post_id, '_page_generator_pro_settings', $settings );

    }

    /**
     * Limit creating more than one Group via the WordPress Administration, by preventing
     * the 'Add New' functionality, and ensuring the user is always taken to the edit
     * screen of the single Group when they access the Post Type.
     *
     * @since   1.3.8
     */
    public function limit_admin() {

        global $pagenow;

        switch ( $pagenow ) {
            /**
             * Edit
             * WP_List_Table
             */
            case 'edit.php':
                // Bail if no Post Type is supplied
                if ( ! isset( $_REQUEST['post_type'] ) ) {
                    break;
                }

                // Fetch first group
                $groups = new WP_Query( array(
                    'post_type'     => Page_Generator_Pro_PostType::get_instance()->post_type_name,
                    'post_status'   => 'publish',
                    'posts_per_page'=> 1,
                ) );

                // Bail if no Groups exist, so the user can create one
                if ( count( $groups->posts ) == 0 ) {
                    break;
                }

                // Redirect to the Group's edit screen
                wp_safe_redirect( 'post.php?post=' . $groups->posts[0]->ID . '&action=edit' );
                die();

                break;

            /**
             * Add New
             */
            case 'post-new.php':
            case 'press-this.php':
                // Bail if we don't know the Post Type
                if ( ! isset( $_REQUEST['post_type'] ) ) {
                    break;
                }

                // Bail if we're not on our Group Post Type
                if ( $_REQUEST['post_type'] != Page_Generator_Pro_PostType::get_instance()->post_type_name ) {
                    break;
                }

                // Fetch first group
                $groups = new WP_Query( array(
                    'post_type'     => Page_Generator_Pro_PostType::get_instance()->post_type_name,
                    'post_status'   => 'publish',
                    'posts_per_page'=> 1,
                ) );

                // Bail if no Groups exist, so the user can create one
                if ( count( $groups->posts ) == 0 ) {
                    break;
                }

                // Redirect to the Group's edit screen
                wp_safe_redirect( 'post.php?post=' . $groups->posts[0]->ID . '&action=edit' );
                die();
                
                break;
        }
            
    }

    /**
     * Limit creating more than one Group via XML-RPC
     *
     * @since   1.3.8
     *
     * @param   bool    $limit  Limit XML-RPC
     * @param   array   $post   Post Data
     * @return                  Limit XML-RPC
     */
    public function limit_xml_rpc( $limit, $post = array() ) {

        // Bail if we're not on an XMLRPC request
        if ( ! defined( 'XMLRPC_REQUEST' ) ||  XMLRPC_REQUEST != true ) {
            return $limit;
        }
        
        // Bail if no Post Type specified
        if ( ! isset( $post['post_type'] ) ) {
            return $limit;
        }
        if ( $post['post_type'] != Page_Generator_Pro_PostType::get_instance()->post_type_name ) {
            return $limit;
        }

        // If here, we're trying to create a Group. Don't let this happen.
        return true;

    }
    
    /**
     * Returns the singleton instance of the class.
     *
     * @since   1.2.1
     *
     * @return  object  Class.
     */
    public static function get_instance() {

        if ( ! isset( self::$instance ) && ! ( self::$instance instanceof self ) ) {
            self::$instance = new self;
        }

        return self::$instance;

    }

}