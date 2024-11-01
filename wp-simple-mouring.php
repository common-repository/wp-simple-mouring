<?php

/*
  Plugin Name: WP Simple Mouring
  Version: 1.0
  Description: Simple implementation Mouring in your page
  Author: Montownia-Stron
  Author URI: http://montownia-stron.pl
  License: GPLv2
  Text Domain: wp-simple-mouring
  Domain Path: languages
 */

/**
 * Load files with languages
 */
load_plugin_textdomain('wp-simple-mouring', false, 'wp-simple-mouring/languages');

$plugin_dir_url = plugin_dir_url(__FILE__);
$plugin_dir_path = plugin_dir_path(__FILE__);

if (function_exists('wp_normalize_path')) {
    $plugin_dir_url = wp_normalize_path($plugin_dir_url);
    $plugin_dir_path = wp_normalize_path($plugin_dir_path);
}

define('WPSM_URL', $plugin_dir_url);
define('WPSM_PATH', $plugin_dir_path);

/**
 * The connection to the database for the plugin
 *
 * @global $wpdb
 *
 * install plugin
 */
function simple_mouring_install() {

    global $wpdb;
    $table_simple_mouring = $wpdb->prefix . "simple_mouring";

    $simple_mouring_db_version = "1.0";

    if ($wpdb->get_var("SHOW TABLES LIKE '" . $table_simple_mouring . "'") != $table_simple_mouring) {

        $query1 = "CREATE TABLE " . $table_simple_mouring . " (
		id int(11) NOT NULL,
		name VARCHAR(300) CHARACTER SET utf8 NOT NULL,
		grayscale VARCHAR(300) CHARACTER SET utf8 NOT NULL,
                active boolean NOT NULL
		) ENGINE=InnoDB DEFAULT CHARSET=utf8;";


        $query2 = "ALTER TABLE " . $table_simple_mouring . "
		ADD PRIMARY KEY (id);";
        $query3 = "ALTER TABLE " . $table_simple_mouring .
                " MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;";
        $query4 = "INSERT INTO " . $table_simple_mouring . "(name, grayscale, active) values ('settings css', '100', '0');";

        $wpdb->query($query1);
        $wpdb->query($query2);
        $wpdb->query($query3);
        $wpdb->query($query4);

        add_option("simple_mouring_db_version", $simple_mouring_db_version);
        add_option('simple_mouring_speed', '2000');
        add_option('simple_mouring_interval', '2000');
        add_option('simple_mouring_type', 'vertical');
    }
}

register_activation_hook(__FILE__, 'simple_mouring_install');

/**
 * uninstall plugin
 */
function simple_mouring_uninstall() {

    global $wpdb;
    $table_simple_mouring = $wpdb->prefix . "simple_mouring";

    $query1 = 'DROP TABLE ' . $table_simple_mouring;

    $wpdb->query($query1);
}

register_deactivation_hook(__FILE__, 'simple_mouring_uninstall');

/**
 * Adds plugin menu in the Admin Panel
 */
function simple_mouring_menu() {

    //variable translations
    $name_menu_page = __('Simple Mouring', 'wp-simple-mouring');

    add_menu_page($name_menu_page, $name_menu_page, 'administrator', 'wp-simple-mouring-main', 'simple_mouring_manage', 'dashicons-admin-generic');
    add_submenu_page('wp-simple-mouring-main', 'Settings', 'Settings', 'administrator', 'wp-simple-mouring-main', 'simple_mouring_manage');
}

add_action('admin_menu', 'simple_mouring_menu');

function simple_mouring_manage() {
    $current_user = wp_get_current_user();
    $allowed_roles = array('administrator');
    if (array_intersect($allowed_roles, $current_user->roles)) {
        global $wpdb;
        $table_simple_mouring = $wpdb->prefix . "simple_mouring";

        if (!empty($_POST) && check_admin_referer('simple_mouring_settings', 'simple_mouring_save')) {
            $active = (isset($_POST['active'])) ? 1 : 0;
            $grayscale = (isset($_POST['grayscale']) && intval($_POST['grayscale'])) ? $_POST['grayscale'] : '0';

            $wpdb->update($table_simple_mouring, array('active' => $active, 'grayscale' => $grayscale), array('id' => 1));
        }
        $settings = get_settings_simple_mouring();
        simpleMouringView($settings);
    }
}

function simpleMouringView($settings) {
    $checked = ($settings["active"] == 1 ? " checked" : "");
    $output .= '<div class="wrap">';
    $output .= '<h1>Simple Mouring Settings</h1>';
    $output .= '<form action="" method="POST">';
    $output .= wp_nonce_field('simple_mouring_settings', 'simple_mouring_save');
    $output .= '<table class="form-table">';
    $output .= '<tbody>';
    $output .= '<tr>';
    $output .= '<th scope="row">';
    $output .= 'Activate';
    $output .= '</th>';
    $output .= '<td>';
    $output .= '<input type="checkbox" id="active" name="active" value="1"' . $checked . ' /> <label for="active">Activate plugin</label>';
    $output .= '<td>';
    $output .= '</tr>';
    $output .= '<tr>';
    $output .= '<th scope="row">';
    $output .= 'Percent of grayscale';
    $output .= '</th>';
    $output .= '<td>';
    $output .= '<input type="number" name="grayscale" value="' . $settings['grayscale'] . '">';
    $output .= '<p class="description" id="tagline-description">Grayscale in percentage</p>';
    $output .= '<td>';
    $output .= '</tr>';
    $output .= '</tbody>';
    $output .= '</table>';
    $output .= '<button type="submit" name="save" value="1" class="button button-primary">Zapisz</button>';
    $output .= '</form>';
    $output .= '</div>';

    echo $output;
}

function get_settings_simple_mouring() {
    global $wpdb;
    $table_simple_mouring = $wpdb->prefix . "simple_mouring";
    $query = "SELECT * FROM " . $table_simple_mouring;
    $data = $wpdb->get_row($query, ARRAY_A);
    return $data;
}

function simple_mouring_css() {
    $settings = get_settings_simple_mouring();
    $siteurl = get_option('siteurl');
    $url = WPSM_PATH . 'css/wp-simple-mouring-style.php';

    if ($settings['active'] && !is_admin()) {
        require_once $url;
    }
}

add_action('init', 'simple_mouring_css');
?>
