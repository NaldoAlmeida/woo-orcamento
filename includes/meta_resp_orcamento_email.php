<?php

$order_id = isset($_GET['post']) ? $_GET['post'] : false;
if (!$order_id) return; // Exit
$order = wc_get_order($order_id);
$items = $order->get_items();

$style = [];
$style['td'] = 'padding: 10px';
$style['tr'] = 'border: 1px solid #333';

$content = '<h4>Orçamento #' . $order_id . '</h4>';
$content .= '<p>Olá <b>Cliente</b>, segue resposta a sua solicitação de orçamento.</p>';
$content .= '<table style="width: 100%">';
$content .= '<thead>';
$content .= '<tr>';
$content .= '<th>Produto</th>';
$content .= '<th>Qnt</th>';
$content .= '</tr>';
$content .= '</thead>';
$content .= '<tbody>';

foreach ($items as $item_id => $item) {
    $content .= '<tr style="' . $style['tr'] . '">';

    $product_id   = $item->get_product_id(); //Get the product ID
    $product      = $item->get_product($product_id);
    $quantity     = $item->get_quantity(); //Get the product QTY
    $product_name = $item->get_name(); //Get the product NAME
    $content .= '<td style="' . $style['td'] . '">' . $product_name;


    if ($product->is_type('variation')) {

        $variation_attributes = $product->get_variation_attributes();
        $variation_id = $item->get_variation_id();
        $variation    = new WC_Product_Variation($variation_id);
        $attributes   = $variation->get_attributes();

        if ($variation_attributes) {

            $content .= '<ul>';
            foreach ($variation_attributes as $attribute_taxonomy => $term_slug) {
                $taxonomy = str_replace('attribute_', '', $attribute_taxonomy);
                $attribute_name = wc_attribute_label($taxonomy, $product);

                $termo = get_term_by('slug', $term_slug, $taxonomy);
                $attribute_value = (isset($termo->name)) ? $termo->name : $term_slug;
                $value = ($attribute_value == '') ? $item->get_meta($taxonomy) : $attribute_value;
                $content .= '<li>' . $attribute_name . ': ' . $value . '</li>';
            }

            $content .= ($product->get_sku()) ? "<li>SKU: " . $product->get_sku() . "</li>" : '';


            $content .= '</ul>';
        }
    }

    $content .= '</td>';
    $content .= '<td style="' . $style['td'] . '">' . $quantity . '</td>';

    $content .= '</tr>';
}

$content .= '</tbody>';
$content .= '</table>';


$content .= '<p>O valor fica em <b>R$ ?,??</b></p>';
$content .= '<p>O prazo é de <b>?</b> dias, contados a partir da assinatura do contrato.</p>';

$content .= '<hr>';
$content .= 'Atenciosamente<br><b>Rofida Festas</b>';

?>

<?php wp_editor($content, 'form_resp_orcamento'); ?>
<p>
    <span>Será encaminhado para o e-mail <i><?php echo $order->get_billing_email(); ?></i></span>
</p>
<p>
    <button type="button" class="button button-primary" onclick="respondeCliente()"><?php _e('Responder Cliente'); ?></button>
</p>
</form>

<script>
    function respondeCliente() {
        Swal.fire({
            title: 'Enviando resposta',
            onOpen: () => {
                swal.showLoading();
            }
        })

        let resposta = tinyMCE.activeEditor.getContent();

        var data = {
            action: 'responderCliente',
            resposta: resposta,
            emailCliente: '<?php echo $order->get_billing_email(); ?>'
        }
        jQuery.post(ajaxurl, data, function(res) {
            console.log('RESP', res);
            Swal.close();
            try {
                let resp = JSON.parse(res);
                if (resp.wp_mail) {
                    Swal.fire({
                        icon: 'success',
                        title: 'Ok',
                        html: 'E-mail enviado com sucesso',
                    })
                } else {
                    Swal.fire({
                        icon: 'error',
                        title: 'OPS',
                        html: 'E-mail não enviado',
                    })
                }
            } catch (error) {
                Swal.fire({
                    icon: 'error',
                    title: 'OPS',
                    html: 'E-mail não enviado',
                })
            }
        })
    }
</script>