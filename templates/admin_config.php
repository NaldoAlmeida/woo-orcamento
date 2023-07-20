<form style="max-width: 800px;" onsubmit="event.preventDefault(); ORCAMENTO.submit(this)">
    <div class="cw-container">
        <h1>Configurações - Orçamentos CW</h1>
        <hr>
        <?php
        //printR($option);
        ?>
        <input type="hidden" name="action" value="configuracoes">
        <?php wp_nonce_field("finaliza_orcamento", "finaliza_orcamento"); ?>
        <table class="form-table">
            <tbody>
                <tr>
                    <th class="title">
                        Ocultar Preço
                    </th>
                    <td>
                        <label>
                            <input type="checkbox" name="ocultar_preco" <?php echo getConfigOrcamento('ocultar_preco') ?> value="checked">
                            <span>Sim</span>
                        </label>
                    </td>
                </tr>

                <tr>
                    <th class="title">
                        WhatsApp
                    </th>
                    <td>
                        <label>
                            <input type="tel" name="whatsapp" value="<?php echo getConfigOrcamento('whatsapp') ?>">
                        </label>
                    </td>
                </tr>
            </tbody>
        </table>

        <button class="button button-primary button-large" type="submit">Atualizar</button>

    </div>

</form>