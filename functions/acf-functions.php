<?php
/*
 * Allow ACF to save a copy of it's settings to a JSON file
 */
function custom_acf_save_directory($path)
{
    $path = get_stylesheet_directory() . '/acf';
    return $path;
}
add_filter('acf/settings/save_json', 'custom_acf_save_directory');
