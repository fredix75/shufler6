import { Controller } from '@hotwired/stimulus';
import {Modal} from "bootstrap";
import $ from 'jquery';

export default class extends Controller {

    async setNote(event) {
        const element = event.currentTarget;
        const id = element.dataset.id;
        const note = element.dataset.note;

        const data = new FormData();
        data.append('key', id);
        data.append('note', note);

        fetch(`/fr/music/set-extra-note`, {
            method: 'POST', body: data })
            .then((response) => response.json())
            .then((data) => {
                //console.log(event.target);
                if (data.note === -1 || !data.note) {
                    element.querySelector('i').classList.replace("bi-heart-fill", "bi-heart");
                } else {
                    element.querySelector('i').classList.replace("bi-heart", "bi-heart-fill");
                }
            });
        event.preventDefault();
    }
}
