<?php
/*
 * This classes handles the plugin's options page
 * @author Samer Bechara <sam@thoughtengineer.com>
 */

use Meetup as TTE_OAUTH;

if(!class_exists('TTE_OAuth_Settings')){
    
    class TTE_OAuth_Settings {

        // This stores our plugin options
        private $options;

        /*
         * Class constructor, initializes menu and settings page
         */

        public function __construct() {

            add_action('admin_menu', array($this, 'add_admin_menu'));
            add_action('admin_init', array($this, 'init_settings'));

            $this->options = get_option(TTE_OAUTH\Plugin_Config::$prefix.'_basic_options');

        }

        /*
         * Adds an admin menu
         */

        public function add_admin_menu() {
            add_options_page('WP '. TTE_OAUTH\Plugin_Config::$plugin_name . ' Options Page', TTE_OAUTH\Plugin_Config::$plugin_name , 'manage_options', TTE_OAUTH\Plugin_Config::$prefix. '_settings', array($this, 'options_page_display'));

        }

        /*
         * Displays the options page
         */

        public function options_page_display() {
            ?>
            <form action='options.php' method='post'>

                <?php
                settings_fields(TTE_OAUTH\Plugin_Config::$prefix . '_options_page');
                do_settings_sections(TTE_OAUTH\Plugin_Config::$prefix . '_options_page');
                submit_button();
                ?>

            </form>
            <?php

        }

        /*
         * Initializes our settings
         */

        public function init_settings() {

            register_setting( TTE_OAUTH\Plugin_Config::$prefix . '_options_page', TTE_OAUTH\Plugin_Config::$prefix. '_basic_options' );

            add_settings_section(
                    TTE_OAUTH\Plugin_Config::$prefix . '_general_options_section', 
                    __( TTE_OAUTH\Plugin_Config::$plugin_name . ' Plugin Settings', TTE_OAUTH\Plugin_Config::$slug ), 
                    array($this, 'basic_options_section_callback'), 
                    TTE_OAUTH\Plugin_Config::$prefix . '_options_page'
            );

            add_settings_field( 
                    'api_key', 
                    __( 'Your '. TTE_OAUTH\Plugin_Config::$provider_name. ' API Key', TTE_OAUTH\Plugin_Config::$slug ), 
                    array($this, 'text_field_display'), 
                    TTE_OAUTH\Plugin_Config::$prefix . '_options_page', 
                    TTE_OAUTH\Plugin_Config::$prefix . '_general_options_section',
                    array('field_name' => 'api_key',
                            'field_description' => 'Retrieved from <a href="'. TTE_OAUTH\Plugin_Config::$apps_url . '" target="_blank">'. TTE_OAUTH\Plugin_Config::$provider_name. ' Developer Portal</a>. Follow the previous link, create an application and paste the key here',
                        'field_help' => 'help text goes here')
            );

            add_settings_field( 
                    'api_secret', 
                    __( 'Your '. TTE_OAUTH\Plugin_Config::$provider_name. ' API Secret', TTE_OAUTH\Plugin_Config::$slug ), 
                    array($this, 'text_field_display'), 
                    TTE_OAUTH\Plugin_Config::$prefix . '_options_page', 
                    TTE_OAUTH\Plugin_Config::$prefix . '_general_options_section' ,
                     array('field_name' => 'api_secret',
                         'field_description' => 'This is another key that can be found when you create the application following the previous link as well. Paste it here.')
            );

            add_settings_field( 
                    'redirect_url', 
                    __( 'Login Redirect URL', TTE_OAUTH\Plugin_Config::$slug ), 
                    array($this, 'text_field_display'),
                    TTE_OAUTH\Plugin_Config::$prefix . '_options_page', 
                    TTE_OAUTH\Plugin_Config::$prefix . '_general_options_section' ,
                    array('field_name' => 'redirect_url',
                        'field_description' => 'The absolute URL to redirect users to after login. If left blank or points to external host, will redirect to the dashboard page.')

            );

            add_settings_field( 
                    'registration_redirect_url', 
                    __( 'Sign-Up Redirect URL', TTE_OAUTH\Plugin_Config::$slug ), 
                    array($this, 'text_field_display'), 
                    TTE_OAUTH\Plugin_Config::$prefix . '_options_page', 
                    TTE_OAUTH\Plugin_Config::$prefix . '_general_options_section',
                    array('field_name' => 'registration_redirect_url',
                        'field_description' => 'Users are redirected to this URL when they register via their '. TTE_OAUTH\Plugin_Config::$provider_name.' account. This is useful if you want to show them a one-time welcome message after registration. If left blank or points to external host, will redirect to the dashboard page.')
            );

            add_settings_field( 
                    'cancel_redirect_url', 
                    __( 'Cancel Redirect URL', TTE_OAUTH\Plugin_Config::$slug ), 
                    array($this, 'text_field_display'),  
                    TTE_OAUTH\Plugin_Config::$prefix . '_options_page', 
                    TTE_OAUTH\Plugin_Config::$prefix . '_general_options_section',
                    array('field_name' => 'cancel_redirect_url',
                        'field_description' => 'Users are redirected to this URL when they click Cancel on the '. TTE_OAUTH\Plugin_Config::$slug. ' Authentication page. This is useful if you want to show them a different option if for some reason they do not want to login with their '. TTE_OAUTH\Plugin_Config::$slug. ' account. If left blank or points to external host, will redirect back to default WordPress login page.')
            );

            add_settings_field( 
                    'auto_profile_update', 
                    __( 'Retrieve '. TTE_OAUTH\Plugin_Config::$slug. ' profile data everytime?', TTE_OAUTH\Plugin_Config::$slug ), 
                    array($this, 'select_field_display'),  
                    TTE_OAUTH\Plugin_Config::$prefix . '_options_page', 
                    TTE_OAUTH\Plugin_Config::$prefix . '_general_options_section' ,
                    array('field_name' => 'auto_profile_update',
                        'field_description' => 'This option allows you to pull in the users data the first time, upon registration but not overwrite all of their information every time they login with the '. TTE_OAUTH\Plugin_Config::$provider_name.' button. This is useful if users spend time creating a custom profile and then they later use the login with '. TTE_OAUTH\Plugin_Config::$provider_name.' button. Disable this if you do not want their information to be overwritten')                
            );

            add_settings_field( 
                    'override_profile_photo', 
                    __( "Override the user's profile picture?", TTE_OAUTH\Plugin_Config::$slug ), 
                    array($this, 'select_field_display'),  
                    TTE_OAUTH\Plugin_Config::$prefix . '_options_page', 
                    TTE_OAUTH\Plugin_Config::$prefix . '_general_options_section' ,
                    array('field_name' => 'override_profile_photo',
                        'field_description' => 'When enabled, this option fetches the user\'s profile picture from '. TTE_OAUTH\Plugin_Config::$slug. ' and overrides the default gravatar.com user profile picture used by WordPress. If the plugin is setup to retrive new profile data on every login, the profile picture will be retrieved as well.')                

            );

            add_settings_field( 
                    'logged_in_message', 
                    __( 'Logged In Message', TTE_OAUTH\Plugin_Config::$slug ), 
                    array($this, 'text_area_display'), 
                    TTE_OAUTH\Plugin_Config::$prefix . '_options_page', 
                    TTE_OAUTH\Plugin_Config::$prefix . '_general_options_section',
                    array('field_name' => 'logged_in_message',
                        'field_description' => 'Enter a message you would like to show for logged in users in place of the login button. If left blank, the button is hidden and no message is shown.')
            );

        }

        /*
         * Displays a text field setting, called back by the add_settings_field function
         * @param   array   $field_options  Passed by the add_settings_field callback function
         */

        public function text_field_display($field_options) {

            // Get the text field name
            $field_name = $field_options['field_name'];
            ?>
            <input type='text' name='<?php echo TTE_OAUTH\Plugin_Config::$prefix;?>_basic_options[<?php echo $field_name; ?>]' value='<?php echo $this->get_field_value($field_name) ?>'>
            <p class="description"><?php echo isset($field_options['field_description'])?$field_options['field_description']:''; ?></p>
            <?php

        }

        /*
         * Displays a text area setting, called back by the add_settings_field function
         * @param   array   $field_options  Passed by the add_settings_field callback function
         */

        public function text_area_display($field_options) {

            $field_name = $field_options['field_name'];
            ?>
            <textarea cols='40' rows='5' name='<?php echo TTE_OAUTH\Plugin_Config::$prefix;?>_basic_options[<?php echo $field_name; ?>]'><?php echo $this->get_field_value($field_name) ?></textarea>
            <p class="description"><?php echo isset($field_options['field_description'])?$field_options['field_description']:''; ?></p>
            <?php

        }

        /*
         * Returns the field's value
         */

        private function get_field_value($field_name) {

            return isset($this->options[$field_name]) ? $this->options[$field_name] : '';

        }

        /*
         * Displays a select field
         */
        function select_field_display($field_options) {

            $field_name = $field_options['field_name'];
            $field_value = $this->get_field_value($field_name);
        ?>
        <select name='<?php echo TTE_OAUTH\Plugin_Config::$prefix;?>_basic_options[<?php echo $field_name;?>]'>
            <option value='yes' <?php selected($field_value, 'yes'); ?>>Yes</option>
            <option value='no' <?php selected($field_value, 'no'); ?>>No</option>
        </select>
        <p class="description"><?php echo isset($field_options['field_description']) ? $field_options['field_description'] : ''; ?></p>
        <?php

    }
    /*
     * Rendered at the start of the options section
     */
    function basic_options_section_callback() {

            echo __('For installation instructions, please visit <a href="http://thoughtengineer.com/wordpress-'.TTE_OAUTH\Plugin_Config::$slug. '-plugin/" target="_blank">Installation Instructions Page</a>', TTE_OAUTH\Plugin_Config::$slug);

        }

    }

}