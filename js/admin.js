var form, btnSubmit, textBtn;

class orcamento_admin {
    submit(e) {
        form = jQuery(e);
        btnSubmit = form.find('[type=submit]');
        textBtn = btnSubmit.html();
        btnSubmit.addClass('disabled').html('Aguarde... <i class="fa fa-spinner fa-pulse"></i>');

        var data = form.serializeJSON();

        jQuery.post(urlAjax[0], data, function (res) {
            res = JSON.parse(res);
            console.log('RESP', res);

        }).fail(function () {
            alert("error");
        }).always(function () {
            btnSubmit.removeClass('disabled').html(textBtn);
        });
    }
}

var ORCAMENTO = new orcamento_admin();