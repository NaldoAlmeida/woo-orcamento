<?php
/*
Plugin Name: Woo - Orçamento
Plugin URI: http://www.cicloneweb.com.br/
Description: Transforme seu site em um catalogo online. Sistema para captação de orçamentos.
Version: 2.0.0
Author: Ciclone Web
License: GPL version 2 or later - http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
*/


defined('ABSPATH') or die('No script kiddies please!');

define('DIR_PATH', plugin_dir_path(__FILE__));
define('PLUGIN_CW', '2.0.0');


if (!function_exists('printR')) {
    function printR($array)
    {
        echo '<pre>';
        print_r($array);
        echo '</pre>';
    }
}


function is_active_woocommerce()
{
    $active_plugins = (array) get_option('active_plugins', array());

    if (is_multisite()) {
        $active_plugins = array_merge($active_plugins, get_site_option('active_sitewide_plugins', array()));
    }

    return in_array('woocommerce/woocommerce.php', $active_plugins) || array_key_exists('woocommerce/woocommerce.php', $active_plugins) || class_exists('WooCommerce');
}

if (!is_active_woocommerce()) {
    function general_admin_notice()
    {
        echo '<div class="notice notice-warning is-dismissible">
             <p><strong>Woo Orçamento CW:</strong> instale/ative o plugin "Woocommerce"</p>
         </div>';
    }
    add_action('admin_notices', 'general_admin_notice');

    return;
}


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

function getConfigOrcamento($indice = '')
{
    $option = json_decode(get_option('config_orcamento'));
    if ($indice == '') {
        return $option;
    } else {
        return (isset($option->$indice)) ? $option->$indice : false;
    }
}

if (getConfigOrcamento('ocultar_preco')) {
    add_filter('woocommerce_get_price_html', 'remove_sale_price', 100, 2);
    add_filter('woocommerce_cart_item_price', 'remove_sale_price', 100, 2);
    add_filter('woocommerce_cart_item_subtotal', 'remove_sale_price', 100, 2);
    function remove_sale_price()
    {
        return '';
    }
}


include(plugin_dir_path(__FILE__) . 'includes/template_plugin.php');


add_action('wp_enqueue_scripts', 'assets_orcamento_cw_front');
function assets_orcamento_cw_front()
{
    /* CSS*/
    wp_enqueue_style('style-cw', plugins_url('/css/style-cw.css', __FILE__), array(), PLUGIN_CW);
    wp_enqueue_style('sweetalert2', plugins_url('/css/sweetalert2.min.css', __FILE__));

    /* JS*/
    wp_enqueue_script('ajax_operation_script', plugins_url('/js/app.js', __FILE__), array('jquery'), PLUGIN_CW, true);
    wp_localize_script('ajax_operation_script', 'urlAjax', [admin_url('admin-ajax.php')]);
    wp_enqueue_script('ajax_operation_script');
    wp_enqueue_script('sweetalert2', plugins_url('/js/sweetalert2.all.min.js', __FILE__));
    wp_enqueue_script('serializeJSON', plugins_url('/js/serializeJSON.js', __FILE__));
    wp_enqueue_script('maskedinput', plugins_url('/js/maskedinput.min.js', __FILE__), array('jquery'));
}

function assets_orcamento_cw_admin($hook)
{
    //wp_enqueue_style('style_admin', plugins_url('/css/grid.css', __FILE__));
    wp_enqueue_style('sweetalert2', plugins_url('/css/sweetalert2.min.css', __FILE__));

    /** JS */
    wp_enqueue_script('sweetalert2', plugins_url('/js/sweetalert2.all.min.js', __FILE__));
    wp_enqueue_script('maskedinput', plugins_url('/js/maskedinput.min.js', __FILE__), array('jquery'));
    wp_enqueue_script('serializeJSON', plugins_url('/js/serializeJSON.js', __FILE__));
    wp_enqueue_script('ajax_operation_script', plugins_url('/js/admin.js', __FILE__), array('jquery'), PLUGIN_CW, true);
    wp_localize_script('ajax_operation_script', 'urlAjax', [admin_url('admin-ajax.php')]);
}
add_action('admin_enqueue_scripts', 'assets_orcamento_cw_admin');


