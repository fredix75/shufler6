import { Controller } from '@hotwired/stimulus';
import { Modal } from 'bootstrap';
import $ from 'jquery';

export default class extends Controller {
    static targets = ['modal', 'modalBody'];

    async openModal(event) {
        let query = '?';
        if ($(event.target).closest('a').data('artist')) {
            let artist = $(event.target).closest('a').data('artist') ?? 0;
            query += 'artist=' + artist;
        }
        if ($(event.target).closest('a').data('album')) {
            let album = $(event.target).closest('a').data('album') ?? 0;
            query += query != '?' ? '&' : '';
            query += 'album=' + album;
        }
        const modal = new Modal('#formModal', {keyboard: false});
        modal.show();
        $(document).find('.modal-body').html(await $.ajax('/fr/music/tracks_album' + query));
        event.preventDefault()
    }


}