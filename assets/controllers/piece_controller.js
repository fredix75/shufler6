import { Controller } from '@hotwired/stimulus';
import {Modal} from "bootstrap";
import $ from 'jquery';

export default class extends Controller {

    async setNote(event) {
        const element = event.currentTarget;
        const id = element.dataset.id;

        fetch(`/fr/music/set-extra-note/${id}`, { credentials: 'same-origin' })
            .then((response) => response.json())
            .then((data) => {
                //console.log(event.target);
                if (data.note == -1) {
                    element.querySelector('i').classList.replace("bi-heart-fill", "bi-heart");
                } else {
                    element.querySelector('i').classList.replace("bi-heart", "bi-heart-fill");
                }
            });
        event.preventDefault();
    }
}
