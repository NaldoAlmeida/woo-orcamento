<?php

$order_id = isset($_GET['post']) ? $_GET['post'] : false;
if (!$order_id) return; // Exit
$order = wc_get_order($order_id);
$pedido = textWhatsOrder($order_id);
$user = $order->get_user();


?>
<textarea name="resp_whatsapp" cols="30" rows="7" style="width: 100%"><?php echo $pedido ?></textarea>
<p>
    <span>Será encaminhado para o WhatsApp <i>+55 <?php echo $user->billing_whatsapp; ?></i></span>
</p>
<p>
    <button type="button" class="button button-primary" onclick="respondeClienteWhatsApp()"><?php _e('Responder Cliente'); ?></button>
</p>
</form>

<script>
    function respondeClienteWhatsApp() {
        let resposta = jQuery('textarea[name="resp_whatsapp"]').val();
        let url = "<?php echo "https://api.whatsapp.com/send?phone=+55" . str_replace(' ', '', $user->billing_whatsapp) . "&text="; ?>" + resposta;
        console.log('URL ZAP: ', url)
        window.open(url, '_blank').focus();
    }
</script>