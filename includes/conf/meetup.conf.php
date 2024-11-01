<?php

/* 
 * Copyright (C) 2017 Samer Bechara <sam@thoughtengineer.com>
 *
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License
 * as published by the Free Software Foundation; either version 2
 * of the License, or (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 59 Temple Place - Suite 330, Boston, MA  02111-1307, USA.
 */

namespace Meetup;

class Plugin_Config {
 
    // Define the plugin slug
    public static $slug = 'meetup-login';
    
    // Define the provider name
    public static $provider_name = 'Meetup';
    
    // Define plugin prefix that will be used in DB to help prevent conflicts with similar plugins
    public static $prefix = 'tte_mup';
    
    // Define plugin name
    public static $plugin_name = 'Meetup Login';
    
    // Define plugin URL
    public static $plugin_url;
    
    // Define authorize URL
    public static $authorize_url = 'https://secure.meetup.com/oauth2/authorize';
    
    // Define token URL
    public static $token_url = 'https://secure.meetup.com/oauth2/access';
    
    // Define API Base URL
    public static $base_url = 'https://api.meetup.com/2';
    
    // Define the URL to create OAuth application for this provider
    public static $apps_url = 'https://secure.meetup.com/meetup_api/oauth_consumers/';
    
    // Define our preferred HTTP authentication method
    public static $http_auth_method = 'POST';
    
    // Define the access token name per the API
    public static $access_token_name = 'access_token';
}

// Define Plugin URL
Plugin_Config::$plugin_url = plugins_url().'/'.Plugin_Config::$slug.'/';