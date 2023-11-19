import { Controller } from '@hotwired/stimulus';
import $ from 'jquery';
import {Modal} from "bootstrap";

export default class extends Controller {
    static values = {
        formUrl: String,
    }
    static targets = ['modal', 'modalBody'];

    async openModal(event) {
        const modal = new Modal('#formModal', {keyboard: false});
        modal.show();
        $(document).find('.modal-body').html(await $.ajax(this.formUrlValue));
    }
    
    toggleSlide(event) {
        $('#slider').slideToggle();
    }

}