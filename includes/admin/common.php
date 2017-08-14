<?php
/**
* Common class
* 
* @package WP Zinc
* @subpackage Page Generator Pro
* @author Tim Carr
* @version 1.0
*/
class Page_Generator_Pro_Common {

    /**
     * Holds the class object.
     *
     * @since 1.1.3
     *
     * @var object
     */
    public static $instance;

    /**
     * Helper method to retrieve public Post Types
     *
     * @since 1.1.3
     *
     * @return array Public Post Types
     */
    public function get_post_types() {

        // Get public Post Types
        $types = get_post_types( array(
            'public' => true,
        ), 'objects' );

        // Filter out excluded post types
        $excluded_types = $this->get_excluded_post_types();
        if ( is_array( $excluded_types ) ) {
            foreach ( $excluded_types as $excluded_type ) {
                unset( $types[ $excluded_type ] );
            }
        }

        // Return filtered results
        return apply_filters( 'page_generator_pro_get_post_types', $types );

    }

    /**
     * Helper method to retrieve hierarchical public Post Types
     *
     * @since 1.2.1
     *
     * @return array Public Post Types
     */
    public function get_hierarchical_post_types() {

        // Get public hierarchical Post Types
        $types = get_post_types( array(
            'public'        => true,
            'hierarchical'  => true,
        ), 'objects' );

        // Filter out excluded post types
        $excluded_types = $this->get_excluded_post_types();
        if ( is_array( $excluded_types ) ) {
            foreach ( $excluded_types as $excluded_type ) {
                unset( $types[ $excluded_type ] );
            }
        }

        // Return filtered results
        return apply_filters( 'page_generator_pro_get_hierarchical_post_types', $types );

    }

    /**
     * Helper method to retrieve excluded Post Types
     *
     * @since 1.1.3
     *
     * @return array Excluded Post Types
     */
    public function get_excluded_post_types() {

        // Get excluded Post Types
        $types = array(
            'attachment',
            'revision',
            'nav_menu_item',
        );

        // Return filtered results
        return apply_filters( 'page_generator_pro_get_excluded_post_types', $types );

    }

    /**
     * Helper method to retrieve authors
     *
     * @since 1.1.3
     *
     * @return array Authors
     */
    public function get_authors() {

        // Get authors
        $authors = get_users( array(
             'orderby' => 'nicename',
        ) );

        // Return filtered results
        return apply_filters( 'page_generator_pro_get_authors', $authors );
        
    }

    /**
     * Helper method to retrieve post statuses
     *
     * @since 1.1.3
     *
     * @return array Post Statuses
     */
    public function get_post_statuses() {

        // Get statuses
        if ( class_exists( 'Page_Generator_Pro' ) ) {
            $statuses = array(
                'draft'     => __( 'Draft', 'page-generator-pro' ),
                'future'    => __( 'Scheduled', 'page-generator-pro' ),
                'private'   => __( 'Private', 'page-generator-pro' ),
                'publish'   => __( 'Publish', 'page-generator-pro' ),
            );
        } else {
            $statuses = array(
                'draft'     => __( 'Draft', 'page-generator-pro' ),
                'publish'   => __( 'Publish', 'page-generator-pro' ),
            );
        }

        // Return filtered results
        return apply_filters( 'page_generator_pro_get_post_statuses', $statuses );
        
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