function action_woocommerce_thankyou($order_id)
{
    // Get $order object
    $order = wc_get_order($order_id);

    // Get the user email from the order
    $order_email = $order->get_billing_email();
    // Check if there are any users with the billing email as user or email
    $email = email_exists($order_email);

    // Determines whether the current visitor is a logged in user.
    if (is_user_logged_in()) {
        $user  = wp_get_current_user();
        $user_id = $user->ID;
    } else {

        if ($user == false && $email == false) {
            $user = username_exists($order_email);

            // Random password with 12 chars
            $random_password = wp_generate_password();

            // Firstname
            $first_name = $order->get_billing_first_name();

            // Lastname
            $last_name = $order->get_billing_last_name();

            // Create new user with email as username, newly created password and userrole          
            $user_id = wp_insert_user(
                array(
                    'user_email' => $order_email,
                    'user_login' => $order_email,
                    'user_pass'  => $random_password,
                    'first_name' => $first_name,
                    'last_name'  => $last_name,
                    'role'       => 'customer',
                )
            );

            // Get all WooCommerce emails Objects from WC_Emails Object instance
            $emails = WC()->mailer()->get_emails();
            // Send WooCommerce "Customer New Account" email notification with the password
            $emails['WC_Email_Customer_New_Account']->trigger($user_id, $random_password, true);
        }
    }

    // If the UID is null, then it's a guest checkout (new user)


    // (Optional) WC guest customer identification
    //update_user_meta( $user_id, 'guest', 'yes' );
    // User's billing data
    update_user_meta($user_id, 'billing_address_1', $order->get_billing_address_1());
    update_user_meta($user_id, 'billing_address_2', $order->get_billing_address_2());
    update_user_meta($user_id, 'billing_city', $order->get_billing_city());
    update_user_meta($user_id, 'billing_company', $order->get_billing_company());
    update_user_meta($user_id, 'billing_country', $order->get_billing_country());
    update_user_meta($user_id, 'billing_email', $order_email);
    update_user_meta($user_id, 'billing_first_name', $order->get_billing_first_name());
    update_user_meta($user_id, 'billing_last_name',  $order->get_billing_last_name());
    update_user_meta($user_id, 'billing_phone', $order->get_billing_phone());
    update_user_meta($user_id, 'billing_postcode', $order->get_billing_postcode());
    update_user_meta($user_id, 'billing_state', $order->get_billing_state());
    update_user_meta($user_id, 'billing_whatsapp', preg_replace('/[^0-9]/', '', $_POST['billing_whatsapp']));
    update_user_meta($user_id, 'billing_cpf_cnpj', preg_replace('/[^0-9]/', '', $_POST['billing_cpf_cnpj']));

    // User's shipping data
    update_user_meta($user_id, 'shipping_address_1', $order->get_shipping_address_1());
    update_user_meta($user_id, 'shipping_address_2', $order->get_shipping_address_2());
    update_user_meta($user_id, 'shipping_city', $order->get_shipping_city());
    update_user_meta($user_id, 'shipping_company', $order->get_shipping_company());
    update_user_meta($user_id, 'shipping_country', $order->get_shipping_country());
    update_user_meta($user_id, 'shipping_first_name', $order->get_shipping_first_name());
    update_user_meta($user_id, 'shipping_last_name', $order->get_shipping_last_name());
    update_user_meta($user_id, 'shipping_method', $order->get_shipping_method());
    update_user_meta($user_id, 'shipping_postcode', $order->get_shipping_postcode());
    update_user_meta($user_id, 'shipping_state', $order->get_shipping_state());
    // Link past orders to this newly created customer
    wc_update_new_customer_past_orders($user_id);

    // Auto login
    wp_set_current_user($user_id);
    wp_set_auth_cookie($user_id);
}

