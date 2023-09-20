import { Controller } from '@hotwired/stimulus';
import { Modal } from 'bootstrap';
import $ from 'jquery';

export default class extends Controller {
    static targets = ['modal', 'modalBody'];

    connect() {
        $('select[name="flux_form[channel]"]')
            .append($('<option>', {value : 'new', text: 'Add new Channel'}));
    }

    async submitForm() {
        const $form = $('#formModal').find('form');
        $('#formModal').find('.form-body').innerHTML = await $.ajax({
            url: $form.prop('action'),
            method: $form.prop('method'),
            data:  new FormData($form[0]),
            processData: false,
            contentType: false,
            dataType	: 'json', // what type of data do we expect back from the server
            encode		: true,
            error       : function(data) {
                console.log(data.responseText);
            }
        }).done(function(result) {
            if ($('select[name="flux_form[channel]"] option[value="' + result.id + '"]').length === 0) {
                $('select[name="flux_form[channel]"]').append('<option value="' + result.id + '">' + result.name + '</option>');
                $('select[name="flux_form[channel]"]').val(result.id);
            }
            $('#formModal').modal.hide();
        });

    }

    async openModal(event) {
        if ($(event.target).val() === 'new' || $(event.target).closest('a').data('channel')) {
            let id = $(event.target).closest('a').data('channel') ?? 0;
            const modal = new Modal('#formModal', {keyboard: false});
            modal.show();
            $(document).find('.modal-body').html(await $.ajax('/fr/channel/edit/' + id));
        }
        event.preventDefault()
    }
}