<?php
/*
 * Allow ACF to save a copy of it's settings to a JSON file
 */
function custom_acf_save_directory($path)
{
    return get_stylesheet_directory() . '/acf';
}
add_filter('acf/settings/save_json', 'custom_acf_save_directory');
