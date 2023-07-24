<?php
// Helper function to load a WooCommerce template or template part file from the
// active theme or a plugin folder.
function bb_load_wc_template_file($template_name)
{
    // Check theme folder first - e.g. wp-content/themes/bb-theme/woocommerce.
    $file = get_stylesheet_directory() . '/woocommerce/' . $template_name;
    if (@file_exists($file)) {
        //return $file;
    }
    // Now check plugin folder - e.g. wp-content/plugins/myplugin/woocommerce.
    $file = untrailingslashit(DIR_PATH . '/templates/woocommerce/' . $template_name);
    if (@file_exists($file)) {
        return $file;
    }
}
add_filter('woocommerce_template_loader_files', function ($templates, $template_name) {
    // Capture/cache the $template_name which is a file name like single-product.php
    wp_cache_set('bb_wc_main_template', $template_name); // cache the template name
    return $templates;
}, 10, 2);

add_filter('template_include', function ($template) {
    if ($template_name = wp_cache_get('bb_wc_main_template')) {
        wp_cache_delete('bb_wc_main_template'); // delete the cache
        if ($file = bb_load_wc_template_file($template_name)) {
            return $file;
        }
    }
    return $template;
}, 11);
add_filter('wc_get_template_part', function ($template, $slug, $name) {
    //return $file ? $file : $template;
    $file = bb_load_wc_template_file("{$slug}-{$name}.php");
    return $file ? $file : $template;
}, 10, 3);

add_filter('woocommerce_locate_template', function ($template, $template_name) {
    $file = bb_load_wc_template_file($template_name);
    return $file ? $file : $template;
}, 10, 2);

add_filter('wc_get_template', function ($template, $template_name) {
    $file = bb_load_wc_template_file($template_name);
    return $file ? $file : $template;
}, 10, 2);
