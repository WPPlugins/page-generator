<?php
/**
* Generate class
* 
* @package Page_Generator_Pro
* @author Tim Carr
* @version 1.0
*/
class Page_Generator_Pro_Generate {

    /**
     * Holds the class object.
     *
     * @since 1.1.3
     *
     * @var object
     */
    public static $instance;

    /**
     * Holds the array of found keywords across all settings.
     *
     * @since 1.2.0
     *
     * @var array
     */
    public $required_keywords = array();

    /**
     * Holds the array of keywords to replace e.g. {city}
     *
     * @since   1.3.1
     *
     * @var     array
     */
    public $searches = array();

    /**
     * Holds the array of keyword values to replace e.g. Birmingham
     *
     * @since   1.3.1
     *
     * @var     array
     */
    public $replacements = array();

    /**
     * Calculates the maximum number of pages that will be generated based
     * on the settings and keywords
     *
     * @since 1.1.5
     *
     * @param   int     $group_id   Group ID
     * @return  mixed               WP_Error | integer
     */
    public function get_max_number_of_pages( $group_id ) {

        // Get instances
        $common_instance = Page_Generator_Pro_Common::get_instance();
        $keywords_instance = Page_Generator_Pro_Keywords::get_instance();
        $groups_instance = Page_Generator_Pro_Groups::get_instance();
        
        // Get group
        $settings = $groups_instance->get_settings( $group_id );
        if ( ! $settings ) {
            return new WP_Error( 'group_error', sprintf( __( 'Group ID %s could not be found.', 'page-generator-pro' ), $group_id ) );
        }

        // Get an array of required keywords that need replacing with data
        $required_keywords = $this->find_keywords_in_settings( $settings );

        if ( count( $required_keywords ) == 0 ) {
            return 0;
        }

        // Get the terms for each required keyword
        $keywords = array();
        foreach ( $required_keywords as $key => $keyword ) {
            // Get terms for this keyword
            $terms = $keywords_instance->get_by( 'keyword', $keyword );
            
            if ( ! is_array( $terms ) ) {
                // Remove this keyword
                unset( $required_keywords[ $key ] );
                continue;
            }

            $keywords[ $keyword ] = $terms['dataArr'];
        }

        // Depending on the generation method chosen, for each keyword, define the term
        // that will replace it.
        switch ( $settings['method'] ) {

            /**
            * All
            * - Generates all possible term combinations across keywords
            */
            case 'all':
                // Generate all possible term combinations, and return the count
                $combinations = $this->generate_all_array_combinations( $keywords );
                return count( $combinations );
                break;

            /**
            * Sequential
            * - Generates term combinations across keywords matched by index
            */
            case 'sequential':
                $total = 0;
                foreach ( $keywords as $keyword => $terms ) {
                    if ( count( $terms ) > 0 && ( count( $terms ) < $total || $total == 0 ) ) {
                        $total = count( $terms );
                    }
                }

                return $total;
                break;

            /**
            * Random
            * - Gets a random term for each keyword
            */
            case 'random':
                return 0;
                break;

        }

    }
    
