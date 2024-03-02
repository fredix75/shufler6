import { Controller } from '@hotwired/stimulus';
import { Modal } from 'bootstrap';
import $ from 'jquery';

export default class extends Controller {

    static values = {
        id: String,
    };

    connect() {
        let select = document.getElementById('flux_form_channel');
        let control = select.tomselect;
        control.addOption({value : 0, text: 'Add new Channel'});
    }

    async submitForm(event) {
        const form = document.querySelector('.modal-body form');
        const formData = new FormData(form);

        try {
            const response = await fetch($(form).prop('action'), {
                method: form.method,
                body: formData
            });

            if (!response.ok) {
                const errorText = await response.text();
                document.querySelector(".modal-body").innerHTML = errorText;
                return;
            }

            const result = await response.json();
            const select = document.getElementById('flux_form_channel');
            let control = select.tomselect;
            if (this.idValue === '0') {
                control.addOption({ value: result.id, text: result.name });
                control.setValue(result.id);
            } else {
                control.updateOption(result.id, { value: result.id, text: result.name });
            }

            let modal = document.querySelector('#formModal');
            Modal.getInstance(modal).hide();
        } catch (error) {
            console.error('Error:', error);
        }
        event.stopPropagation();
    }

    async openModal(event) {
        if ($(event.target).val() === '0' || $(event.target).closest('a').data('channel')) {
            this.idValue = $(event.target).closest('a').data('channel') ?? 0;
            const modal = new Modal('#formModal', {keyboard: false});
            modal.show();
            $(document).find('.modal-body').html(await $.ajax('/fr/channel/edit/' + this.idValue));
            event.stopPropagation();
        } else {
            $('#btn-edit-channel').data('channel', $(event.target).val());
        }
    }
}