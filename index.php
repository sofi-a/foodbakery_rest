<?php
/*
Plugin Name: Foodbakery REST API
Plugin URI: https://github.com/sofi-a/foodbakery-rest#readme.md
Description: Adds API endpoints for Order/Inquiry, Transaction, Package Order and Withdrawal post types.
Version: 1.0
Author: Sofonias Abathun
Author URI: https://github.com/sofi-a/
Text Domain: foodbakery-rest
License: GPL3

Foodbakery REST API is free software. You can redistribute it and/or modify
it under the terms of the GNU General Public License as published
by the Free Software Foundation, either version 2 of the license, or
any later version.

Foodbakery REST API is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
GNU GENERAL PUBLIC LICENSE for more details.

You should have received a copy of the GNU Public License
along with Foodbakery REST API. If not see https://www.gnu.org/licenses/gpl.html.
*/

require_once plugin_dir_path(__FILE__) . '/vendor/autoload.php';

use FoodbakeryRestApi\Endpoints\OrdersEndpoint;
use FoodbakeryRestApi\Endpoints\PackageOrdersEndpoint;
use FoodbakeryRestApi\Endpoints\TransactionsEndpoint;
use FoodbakeryRestApi\Endpoints\WithdrawalsEndpoint;
use FoodbakeryRestApi\Utils\Roles;

add_action( 'admin_init', 'check_wp_foodbakery_activation' );

function check_wp_foodbakery_activation() {
    if (is_admin() && current_user_can('activate_plugins') && !is_plugin_active('wp-foodbakery/wp-foodbakery.php') ) {
        add_action('admin_notices', 'foodbakery_rest_plugin_notice');

        deactivate_plugins(plugin_basename(__FILE__)); 
        if (isset($_GET['activate'])) {
            unset($_GET['activate']);
        }
    }
}

function foodbakery_rest_plugin_notice(){
    ?>
    <div class="error">
        <p>Sorry, but the Foodbakery REST API plugin requires the WP Foodbakery plugin to be installed and active.</p>
    </div>
    <?php
}

register_activation_hook(__FILE__, 'foodbakery_rest_register_roles');
register_deactivation_hook(__FILE__, 'foodbakery_rest_remove_roles');

function foodbakery_rest_register_roles() {
    Roles::register_roles();
}

function foodbakery_rest_remove_roles() {
    Roles::remove_roles();
}

add_action('rest_api_init', function() {
    new OrdersEndpoint;
    new PackageOrdersEndpoint;
    new TransactionsEndpoint;
    new WithdrawalsEndpoint;
});
