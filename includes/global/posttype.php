<?php
/**
 * Post Types class
 * 
 * @package  Page Generator Pro
 * @author   Tim Carr
 * @version  1.2.3
 */
class Page_Generator_Pro_PostType {

    /**
     * Holds the class object.
     *
     * @since   1.2.3
     *
     * @var     object
     */
    public static $instance;

    /**
     * Holds the Post Type Name for Groups
     *
     * @since   1.3.8
     *
     * @var     string
     */
    public $post_type_name = 'page-generator-pro';

    /**
     * Constructor
     *
     * @since 1.2.3
     */
    public function __construct() {

        // Register post types
        add_action( 'init', array( $this, 'register_post_types' ) );
        
    }

    /**
    * Registers Custom Post Types
    *
    * @since 1.2.3.0
    */
    public function register_post_types() {

        register_post_type( $this->post_type_name, array(
            'labels' => array(
                'name'              => _x( 'Page Generator Pro &raquo; Groups', 'post type general name' ),
                'singular_name'     => _x( 'Group', 'post type singular name' ),
                'menu_name'         => __( 'Page Generator Pro', 'page-generator-pro' ),
                'add_new'           => _x( 'Add New', 'page-generator-pro' ),
                'add_new_item'      => __( 'Add New Group', 'page-generator-pro'),
                'edit_item'         => __( 'Page Generator Pro &raquo; Edit Group', 'page-generator-pro'),
                'new_item'          => __( 'Page Generator Pro &raquo; New Group', 'page-generator-pro'),
                'view_item'         => __( 'View Group', 'page-generator-pro'),
                'search_items'      => __( 'Search Groups', 'page-generator-pro' ),
                'not_found'         => __( 'No Groups found', 'page-generator-pro' ),
                'not_found_in_trash'=> __( 'No Groups found in Trash', 'page-generator-pro' ), 
                'parent_item_colon' => ''
            ),
            'description'       => __( 'Page Generator Groups', 'page-generator-pro' ),
            'public'            => false,   
            'publicly_queryable'=> true,    // Needs to be true for frontend Page Builders
            'exclude_from_search'=> true,
            'show_ui'           => true,
            'show_in_menu'      => false,
            'menu_position'     => 9999,
            'menu_icon'         => 'dashicons-admin-network',
            'capability_type'   => 'page',
            'hierarchical'      => false,
            'supports'          => array( 'title', 'editor' ),
            'has_archive'       => false,
            'show_in_nav_menus' => false,
        ) );

    }

    /**
     * Returns the singleton instance of the class.
     *
     * @since   1.2.3
     *
     * @return  object Class.
     */
    public static function get_instance() {

        if ( ! isset( self::$instance ) && ! ( self::$instance instanceof self ) ) {
            self::$instance = new self;
        }

        return self::$instance;

    }

}