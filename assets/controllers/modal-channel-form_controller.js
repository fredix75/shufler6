import { Controller } from '@hotwired/stimulus';
import { Modal } from 'bootstrap';
import $ from 'jquery';

export default class extends Controller {
    static targets = ['modal', 'modalBody'];

    connect() {
        let select = document.getElementById('flux_form_channel');
        let control = select.tomselect;
        control.addOption({value : 'new', text: 'Add new Channel'});
    }

    async submitForm() {
        const $form = $('#formModal').find('form');
        await $.ajax({
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
            let select = document.getElementById('flux_form_channel');
            let control = select.tomselect;
            control.addOption({value: result.id, text: result.name});
            $('#formModal').modal('hide');
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