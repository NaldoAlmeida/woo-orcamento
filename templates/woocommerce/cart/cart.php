<?php

/**
 * Cart Page
 *
 * This template can be overridden by copying it to yourtheme/woocommerce/cart/cart.php.
 *
 * HOWEVER, on occasion WooCommerce will need to update template files and you
 * (the theme developer) will need to copy the new files to your theme to
 * maintain compatibility. We try to do this as little as possible, but it does
 * happen. When this occurs the version of the template file will be bumped and
 * the readme will list any important changes.
 *
 * @see     https://docs.woocommerce.com/document/template-structure/
 * @package WooCommerce\Templates
 * @version 7.4.0
 */

defined('ABSPATH') || exit;

do_action('woocommerce_before_cart'); ?>

<div id="final_orcamento" class="cw-container">
	<form class="woocommerce-cart-form" action="<?php echo esc_url(wc_get_cart_url()); ?>" method="post">
		<?php do_action('woocommerce_before_cart_table'); ?>

		<table class="shop_table shop_table_responsive cart woocommerce-cart-form__contents" cellspacing="0">
			<thead>
				<tr>
					<th class="product-remove"><span class="screen-reader-text"><?php esc_html_e('Remove item', 'woocommerce'); ?></span></th>
					<th class="product-thumbnail"><span class="screen-reader-text"><?php esc_html_e('Thumbnail image', 'woocommerce'); ?></span></th>
					<th class="product-name"><?php esc_html_e('Product', 'woocommerce'); ?></th>
					<th class="product-price"><?php esc_html_e('Price', 'woocommerce'); ?></th>
					<th class="product-quantity"><?php esc_html_e('Quantity', 'woocommerce'); ?></th>
					<th class="product-subtotal"><?php esc_html_e('Subtotal', 'woocommerce'); ?></th>
				</tr>
			</thead>
			<tbody>
				<?php do_action('woocommerce_before_cart_contents'); ?>

				<?php
				foreach (WC()->cart->get_cart() as $cart_item_key => $cart_item) {
					$_product   = apply_filters('woocommerce_cart_item_product', $cart_item['data'], $cart_item, $cart_item_key);
					$product_id = apply_filters('woocommerce_cart_item_product_id', $cart_item['product_id'], $cart_item, $cart_item_key);

					if ($_product && $_product->exists() && $cart_item['quantity'] > 0 && apply_filters('woocommerce_cart_item_visible', true, $cart_item, $cart_item_key)) {
						$product_permalink = apply_filters('woocommerce_cart_item_permalink', $_product->is_visible() ? $_product->get_permalink($cart_item) : '', $cart_item, $cart_item_key);
				?>
						<tr class="woocommerce-cart-form__cart-item <?php echo esc_attr(apply_filters('woocommerce_cart_item_class', 'cart_item', $cart_item, $cart_item_key)); ?>">

							<td class="product-remove">
								<?php
								echo apply_filters( // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
									'woocommerce_cart_item_remove_link',
									sprintf(
										'<a href="%s" class="remove" aria-label="%s" data-product_id="%s" data-product_sku="%s">&times;</a>',
										esc_url(wc_get_cart_remove_url($cart_item_key)),
										esc_html__('Remove this item', 'woocommerce'),
										esc_attr($product_id),
										esc_attr($_product->get_sku())
									),
									$cart_item_key
								);
								?>
							</td>

							<td class="product-thumbnail">
								<?php
								$thumbnail = apply_filters('woocommerce_cart_item_thumbnail', $_product->get_image(), $cart_item, $cart_item_key);

								if (!$product_permalink) {
									echo $thumbnail; // PHPCS: XSS ok.
								} else {
									printf('<a href="%s">%s</a>', esc_url($product_permalink), $thumbnail); // PHPCS: XSS ok.
								}
								?>
							</td>

							<td class="product-name" data-title="<?php esc_attr_e('Product', 'woocommerce'); ?>">
								<?php
								if (!$product_permalink) {
									echo wp_kses_post(apply_filters('woocommerce_cart_item_name', $_product->get_name(), $cart_item, $cart_item_key) . '&nbsp;');
								} else {
									echo wp_kses_post(apply_filters('woocommerce_cart_item_name', sprintf('<a href="%s">%s</a>', esc_url($product_permalink), $_product->get_name()), $cart_item, $cart_item_key));
								}

								do_action('woocommerce_after_cart_item_name', $cart_item, $cart_item_key);

								// Meta data.
								echo wc_get_formatted_cart_item_data($cart_item); // PHPCS: XSS ok.

								// Backorder notification.
								if ($_product->backorders_require_notification() && $_product->is_on_backorder($cart_item['quantity'])) {
									echo wp_kses_post(apply_filters('woocommerce_cart_item_backorder_notification', '<p class="backorder_notification">' . esc_html__('Available on backorder', 'woocommerce') . '</p>', $product_id));
								}
								?>
							</td>

							<td class="product-price" data-title="<?php esc_attr_e('Price', 'woocommerce'); ?>">
								<?php
								echo apply_filters('woocommerce_cart_item_price', WC()->cart->get_product_price($_product), $cart_item, $cart_item_key); // PHPCS: XSS ok.
								?>
							</td>

							<td class="product-quantity" data-title="<?php esc_attr_e('Quantity', 'woocommerce'); ?>">
								<?php
								if ($_product->is_sold_individually()) {
									$min_quantity = 1;
									$max_quantity = 1;
								} else {
									$min_quantity = 0;
									$max_quantity = $_product->get_max_purchase_quantity();
								}

								$product_quantity = woocommerce_quantity_input(
									array(
										'input_name'   => "cart[{$cart_item_key}][qty]",
										'input_value'  => $cart_item['quantity'],
										'max_value'    => $max_quantity,
										'min_value'    => $min_quantity,
										'product_name' => $_product->get_name(),
									),
									$_product,
									false
								);

								echo apply_filters('woocommerce_cart_item_quantity', $product_quantity, $cart_item_key, $cart_item); // PHPCS: XSS ok.
								?>
							</td>

							<td class="product-subtotal" data-title="<?php esc_attr_e('Subtotal', 'woocommerce'); ?>">
								<?php
								echo apply_filters('woocommerce_cart_item_subtotal', WC()->cart->get_product_subtotal($_product, $cart_item['quantity']), $cart_item, $cart_item_key); // PHPCS: XSS ok
								?>
							</td>
						</tr>
				<?php
					}
				}
				?>

				<?php do_action('woocommerce_cart_contents'); ?>

				<tr>
					<td colspan="6" class="actions">
						<b>Total <?php wc_cart_totals_order_total_html(); ?></b>
						<!-- <?php if (wc_coupons_enabled()) { ?>
						<div class="coupon">
							<label for="coupon_code" class="screen-reader-text"><?php esc_html_e('Coupon:', 'woocommerce'); ?></label> <input type="text" name="coupon_code" class="input-text" id="coupon_code" value="" placeholder="<?php esc_attr_e('Coupon code', 'woocommerce'); ?>" /> <button type="submit" class="button<?php echo esc_attr(wc_wp_theme_get_element_class_name('button') ? ' ' . wc_wp_theme_get_element_class_name('button') : ''); ?>" name="apply_coupon" value="<?php esc_attr_e('Apply coupon', 'woocommerce'); ?>"><?php esc_attr_e('Apply coupon', 'woocommerce'); ?></button>
							<?php do_action('woocommerce_cart_coupon'); ?>
						</div>
					<?php } ?> -->

						<button type="submit" class="button<?php echo esc_attr(wc_wp_theme_get_element_class_name('button') ? ' ' . wc_wp_theme_get_element_class_name('button') : ''); ?>" name="update_cart" value="<?php esc_attr_e('Update cart', 'woocommerce'); ?>">Atualizar orçamento</button>

						<?php do_action('woocommerce_cart_actions'); ?>

						<?php wp_nonce_field('woocommerce-cart', 'woocommerce-cart-nonce'); ?>
					</td>
				</tr>



				<?php do_action('woocommerce_after_cart_contents'); ?>
			</tbody>
		</table>
		<?php do_action('woocommerce_after_cart_table'); ?>
	</form>



	<?php
	$user = false;
	$userWp  = wp_get_current_user();
	$meta = [];
	if ($userWp->ID) {
		$user = new WC_Customer($userWp->ID);
		$endereco = $user->billing;
	?>
		<div class="boasVindas">
			<p>
				Olá <b><?php echo $user->display_name ?></b>, seja bem vindo(a)! <a class="button" href="<?php echo wp_logout_url(get_permalink()); ?>">Sair <i class="fa fa-sign-out-alt"></i></a>
			</p>
		</div>
	<?php
	} else {
	?>
		<p>Já tem conta? <a href="#" onclick="jQuery('.login-modal').addClass('show')" class="button">Fazer login</a></p>
		<p>Ou preecha o formulário abaixo para finalizar o orçamento</p>
		<div class="login-modal">
			<div class="close" onclick="jQuery('.login-modal').removeClass('show')"></div>
			<div class="content">
				<button class="close" onclick="jQuery('.login-modal').removeClass('show')">&times;</button>
				<?php
				include(plugin_dir_path(__DIR__) . 'myaccount/form-login.php');
				?>
			</div>
		</div>
		<?php
		if (isset($_POST['login'])) {
		?>
			<script>
				setTimeout(function() {
					jQuery('.login-modal').addClass('show')
				}, 3000)
			</script>
	<?php
		}
	}

	?>


	<form onsubmit="event.preventDefault(); ORCAMENTO.submit(this)">
		<input type="hidden" name="action" value="submitOrcamento">

		<?php wp_nonce_field("finaliza_orcamento", "finaliza_orcamento"); ?>
		<div class="row">
			<div class="col s12 m6">
				<div class="input-group">
					<label for="nameCliente">Seu Nome</label>
					<input required type="text" id="first_name" name="first_name" placeholder="Seu nome" value="<?php echo ($user) ? esc_attr($user->display_name) : ''; ?>">
				</div>

				<p class="woocommerce-form-row woocommerce-form-row--wide form-row form-row-wide">
					<label for="reg_cpf_cnpj"><?php _e('CPF/CNPJ', 'woocommerce'); ?> <span class="required">*</span></label>
					<input type="tel" class="maskCpfCnpj input-text" name="billing_cpf_cnpj" id="reg_cpf_cnpj" value="<?php echo esc_attr($userWp->billing_cpf_cnpj); ?>" />
				</p>

				<div class="field_company input-group" style="display: none">
					<label for="empresaCliente">Empresa</label>
					<input type="text" id="empresaCliente" name="company" placeholder="Nome da empresa" value="<?php echo (isset($endereco['company'])) ? esc_attr($endereco['company']) : ''; ?>">
				</div>

				<div class="input-group">
					<label for="emailCliente">Email</label>
					<input required type="email" id="emailCliente" name="email" placeholder="Seu e-mail" value="<?php echo ($user) ? esc_attr($user->email) : ''; ?>">
				</div>


				<p class="woocommerce-form-row woocommerce-form-row--wide form-row form-row-wide">
					<label for="reg_whatsapp"><?php _e('WhatsApp', 'woocommerce'); ?> <span class="required">*</span></label>
					<input required type="tel" class="maskTel input-text" name="billing_whatsapp" id="reg_whatsapp" value="<?php echo esc_attr($userWp->billing_whatsapp); ?>" />
				</p>

				<div class="input-group">
					<label for="telCliente">Telefone</label>
					<input type="tel" id="telCliente" name="phone" placeholder="Seu telefone" value="<?php echo (isset($endereco['phone'])) ? esc_attr($endereco['phone']) : ''; ?>">
				</div>

			</div>
			<div class="col s12 m6">
				<div class="row">
					<div class="col s12 m6">
						<p class="entrega-retira input-group radio_checkbox">
							<label><input type="radio" name="entrega" checked value="entrega"><span>Entregar</span></label>
							<label><input type="radio" name="entrega" value="retira"><span>Retirar</span></label>
						</p>
					</div>
					<div class="col s12 m6 input-group set-entrega show">
						<label for="cep">CEP</label>
						<input type="tel" name="postcode" id="cep" onblur="ORCAMENTO.buscaCep(this)" value="<?php echo (isset($endereco['postcode'])) ? esc_attr($endereco['postcode']) : ''; ?>">
					</div>
				</div>

				<div class="row set-entrega show">
					<div class="col s9 input-group">
						<label for="rua">Rua</label>
						<input type="text" name="address_1" id="rua" value="<?php echo (isset($endereco['address_1'])) ? esc_attr($endereco['address_1']) : ''; ?>">
					</div>
					<div class="col s3 input-group">
						<label for="numero">Número</label>
						<input type="tel" name="address_2" id="numero" value="<?php echo (isset($endereco['address_2'])) ? esc_attr($endereco['address_2']) : ''; ?>">
					</div>
					<div class="col s12 m8 input-group">
						<label for="cidade">Cidade</label>
						<input type="tel" name="city" id="cidade" value="<?php echo (isset($endereco['city'])) ? esc_attr($endereco['city']) : ''; ?>">
					</div>
					<div class="col s12 m4 input-group">
						<label for="state">UF</label>
						<select name="state" id="state" class="browser-default">
							<?php
							$estadosBrasileiros = array(
								0   => 'Estado',
								'AC' => 'Acre',
								'AL' => 'Alagoas',
								'AP' => 'Amapá',
								'AM' => 'Amazonas',
								'BA' => 'Bahia',
								'CE' => 'Ceará',
								'DF' => 'Distrito Federal',
								'ES' => 'Espírito Santo',
								'GO' => 'Goiás',
								'MA' => 'Maranhão',
								'MT' => 'Mato Grosso',
								'MS' => 'Mato Grosso do Sul',
								'MG' => 'Minas Gerais',
								'PA' => 'Pará',
								'PB' => 'Paraíba',
								'PR' => 'Paraná',
								'PE' => 'Pernambuco',
								'PI' => 'Piauí',
								'RJ' => 'Rio de Janeiro',
								'RN' => 'Rio Grande do Norte',
								'RS' => 'Rio Grande do Sul',
								'RO' => 'Rondônia',
								'RR' => 'Roraima',
								'SC' => 'Santa Catarina',
								'SP' => 'São Paulo',
								'SE' => 'Sergipe',
								'TO' => 'Tocantins'
							);
							foreach ($estadosBrasileiros as $uf => $nome) {
								$selected = ($endereco['state'] == $uf) ? 'selected' : '';
								echo '<option ' . $selected . ' value="' . $uf . '">' . $nome . '</option>';
							}
							?>
						</select>
					</div>

				</div>

				<div class="input-group">
					<label for="mensagemCliente">Mensagem</label>
					<textarea id="mensagemCliente" name="mensagem" rows="10" placeholder="Sua mensagem"></textarea>
				</div>
			</div>
			<div class="col s12 center-align">
				<button type="submit" class="button alt">
					Finalizar orçamento
				</button>
			</div>
		</div>
	</form>

</div>


<style>
	.radio_checkbox label {
		display: inline-block;
		width: min-content;
		background-color: #e9e9e9;
		margin-right: 5px;
		padding: 8px 17px 8px 8px;
		border-radius: 5px;
		margin-top: 11px;
	}

	.set-entrega {
		display: none;
	}

	.set-entrega.show {
		display: block;
	}
</style>

<script>
	jQuery('[name="entrega"]').change(function() {
		var valor = jQuery(this).val();
		console.log('valor', valor);
		if (valor == 'entrega') {
			jQuery('.set-entrega').addClass('show')
			jQuery('.set-entrega input').attr({
				required: true
			})
		} else {
			jQuery('.set-entrega').removeClass('show')
			jQuery('.set-entrega input').removeAttr('required')
		}
	})
</script>