    /**
     * Main function to generate a Page, Post or Custom Post Type
     *
     * @since   1.0
     *
     * @param   int     $group_id   Group ID
     * @param   int     $index      Keyword Index
     * @param   bool    $test_mode  Test Mode
     * @return  mixed               WP_Error | URL
     */
    public function generate( $group_id, $index = 0, $test_mode = false ) {

        // Get instances
        $common_instance    = Page_Generator_Pro_Common::get_instance();
        $keywords_instance  = Page_Generator_Pro_Keywords::get_instance();
        $groups_instance    = Page_Generator_Pro_Groups::get_instance();

        // Get group settings
        $settings = $groups_instance->get_settings( $group_id );
        if ( ! $settings ) {
            return new WP_Error( 'group_error', sprintf( __( 'Group ID %s could not be found.', 'page-generator-pro' ), $group_id ) );
        }

        // Get an array of required keywords that need replacing with data
        $required_keywords = $this->find_keywords_in_settings( $settings );
        if ( count( $required_keywords ) == 0 ) {
            return new WP_Error( 'keyword_error', __( 'No keywords were specified in the title, content or excerpt.', 'page-generator-pro' ) );
            die();
        }

        // Get the terms for each required keyword
        $keywords = array();
        foreach ( $required_keywords as $keyword ) {
            // Get terms for this keyword
            $terms = $keywords_instance->get_by( 'keyword', $keyword );
            
            if ( ! is_array( $terms ) || empty( $terms ) ) {
                $terms = array();
            }

            if ( isset( $terms['dataArr'] ) ) {
                $keywords[ $keyword ] = $terms['dataArr'];
            }
        }

        // Depending on the generation method chosen, for each keyword, define the term
        // that will replace it.
        switch ( $settings['method'] ) {

            /**
            * All
            * - Generates all possible term combinations across keywords
            */
            case 'all':
                // Generate all possible term combinations
                $combinations = $this->generate_all_array_combinations( $keywords );
               
                // If the current index exceeds the total number of combinations, we've exhausted all
                // options and don't want to generate any more Pages (otherwise we end up with duplicates)
                if ( $index > ( count( $combinations ) - 1 ) ) {
                    return new WP_Error( 'keywords_exhausted', __( 'All possible keyword term combinations have been generated. Generating more Pages/Posts would result in duplicate content.', 'page-generator-pro' ) );
                    die();
                }

                // Define the keyword => term key/value pairs to use based on the current index
                $keywords_terms = $combinations[ $index ];
                break;

            /**
            * Sequential
            * - Generates term combinations across keywords matched by index
            */
            case 'sequential':
                $keywords_terms = array();
                foreach ( $keywords as $keyword => $terms ) {
                    // Use modulo to get the term index for this keyword
                    $term_index = ( $index % count( $terms ) );   

                    // Build the keyword => term key/value pairs
                    $keywords_terms[ $keyword ] = $terms[ $term_index ];
                }
                break;

            /**
            * Random
            * - Gets a random term for each keyword
            */
            case 'random':
                $keywords_terms = array();
                foreach ( $keywords as $keyword => $terms ) {
                    $term_index = rand( 0, ( count( $terms ) - 1 ) );  

                    // Build the keyword => term key/value pairs
                    $keywords_terms[ $keyword ] = $terms[ $term_index ];
                }
                break;

        }

        // Rotate Author
        if ( isset( $settings['rotateAuthors'] ) ) {
            $authors = $common_instance->get_authors();
            $userIndex = ( $index % count( $authors ) );
        }

        // Iterate through each keyword and term key/value pair
        foreach ( $keywords_terms as $keyword => $term ) {

            // Define the search and replace queries
            // We have multiple queries as we're looking for:
            // - keyword: {keyword}
            // - keyword with transformation {keyword:uppercase_all}
            $term = trim( html_entity_decode( $term ) );
            $this->searches = array(
                '{' . $keyword . '}',                                   // Keyword Term
            );
            $this->replacements = array(
                $term,
            );
                
            // Go through each of the group's settings, replacing $search with $replacement 
            foreach ( $settings as $key => $value ) {
                // Depending on the setting key, process the search and replace
                switch ( $key ) {
                    /**
                     * Taxonomies
                     */
                    case 'tax':
                        // If the submitted taxonomies are an array, iterate through each one
                        // This allows hierarchical taxonomies to have keyword replacements carried out on nested arrays e.g.
                        // $settings[tax][category][0]
                        if ( is_array( $settings[ $key ] ) ) {
                            foreach ( $settings[ $key ] as $taxonomy => $terms ) {
                                // Hierarchical based taxonomy - first key may contain new tax terms w/ keywords that need replacing now
                                if ( is_array( $terms ) && isset( $terms[0] ) ) {
                                    $settings[ $key ][ $taxonomy ][0] = str_ireplace( $this->searches, $this->replacements, $settings[ $key ][ $taxonomy ][0] );
                                }

                                // Tag based taxonomy
                                if ( ! is_array( $terms ) ) {
                                    $settings[ $key ][ $taxonomy ] = str_ireplace( $this->searches, $this->replacements, $settings[ $key ][ $taxonomy ] );   
                                }
                            }
                        }
                        break;

                    /**
                     * Default
                     * - Will also cover keyword search / replace for Page Builders data
                     */
                    default:
                        // Don't do anything if there's no data
                        if ( empty( $settings[ $key ] ) ) {
                            break;
                        }

                        // If the settings key's value is an array, walk through it recursively to search/replace
                        // Otherwise do a standard search/replace on the string
                        if ( is_array( $settings[ $key ] ) ) {
                            // Array
                            array_walk_recursive( $settings[ $key ], array( $this, 'replace_keywords_in_array' ) );
                        } elseif( is_object( $settings[ $key ] ) ) {
                            // Object
                            array_walk_recursive( $settings[ $key ], array( $this, 'replace_keywords_in_array' ) );
                        } else {
                            // String
                            // Keyword search/replace
                            $settings[ $key ] = str_ireplace( $this->searches, $this->replacements, $settings[ $key ] );   
                        }
                        break;

                }
            }
        }

        // Spin content
        $content = $settings['content'];

        // Remove all shortcode processors, so we don't process any shortcodes. This ensures page builders, galleries etc
        // will work as their shortcodes will be processed when the generated page is viewed.
        remove_all_shortcodes();

        // Execute shortcodes in content, so actual HTML is output instead of shortcodes for this plugin's shortcodes
        $content = do_shortcode( $content );

        // Build Post args
        $post_args = array(
            'post_type'     => $settings['type'],
            'post_title'    => $settings['title'],
            'post_content'  => $content,
            'post_status'   => ( $test_mode ? 'draft' : $settings['status'] ),
            'post_author'   => ( ( isset( $settings['rotateAuthors'] ) && $settings['rotateAuthors'] == 1 ) ? $authors[ $userIndex ]->ID : $settings['author'] ), // ID
            'comment_status'=> ( ( isset( $settings['comments'] ) && $settings['comments'] == 1 ) ? 'open' : 'closed' ),
            'ping_status'   => ( ( isset( $settings['trackbacks'] ) && $settings['trackbacks'] == 1 ) ? 'open' : 'closed' ),
        );

        // Only set post name if not empty
        if ( ! empty( $settings['permalink'] ) ) {
            $post_args['post_name'] = str_replace( ' ', '-', strtolower( $settings['permalink'] ) );
        }

        // Define the Post Date
        switch ( $settings['date_option'] ) {

            /**
            * Now
            */
            case 'now':
                if ( $settings['status'] == 'future' ) {
                    // Increment the current date by the schedule hours and unit
                    $post_args['post_date'] = date( 'Y-m-d H:i:s', strtotime( '+' . ( $settings['schedule'] * ( $index + 1 ) ) . ' ' . $settings['scheduleUnit'] ) );
                } else {
                    $post_args['post_date'] = date( 'Y-m-d H:i:s' );
                }
                break;

        }
        
        // Allow filtering
        $post_args = apply_filters( 'page_generator_pro_generate_post_args', $post_args, $settings );

        // Create Page, Post or CPT
        $post_id = wp_insert_post( $post_args, true );
        
        // Check Post creation worked
        if ( is_wp_error( $post_id ) ) {
            $post_id->add_data( $post_args, $post_id->get_error_code() );
            return $post_id;
        }

        // Store this Generation ID in the Post's meta, so we can edit/delete the generated Post(s) in the future
        update_post_meta( $post_id, '_page_generator_pro_group', $group_id );

        // Page Template
        if ( $settings['type'] == 'page' ) {
            update_post_meta( $post_id, '_wp_page_template', $settings['pageTemplate'] );
        }

        // Get URL of Page/Post/CPT just generated
        $url = get_bloginfo( 'url' ) . '?page_id=' . $post_id . '&preview=true';

        // Request that the user review the plugin. Notification displayed later,
        // can be called multiple times and won't re-display the notification if dismissed.
        if ( ! $test_mode ) {    
            Page_Generator::get_instance()->dashboard->request_review();
        }

        return $url;

    }

