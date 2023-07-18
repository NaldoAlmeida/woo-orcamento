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