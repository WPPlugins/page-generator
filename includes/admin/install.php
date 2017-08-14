<?php
/**
* Install class
* 
* @package  Page_Generator_Pro
* @author   Tim Carr
* @version  1.0.0
*/
class Page_Generator_Pro_Install {

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
     * @since   1.3.8
     *
     * @var     object
     */
    public $base;

    /**
     * Activation routine
     * - Installs database tables as necessary
     *
     * @since 1.0.0
     *
     * @param bool $network_wide Network Wide activation
     */
    static public function activate( $network_wide = false ) {

        // Check if we are on a multisite install, activating network wide, or a single install
        if ( is_multisite() && $network_wide ) {
            // Multisite network wide activation
            // Iterate through each blog in multisite, creating table
            $sites = wp_get_sites( array( 
                'limit' => 0 
            ) );
            foreach ( $sites as $site ) {
                switch_to_blog( $site->blog_id );

                // Run activation routines for groups and keywords, to install tables etc.
                Page_Generator_Pro_Groups::get_instance()->activate();
                Page_Generator_Pro_Keywords::get_instance()->activate();
                
                restore_current_blog();
            }
        } else {
            // Run activation routines for groups and keywords, to install tables etc.
            Page_Generator_Pro_Groups::get_instance()->activate();
            Page_Generator_Pro_Keywords::get_instance()->activate();
        }

    }

    /**
     * Activation routine when a WPMU site is activated
     * - Installs database tables as necessary
     *
     * We run this because a new WPMU site may be added after the plugin is activated
     * so will need necessary database tables
     *
     * @since   1.1.2
     */
    public function activate_wpmu_site( $blog_id ) {

        switch_to_blog( $blog_id );
        $this->activate();
        restore_current_blog();

    }

    /**
     * Runs migrations for Pro to Pro version upgrades
     *
     * @since   1.1.7
     */
    public function upgrade() {

        global $wpdb;

        // Get main plugin instance
        $this->base = ( class_exists( 'Page_Generator_Pro' ) ? Page_Generator_Pro::get_instance() : Page_Generator::get_instance() );

        // Get current installed version number
        $installed_version = get_option( $this->base->plugin->name . '-version' ); // false | 1.1.7

        // If the version number matches the plugin version, bail
        if ( $installed_version == $this->base->plugin->version ) {
            return;
        }

        /**
         * Free to Free 1.3.8+
         * Free to Pro 1.3.8+
         * - If page-generator-pro exists as an option, and there are no groups, migrate settings of the single group
         * to a single group CPT
         */
        if ( ! $installed_version || $installed_version < '1.3.8' ) {
            $number_of_groups = Page_Generator_Pro_Groups::get_instance()->get_count();
            $free_settings = get_option( 'page-generator' );

            if ( $number_of_groups == 0 && ! empty( $free_settings ) ) {
                // Migrate settings
                $group = array(
                    'name'      => $free_settings['title'],
                    'settings'  => $free_settings,
                );

                // Generate Group Post
                $group_id = wp_insert_post( array(
                    'post_type'     => Page_Generator_Pro_PostType::get_instance()->post_type_name,
                    'post_status'   => 'publish',
                    'post_title'    => $group['name'],
                    'post_content'  => $free_settings['content'],
                ) );

                // Bail if an error occured
                if ( is_wp_error( $group_id ) ) {
                    return;
                }

                // Save group settings
                $result = Page_Generator_Pro_Groups::get_instance()->save( $group, $group_id );
                
                // If this failed, don't clear the existing settings
                if ( is_wp_error( $result ) ) {
                    return;
                }

                // Clear existing settings
                delete_option( 'page-generator' );
            }
        }

        /**
         * Pro to Pro 1.2.x+
         * - If a Groups table exists, migrate Groups to CPTs
         */
        if ( ! $installed_version || $installed_version < '1.2.3' ) {
            // If the table exists, migrate the data from it
            $table_exists = $wpdb->get_var( "SHOW TABLES LIKE '" . $wpdb->prefix . "page_generator_groups'" );
            if ( $table_exists == $wpdb->prefix . 'page_generator_groups' ) {
                // Fetch all groups
                $groups = $wpdb->get_results( "SELECT * FROM " . $wpdb->prefix . "page_generator_groups" );

                // Use a flag to tell us whether any errors occured during the groups to CPT migratio process
                $errors = false;

                // Iterate through each group, migrating to a CPT
                if ( is_array( $groups ) && count( $groups ) > 0 ) {
                    foreach ( $groups as $group ) {
                        // Unserialize the settings
                        $settings = unserialize( $group->settings );
                        
                        // Create new Post
                        $post_id = wp_insert_post( array(
                            'post_type'     => Page_Generator_Pro_PostType::get_instance()->post_type_name,
                            'post_status'   => 'publish',
                            'post_title'    => $settings['title'],
                            'post_content'  => $settings['content'],
                        ) );

                        // If an error occured, skip
                        if ( is_wp_error( $post_id ) ) {
                            $errors = true;
                            continue;
                        }

                        // Remove the settings that we no longer need to store in the Post Meta
                        unset( $settings['title'], $settings['content'] );

                        // Store the settings in the Post's meta
                        Page_Generator_Pro_Groups::get_instance()->save( $settings, $post_id );
                    }
                }

                // If no errors occured, we can safely remove the groups table
                if ( ! $errors ) {
                    $wpdb->query( "DROP TABLE " . $wpdb->prefix . "page_generator_groups" );
                }
            }
        }

        // Update the version number
        update_option( $this->base->plugin->name . '-version', $this->base->plugin->version );  

    }

    /**
     * Returns the singleton instance of the class.
     *
     * @since   1.1.3
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