    /**
     * Generate all the possible combinations among a set of nested arrays.
     *
     * @since 1.1.5
     *
     * @param array $data  The entrypoint array container.
     * @param array $all   The final container (used internally).
     * @param array $group The sub container (used internally).
     * @param mixed $val   The value to append (used internally).
     * @param int   $i     The key index (used internally).
     */
    private function generate_all_array_combinations( array $data, array &$all = array(), array $group = array(), $value = null, $i = 0, $key = null ) {
        
        $keys = array_keys( $data );

        if ( isset( $value ) === true ) {
            $group[ $key ] = $value;
        }
        if ( $i >= count( $data ) ) {
            array_push( $all, $group );
        } else {
            $current_key = $keys[ $i ];
            $current_element = $data[ $current_key ];
            if ( count( $data[ $current_key ] ) <= 0 ) {
                $this->generate_all_array_combinations( $data, $all, $group, null, $i + 1, $current_key );
            } else {
                foreach ( $current_element as $val ) {
                    $this->generate_all_array_combinations( $data, $all, $group, $val, $i + 1, $current_key );
                }
            }
        }

        return $all;

    }

    /**
     * Recursively goes through the settings array, finding any {keywords}
     * specified, to build up an array of keywords we need to fetch.
     *
     * @since   1.0.0
     *
     * @param   array   $settings   Settings
     * @return  array               Found Keywords
     */
    public function find_keywords_in_settings( $settings ) {

        // Get all keywords
        $keywords = Page_Generator_Pro_Keywords::get_instance()->get_all( 'keyword', 'ASC', -1 );

        // Recursively walk through all settings to find all keywords
        array_walk_recursive( $settings, array( $this, 'find_keywords_in_string' ) );

        // Return the required keywords object
        return $this->required_keywords;

    }