add_action('wp_ajax_configuracoes', 'configuracoes_callback');
function configuracoes_callback()
{
    $retorno = [];
    if (wp_verify_nonce($_POST['finaliza_orcamento'], "finaliza_orcamento")) {
        update_option('config_orcamento', json_encode([
            'whatsapp'      => $_POST['whatsapp'],
            'ocultar_preco' => $_POST['ocultar_preco'],
        ]));

        $retorno = ['success' => 1];
    } else {
        $retorno = ['error' => 'verify_nonce'];
    }

    echo json_encode($retorno);
    die();
}


add_action('wp_ajax_submitOrcamento', 'submitOrcamento_callback');
add_action('wp_ajax_nopriv_submitOrcamento', 'submitOrcamento_callback');
function submitOrcamento_callback()
{
    if (wp_verify_nonce($_POST['finaliza_orcamento'], "finaliza_orcamento")) {
        $order = wc_create_order();
        $car = WC()->cart->get_cart();
        if (count($car)) {
            foreach ($car as $key => $val) {
                $args = ($val['variation_id']) ? array(
                    'variation' => $val['variation'],
                    'variation_id' => $val['variation_id']
                ) : [];

                $id_product = ($val['variation_id']) ? $val['variation_id'] : $val['product_id'];

                $order->add_product(wc_get_product($id_product), $val['quantity'], $args);
            }

            $name = $_POST['first_name'];
            $last_name = (strpos($name, ' ') === false) ? '' : preg_replace('#.*\s([\w-]*)$#', '$1', $name);
            $first_name = trim(preg_replace('#' . preg_quote($last_name, '#') . '#', '', $name));

            $addressBilling = array(
                'first_name' => $first_name,
                'last_name'  => $last_name,
                'company'    => $_POST['company'],
                'email'      => $_POST['email'],
                'phone'      => $_POST['phone'],
                'address_1'  => $_POST['address_1'],
                'address_2'  => $_POST['address_2'],
                'city'       => $_POST['city'],
                'company'    => $_POST['company'],
                'state'      => $_POST['state'],
                'postcode'   => $_POST['postcode'],
            );

            $order->set_address($addressBilling, 'billing');

            if ($_POST['entrega'] == 'entrega') {
                $order->set_address($addressBilling, 'shipping');
            }

            $order->set_status('wc-orcamento-pending');
            $order->set_customer_note($_POST['mensagem']);
            $save = $order->save();

            action_woocommerce_thankyou($save);


            $retorno = [
                'save' => $save,
                'textWhats' => textWhatsOrder($save, 'cliente')
            ];

            WC()->cart->empty_cart();

            echo json_encode($retorno);
        } else {
            echo json_encode([
                'error' => 'carrinho_vazio'
            ]);
        }
    } else {
        echo json_encode([
            'error' => 'verify_nonce'
        ]);
    }

    die();
}

add_action('wp_ajax_responderCliente', 'responderCliente_callback');
function responderCliente_callback()
{
    $to = $_POST['emailCliente'];
    $subject = 'Orçamento';
    $body    = $_POST['resposta'];
    $headers = array('Content-Type: text/html; charset=UTF-8; From: ' . get_option('blogname') . ' <' . get_option('admin_email') . '>');
    $sendMail = wp_mail($to, $subject, $body, $headers);
    echo json_encode(['wp_mail' => $sendMail]);
    die();
}


// Change add to cart text on single product page
add_filter('woocommerce_product_single_add_to_cart_text', 'woocommerce_add_to_cart_button_text_single');
function woocommerce_add_to_cart_button_text_single()
{
    return __('Pedir Orçamento', 'woocommerce');
}

