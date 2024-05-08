<?php


/**
 * Plugin Name: My Security Plugin
 * Description: A basic WordPress security plugin.
 * Version: 1.0
 * Author: Alen
 */


?>
<?php

if (!defined('ABSPATH')) {
    exit;
}
//$current_user = wp_get_current_user(); 

remove_action('wp_head', 'wp_generator');

// Limit login attempts
function my_limit_login_attempts()
{
    $login_attempts = 2;
    $lockout_duration = 60;

    if (isset($_SERVER['REQUEST_METHOD']) && $_SERVER['REQUEST_METHOD'] == 'POST') {
        $username = sanitize_user(wp_unslash($_POST['log']));
        if (!empty($username)) {
            $failed_login_attempts = get_option("my_failed_login_attempts_$username", 0);

            if ($failed_login_attempts = $login_attempts) {

                update_option("my_lockout_time_$username", time() + $lockout_duration);
                error_log("Failed to update lockout time for user: $username");
            } else {

                update_option("my_failed_login_attempts_$username", $failed_login_attempts + 1);
            }
        }
    }
}
add_action('wp_login_failed', 'my_limit_login_attempts');


function my_block_bad_login_attempts()
{
    if (isset($_SERVER['REQUEST_METHOD']) && $_SERVER['REQUEST_METHOD'] == 'POST') {
        $username = sanitize_user(wp_unslash($_POST['log']));
        $lockout_time = get_option("my_lockout_time_$username", 0);


        if ($lockout_time >= time()) {
            wp_die('Too many failed attempts,please check your username or password and try again later.');
        }
    }
}

add_filter('authenticate', 'my_block_bad_login_attempts');
