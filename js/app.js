var form, btnSubmit, textBtn;

class orcamento {
    submit(e) {
        Swal.fire({
            title: 'Aguarde',
            onOpen: () => {
                swal.showLoading();
            }
        })
        form = jQuery(e);
        btnSubmit = form.find('[type=submit]');
        textBtn = btnSubmit.html();
        btnSubmit.addClass('disabled').html('Aguarde... <i class="fa fa-spinner fa-pulse"></i>');

        var data = form.serializeJSON();

        jQuery.post(urlAjax, data, function (res) {
            res = JSON.parse(res);
            console.log('RESP', res);
            btnSubmit.removeClass('disabled').html(textBtn);
            Swal.close();
            if (res.error) {
                let text = '';
                switch (res.error) {
                    case 'carrinho_vazio':
                        text = 'Não foi encontrado nenhum produto. Por favor atualize a página'
                        break;

                    case 'verify_nonce':
                        text = 'Por favor atualize a página';
                        break;

                    default:
                        text = 'Ocorreu um erro desconhecido';
                }

                Swal.fire({
                    icon: 'error',
                    title: 'OPS!',
                    text: text,
                    showConfirmButton: false,
                    footer: '<button class="swal2-confirm swal2-styled" type="button" onclick="window.location.reload()">Atualizar</button>'
                })
            } else {
                let url = "https://api.whatsapp.com/send?phone=+551125910648&text=" + res.textWhats;

                jQuery('#final_orcamento').html(`<div class="respOrcamento">
                <h1>OBRIGADO!<br><small>Pedido recebido</small></h1>
                <p>Envie também pelo WhatsApp clicando no botão abaixo:</p>
                <a class="btn-zap" href="${url}" target="_blanc">Enviar por WhatsApp</a>
                <p>Você pode acompanhar seus pedidos <a href="/minha-conta">clicando aqui</a></p>
                </div>`)

                jQuery('html, body').animate({
                    scrollTop: (jQuery("#final_orcamento").offset().top - 50)
                }, 1000);
            }

        })
    }

    buscaCep(el) {

        let cep = jQuery(el).val();

        jQuery.getJSON('https://viacep.com.br/ws/' + cep + '/json/', function (res) {
            console.log('res', res)
            if (res.erro) {
                Swal.fire({
                    icon: 'error',
                    text: 'CEP não encontrado'
                })
            } else {
                jQuery('#rua').val(res.logradouro)
                jQuery('#numero').focus()
                jQuery('#cidade').val(res.localidade)
                jQuery('#state').val(res.uf)
            }
        })
    }
}

var ORCAMENTO = new orcamento();


function ativaMascaras() {
    jQuery('.maskTel').mask('(00) 0000-00000');
    jQuery('.maskCep').mask('00000-000');
    jQuery('.maskData').mask('00/00/0000');
    jQuery('.maskCpf').mask('000.000.000-00', {
        reverse: false
    });
    jQuery('.maskRg').mask('00.000.000-0');
    jQuery('.porcentagem1').mask('00.9', {
        reverse: true
    });
    jQuery('.maskCnpj').mask('00.000.000/0000-00', {
        reverse: true
    });


    var options = {
        onKeyPress: function (cpfcnpj, e, field, options) {
            var masks = ['000.000.000-009', '00.000.000/0000-00'];
            var mask = (cpfcnpj.length > 14) ? masks[1] : masks[0];
            if (cpfcnpj.length > 14) {
                jQuery('.field_company').show()
            } else {
                jQuery('.field_company').hide()
            }
            jQuery('.maskCpfCnpj').mask(mask, options);
        },

        // onChange: function (cep, e, field) {
        //     jQuery(field).attr({ onBlur: 'validadeCpfCnpj(this)' })
        // },

    };
    jQuery('.maskCpfCnpj').mask('000.000.000-009', options);
}

jQuery(document).ready(function () {
    ativaMascaras();
})