// Change add to cart text on product archives page
add_filter('woocommerce_product_add_to_cart_text', 'woocommerce_add_to_cart_button_text_archives');
function woocommerce_add_to_cart_button_text_archives()
{
    return __('Orçamento', 'woocommerce');
}


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
function requirePageConfig()
{
?>
    <div class="wrap">
        <?php require_once(plugin_dir_path(__FILE__) . 'templates/admin_config.php'); ?>
    </div>
    <?php
}

// Add a custom metabox only for shop_order post type (order edit pages)
add_action('add_meta_boxes', 'add_meta_boxesws');
function add_meta_boxesws()
{
    add_meta_box(
        'custom_meta_box_resposta_email',
        __('Responder Orçamento por e-mail'),
        'add_btn_resp_orcamento_email',
        'shop_order',
        'normal',
        'default'
    );
    add_meta_box(
        'custom_meta_box_resposta_whatsapp',
        __('Responder Orçamento por WhatsApp'),
        'add_btn_resp_orcamento_whatsapp',
        'shop_order',
        'normal',
        'default'
    );
}

function add_btn_resp_orcamento_email()
{
    require_once(plugin_dir_path(__FILE__) . 'includes/meta_resp_orcamento_email.php');
}

function add_btn_resp_orcamento_whatsapp()
{
    require_once(plugin_dir_path(__FILE__) . 'includes/meta_resp_orcamento_whatsapp.php');
}


///Add TAB in SETTING Woocoomerce
add_filter('woocommerce_settings_tabs_array', 'cxc_add_settings_tab', 5);
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
            'name' => __('WhatsApp', 'config_orcamento_cw'),
            'type' => 'text',
            'desc' => __('Informe o WhatsApp da loja', 'config_orcamento_cw'),
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


function textWhatsOrder($order_id, $tipo = 'admin')
{
    $order = wc_get_order($order_id);
    $items = $order->get_items();

    $produtos = '';



    foreach ($items as $item_id => $item) {
        $product_id   = $item->get_product_id(); //Get the product ID
        $product      = $item->get_product($product_id);
        $atributos = '';
        if ($product->is_type('variation')) {

            $variation_attributes = $product->get_variation_attributes();
            $variation_id = $item->get_variation_id();
            $variation    = new WC_Product_Variation($variation_id);
            $attributes   = $variation->get_attributes();
            $atributos = '';
            if ($variation_attributes) {
                foreach ($variation_attributes as $attribute_taxonomy => $term_slug) {
                    $taxonomy = str_replace("attribute_", '', $attribute_taxonomy);
                    $attribute_name = wc_attribute_label($taxonomy, $product);

                    $termo = get_term_by('slug', $term_slug, $taxonomy);
                    $attribute_value = (isset($termo->name)) ? $termo->name : $term_slug;
                    $value = ($attribute_value == "") ? $item->get_meta($taxonomy) : $attribute_value;
                    $atributos .= $attribute_name . ": " . $value . "%0a";
                }
            }
        }

        $produtos .= "☑️ "  . $item->get_name() . " *(x" . $item->get_quantity() . ")* %0a";
        $produtos .= ($product->get_sku()) ? "SKU: " . $product->get_sku() . "%0a" : '';
        $produtos .= $atributos . "%0a";
    }


    if ($tipo == 'admin') {
        $pedido = "☑️ *ORÇAMENTO - " . $order->get_id() . "* %0a
🏠 *DADOS DA ENTREGA* %0a
*Nome:* " . $order->get_billing_first_name() . " " . $order->get_billing_last_name() . "%0a
*Endereço:* " . $order->get_shipping_address_1() . " %0a
*Complemento:* " . $order->get_shipping_address_2() . " %0a
*Bairro:* " . $order->get_shipping_country() . " %0a
*Cidade:* " . $order->get_billing_city() . " %0a
*Cep:* " . $order->get_billing_postcode() . " %0a
*Telefone/WhatsApp* " . $order->get_billing_phone() . " %0a
--------------------------%0a
➡ *RESUMO DO PEDIDO* %0a%0a" . $produtos . "-------------------------%0a%0a
💵 *RESPOSTA AO ORÇAMENTO* %0a
*Valor:* ?  %0a
*Prazo:* ?";
    } else {
        $pedido = "Olá! Me chamo *" . $order->get_billing_first_name() . "* %0a
Pedido: *" . $order->get_id() . "* %0a Tenho interesse nos seguintes produtos:%0a
--------------%0a
" . $produtos;
        $pedido .=  "--------------%0a 📝 " . $order->get_customer_note();
    }

    return $pedido;
}




