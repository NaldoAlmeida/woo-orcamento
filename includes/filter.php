<?php
add_action('init', 'cp_change_post_object');
function cp_change_post_object()
{
    $get_post_type = get_post_type_object('shop_order');
    $labels = $get_post_type->labels;
    $labels->name = 'Orçamento';
    $labels->singular_name = 'Orçamento';
    $labels->add_new = 'Adicionar Orçamento';
    $labels->add_new_item = 'Adicionar Orçamento';
    $labels->edit_item = 'Editar Orçamento';
    $labels->new_item = 'Orçamento';
    $labels->view_item = 'Ver Orçamento';
    $labels->search_items = 'Pesquisar Orçamento';
    $labels->not_found = 'Nenhum Orçamento encontrado';
    $labels->not_found_in_trash = 'Nenhum Orçamento encontrado no Lixo';
    $labels->all_items = 'Orçamentos';
    $labels->menu_name = 'Orçamento';
    $labels->name_admin_bar = 'Orçamento';
}

add_action('admin_menu', 'custom_change_admin_label');
function custom_change_admin_label()
{
    global $menu, $submenu;
    $menu['55.5'][0] = 'Orçamentos';
}


add_filter('woocommerce_get_price_html', 'mycode_remove_sale_price', 100, 2);
function mycode_remove_sale_price($price, $product)
{
    return '';
}

// Helper function to load a WooCommerce template or template part file from the
// active theme or a plugin folder.
function bb_load_wc_template_file($template_name)
{
    // Check theme folder first - e.g. wp-content/themes/bb-theme/woocommerce.
    $file = get_stylesheet_directory() . '/woocommerce/' . $template_name;
    if (@file_exists($file)) {
        return $file;
    }

    // Now check plugin folder - e.g. wp-content/plugins/myplugin/woocommerce.
    $file = untrailingslashit(plugin_dir_path(__FILE__) . '/templates/woocommerce/' . $template_name);
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


add_action('init', 'registra_status_orcamento');

function registra_status_orcamento()
{
    register_post_status(
        'wc-orcamento-pending',
        array(
            'label'                     => 'Orçamento Pendente',
            'public'                    => true,
            'show_in_admin_status_list' => true,
            'label_count'               => _n_noop('Orçamento Pendente (%s)', 'Orçamento Pendente(%s)')
        )
    );

    register_post_status('wc-orcamento_resp', array(
        'label'                     => 'Orçamento Respondido',
        'public'                    => true,
        'show_in_admin_status_list' => true,
        'label_count'               => _n_noop('Orçamento Respondido (%s)', 'Orçamento Respondido (%s)'),
    ));
}

// Add registered status to list of WC Order statuses
add_filter('wc_order_statuses', 'add_status_orcamento');
function add_status_orcamento($order_statuses)
{
    $order_statuses['wc-orcamento-pending'] = 'Orçamento Pendente';
    $order_statuses['wc-orcamento_resp'] = 'Orçamento Respondido';
    return $order_statuses;
}

add_action('admin_menu', 'register_orcamento_cw_menu', 2);
function register_orcamento_cw_menu()
{
    add_menu_page(
        __('Orçamento CW', 'orcamento-cw-text-domain'),
        'Orçamento CW',
        'manage_options',
        'orcamento-cw',
        false,
        'dashicons-star-filled',
        10
    );

    add_submenu_page(
        'orcamento-cw',
        'Orçamento CW',
        'Dashboard',
        'manage_options',
        'orcamento-cw',
        'requirePageConfig'
    );
}

///Add TAB in SETTING Woocoomerce
add_filter('woocommerce_settings_tabs_array', 'cxc_add_settings_tab', 1);
function cxc_add_settings_tab($settings_tabs)
{
    $settings_tabs['add_tab_orcamentos'] = __('Config. Orçamentos', 'config_orcamento_cw');
    return $settings_tabs;
}

add_action('woocommerce_settings_tabs_add_tab_orcamentos', 'add_tab_orcamento');
function add_tab_orcamento()
{
    woocommerce_admin_fields(cxc_get_settings());
}

function cxc_get_settings()
{

    $settings = array(
        'section_title' => array(
            'name'     => __('Orçamento', 'config_orcamento_cw'),
            'type'     => 'title',
            'desc'     => '',
            'id'       => 'wc_add_tab_orcamentos_section_title'
        ),
        'title' => array(
            'name' => __('Title', 'config_orcamento_cw'),
            'type' => 'text',
            'desc' => __('This is some helper text', 'config_orcamento_cw'),
            'id'   => 'wc_add_tab_orcamentos_title'
        ),
        'description' => array(
            'name' => __('Description', 'config_orcamento_cw'),
            'type' => 'textarea',
            'desc' => __('This is a paragraph describing the setting.', 'config_orcamento_cw'),
            'id'   => 'wc_add_tab_orcamentos_description'
        ),
        'section_end' => array(
            'type' => 'sectionend',
            'id' => 'wc_add_tab_orcamentos_section_end'
        )
    );

    return apply_filters('wc_add_tab_orcamentos_settings', $settings);
}

add_action('woocommerce_update_options_add_tab_orcamentos', 'cxc_update_settings');
function cxc_update_settings()
{
    woocommerce_update_options(cxc_get_settings());
}