    /**
     * Performs a search on the given string to find any {keywords}
     *
     * @since 1.2.0
     *
     * @param   string  $content    Array Value (string to search)
     * @param   string  $key        Array Key
     */
    private function find_keywords_in_string( $content, $key ) {

        // Define array to store found keywords in
        $required_keywords = array();

        // If $content is an object, iterate this call
        if ( is_object( $content ) ) {
            return array_walk_recursive( $content, array( $this, 'find_keywords_in_string' ) );
        }

        // Get keywords and spins
        preg_match_all( "|{(.+?)}|", $content, $matches );
        
        // Continue if no matches found
        if ( ! is_array( $matches ) ) {
            return;
        }
        if ( count( $matches[1] ) == 0 ) {
            return;
        }

        // Iterate through matches
        foreach ( $matches[1] as $m_key => $keyword ) {
            // Ignore spins
            if ( strpos( $keyword, "|" ) !== false ) {
                continue;
            }

            // If there's a transformation flag applied to the keyword, remove it
            if ( strpos( $keyword, ':' ) !== false ) {
                list( $keyword, $transformation ) = explode( ':', $keyword );
            }

            // Lowercase keyword, to avoid duplicates e.g. {City} and {city}
            $keyword = strtolower( $keyword );

            // If this keyword is not in our required_keywords array, add it
            if ( ! in_array( $keyword, $required_keywords ) ) {
                $required_keywords[ $keyword ] = $keyword;
            }
        }

        // Add the found keywords to the class array
        $this->required_keywords = array_merge( $this->required_keywords, $required_keywords );

    }

    /**
     * array_walk_recursive callback, which finds $this->searches, replacing with
     * $this->replacements in $item
     *
     * @since   1.3.1
     *
     * @param   mixed   $item   Item (array, object, string)
     * @param   string  $key    Key
     */
    private function replace_keywords_in_array( &$item, $key ) {

        // If $item is an object, iterate this call
        if ( is_object( $item ) ) {
            array_walk_recursive( $item, array( $this, 'replace_keywords_in_array' ) );
        } else {
            $item = str_ireplace( $this->searches, $this->replacements, $item );
        }

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