// registration Field validation
add_filter('woocommerce_registration_errors', 'account_registration_field_validation', 10, 3);
function account_registration_field_validation($errors, $username, $email)
{
    if (isset($_POST['billing_whatsapp']) && empty($_POST['billing_whatsapp'])) {
        $errors->add('whatsApp_error', __('<strong>Error</strong>: account number is required!', 'woocommerce'));
    }
    if (isset($_POST['billing_cpf_cnpj']) && empty($_POST['billing_cpf_cnpj'])) {
        $errors->add('cpf_cnpj_error', __('<strong>Error</strong>: account number is required!', 'woocommerce'));
    }
    return $errors;
}

// Save registration Field value
// Save Field value in Edit account
add_action('woocommerce_created_customer', 'save_my_account_new_fields');
add_action('woocommerce_save_account_details', 'save_my_account_new_fields');
add_action('personal_options_update', 'save_my_account_new_fields', 999999);
add_action('edit_user_profile_update', 'save_my_account_new_fields', 999999);

function save_my_account_new_fields($user_id)
{
    if (isset($_POST['whatsapp']))
        update_user_meta($user_id, 'billing_whatsapp', preg_replace('/[^0-9]/', '', $_POST['whatsapp']));

    if (isset($_POST['cpf_cnpj']))
        update_user_meta($user_id, 'billing_cpf_cnpj', preg_replace('/[^0-9]/', '', $_POST['cpf_cnpj']));
}

// Display field in admin user billing fields section
add_filter('woocommerce_customer_meta_fields', 'admin_user_custom_billing_field', 9999999);
function admin_user_custom_billing_field($args)
{
    $args['billing']['fields']['billing_whatsapp'] = array(
        'label'         => __('WhatsApp', 'woocommerce'),
        'description'   => '',
        'custom_attributes'   => array('required' => true),
        'class' => 'maskTel'
    );

    $args['billing']['fields']['billing_cpf_cnpj'] = array(
        'label'         => __('CPF/CNPJ', 'woocommerce'),
        'description'   => '',
        'custom_attributes'   => array('required' => true),
        'class' => 'maskCpfCnpj'
    );
    return $args;
}


function cloudways_display_order_data_in_admin($order)
{
    if ($order->get_user()) {
        $user = $order->get_user();

        echo '<p class="form-field form-field-wide"><strong>' . __('WhatsApp') . ':</strong> ' . $user->billing_whatsapp . '</p>';
        echo '<p class="form-field form-field-wide"><strong>' . __('CPF/CNPJ') . ':</strong> ' . $user->billing_cpf_cnpj . '</p>';
    ?>

        <div class="edit_address">
            <?php woocommerce_wp_text_input(array('id' => 'billing_whatsapp', 'label' => __('WhatsApp'), 'wrapper_class' => '_billing_company_field')); ?>
            <?php woocommerce_wp_text_input(array('id' => 'billing_cpf_cnpj', 'label' => __('CPF/CNPJ'), 'wrapper_class' => '_billing_company_field')); ?>
        </div>
<?php
    }
}
add_action('woocommerce_admin_order_data_after_order_details', 'cloudways_display_order_data_in_admin');
