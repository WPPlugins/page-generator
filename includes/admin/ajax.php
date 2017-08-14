<?php
/**
* AJAX class
* 
* @package Page_Generator_Pro
* @author Tim Carr
* @version 1.0
*/
class Page_Generator_Pro_AJAX {

    /**
     * Holds the class object.
     *
     * @since   1.1.3
     *
     * @var     object
     */
    public static $instance;

    /**
     * Constructor
     *
     * @since   1.0.0
     */
    public function __construct() {

        // AJAX Actions
        add_action( 'wp_ajax_page_generator_pro_generate', array( $this, 'generate' ) );

    }

    /**
     * Generates a Page, Post or CPT
     *
     * @since 1.0.0
     */
    public function generate() {

        // Sanitize inputs
        if ( ! isset( $_POST['id'] ) ) {
            wp_send_json_error( __( 'No group ID was specified!', 'page-generator-pro' ) );
        }
        if ( ! isset( $_POST['current_index'] ) ) {
            wp_send_json_error( __( 'No current index was specified!', 'page-generator-pro' ) );
        }
        $group_id       = absint( $_POST['id'] );
        $current_index  = absint( $_POST['current_index'] );

        // Run
        $result = Page_Generator_Pro_Generate::get_instance()->generate( $group_id, $current_index, false );

        // Return error or success JSON
        if ( is_wp_error( $result ) ) {
            wp_send_json_error( $result->get_error_code() . ': ' . $result->get_error_message() );
        }

        // If here, run routine worked
        wp_send_json_success( array(
            'url' => $result,
        ) );

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