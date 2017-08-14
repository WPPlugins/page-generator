<?php
/**
* Settings class
* 
* @package Page_Generator_Pro
* @author Tim Carr
* @version 1.0
*/
class Page_Generator_Pro_Settings {

    /**
     * Holds the class object.
     *
     * @since 1.1.3
     *
     * @var object
     */
    public static $instance;

    /**
     * Retrieves a setting from the options table.
     *
     * Safely checks if the key(s) exist before returning the default
     * or the value.
     *
     * @since 1.0
     *
     * @param string $type      Plugin / Addon Name / Type
     * @param string $key       Setting key value to retrieve
     * @param string $default   Default Value
     * @return string           Value/Default Value
     */
    public function get_setting( $type, $key, $default = '' ) {

        // Get settings
        $settings = $this->get_settings( $type );

        // Convert string to keys
        $keys = explode( '][', $key );
        
        foreach ( $keys as $count => $key ) {
            // Cleanup key
            $key = trim( $key, '[]' );

            // Check if key exists
            if ( ! isset( $settings[ $key ] ) ) {
                return $default;
            }

            // Key exists - make settings the value (which could be an array or the final value)
            // of this key
            $settings = $settings[ $key ];
        }

        // If here, setting exists
        return $settings; // This will be a non-array value

    }

    /**
    * Returns the settings for the given Type
    *
    * @since 1.0
    *
    * @param string $type       Plugin / Addon Name / Type
    * @return array             Settings
    */
    public function get_settings( $type ) {

        // Get current settings
        $settings = get_option( $type );

        // Allow devs to filter before returning
        $settings = apply_filters( 'page_generator_pro_get_settings', $settings, $type );

        // Return result
        return $settings;

    }

    /**
    * Stores the given setting for the given Plugin Type into the options table
    *
    * @since 3.0.0
    *
    * @param string $type       Plugin / Addon Name / Type
    * @param string $key        Key
    * @param string $value      Value
    * @return bool              Success
    */
    public function update_setting( $type, $key, $value ) {

        // Get all settings
        $settings = $this->get_settings( $type );

        // Update setting
        $settings[ $key ] = $value;

        // Allow devs to filter before saving
        $settings = apply_filters( 'page_generator_pro_update_setting', $settings, $type, $key, $value );
        
        // update_option will return false if no changes were made, so we can't rely on this
        update_option( $type, $settings );
        
        return true;
    }

    /**
    * Stores the given settings for the given Plugin Type into the options table
    *
    * @since 3.0.0
    *
    * @param string $type       Plugin / Addon Name / Type
    * @param array $settings    Settings
    * @return bool              Success
    */
    public function update_settings( $type, $settings ) {

        // Strip slashes from settings
        $settings = $this->stripslashes( $settings );

        // Allow devs to filter before saving
        $settings = apply_filters( 'page_generator_pro_update_settings', $settings, $type );
        
        // update_option will return false if no changes were made, so we can't rely on this
        update_option( $type, $settings );

        return true;
    }

    /**
    * Recursively strips slashes from settings
    *
    * @since 1.1
    *
    * @param array $settings Settings
    * @return array Settings
    */
    public function stripslashes( $settings ) {

        if ( is_string( $settings ) ) {
            return stripslashes( $settings );
        }
 
        if ( is_array( $settings ) ) {
            foreach ( $settings as $i => $value ) {
                $settings[ $i ] = $this->stripslashes( $value ) ;   
            }
        }

        return $settings;

    }

    /**
    * Deletes a specific setting for the given Plugin Type
    *
    * @since 1.0
    *
    * @param string $type   Plugin / Addon Name / Type
    * @param string $key    Setting Key
    * @return bool          Success
    */
    public function delete_setting( $type, $key ) {

        // Get settings
        $settings = $this->get_settings( $type );

        // If setting key exists, delete it
        if ( isset( $settings[ $key ] ) ) {
            unset( $settings[ $key ] );
        }

        // Return result of updated settings
        return $this->update_settings( $type, $settings );

    }

    /**
    * Deletes all settings for the given Plugin Type from the options table
    *
    * @since 1.0
    *
    * @param string $type   Plugin / Addon Name / Type
    * @return bool          Success
    */
    public function delete_settings( $type ) {

        // Delete all settings
        delete_option( $type );
        return true;

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