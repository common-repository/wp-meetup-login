<?php

/**
 * Plugin Name: WP Meetup Login
 * Description: Enables login/registration with meetup.com account
 * Version: 0.9.1
 * Author: The Thought Engineer
 * Author URI: http://thoughtengineer.com/
 * Text Domain: meetup-login
 * Domain Path: /languages 
 * License: GPL2
 */
/*  Copyright 2017 Samer Bechara  (email : sam@thoughtengineer.com)

  This program is free software; you can redistribute it and/or modify
  it under the terms of the GNU General Public License, version 2, as
  published by the Free Software Foundation.

  This program is distributed in the hope that it will be useful,
  but WITHOUT ANY WARRANTY; without even the implied warranty of
  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
  GNU General Public License for more details.

  You should have received a copy of the GNU General Public License
  along with this program; if not, write to the Free Software
  Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
 */
use Meetup as TTE_OAUTH;

// Do not allow direct file access
defined('ABSPATH') or die("No script kiddies please!");

// Require config file
require_once (plugin_dir_path(__FILE__) . '/includes/conf/meetup.conf.php');  

class Meetup_Login {

    public function __construct() {

        // Require Settings Class
        require_once (plugin_dir_path(__FILE__) . '/includes/lib/class-tte-oauth-settings.php');

        // Require Login class
        require_once (plugin_dir_path(__FILE__) . '/includes/lib/class-tte-oauth-login.php');

        // Require Custom Mods class
        require_once (plugin_dir_path(__FILE__) . '/includes/lib/class-tte-oauth-mods.php');

        // Require OAuth2 client to process authentications
        require_once(plugin_dir_path(__FILE__) . '/includes/lib/class-tte-oauth-client.php');

        // Call init function 
        $this->init();

    }

    /*
     * Initializes our plugin
     */

    public function init() {

        // Create new objects to register actions
        new TTE_OAuth_Settings();
        new TTE_OAuth_Login();
        new TTE_OAuth_Mods();

        //add action to load language files
        add_action('plugins_loaded', array($this, 'load_translation_files'));

    }

    /*
     * this function loads our translation files
     */

    function load_translation_files() {
        load_plugin_textdomain(TTE_OAUTH\Plugin_Config::$slug, false, TTE_OAUTH\Plugin_Config::$slug . '/languages');

    }

}

// Initialize our plugin
new Meetup_Login();

