import { Controller } from '@hotwired/stimulus';
import { Modal } from "bootstrap";
import $ from 'jquery';

/*
* The following line makes this controller "lazy": it won't be downloaded until needed
* See https://github.com/symfony/stimulus-bridge#lazy-controllers
*/
/* stimulusFetch: 'lazy' */
export default class extends Controller {

    async displayContent(event) {
        document.querySelectorAll('.block-content-album').forEach(el => el.hidden = true);
        let artist = event.target.closest('a')?.dataset.artist;
        artist = artist.toString().replaceAll('&', '%26');
        let album = event.target.closest('a')?.dataset.album;
        album = album.toString().replaceAll('&', '%26');
        album = album.toString().replaceAll('#', '%23');
        album = album.toString().replaceAll('+', '%2B');
        const url = '/fr/music/tracks_album';
        const query = '?artist=' + artist + '&album=' + album;
        const response = await fetch(url + query);
        const content = await response.text();
        event.target
            .closest('.album')
            ?.insertAdjacentHTML('afterend', content);
        // jquery inévitable !
        $('.block-content-album').toggle("slow");
    }

    async saveAlbum(event){
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
            let picture = $('.album-picture-' + result.id);
            picture.attr('src', result.picture);
            if (result.youtube_key != null) {
                let element = $('a#album-youtube-' + result.id);
                element.attr('href', 'https://www.youtube.com/watch?v=' + result.youtube_key);
                if (element.hasClass('no-link')) {
                    element.removeClass('no-link');
                }
                if (!element.hasClass('video-link icon-youtube')) {
                    element.addClass('video-link icon-youtube');
                }
                if (element.children('i').length === 0) {
                    element.html('<i class="bi bi-youtube"></i>');
                }
                element.data('action', 'music#popupPlaylist');
            } else {
                if ($('a#album-youtube-' + result.id).length > 0) {
                    $('a#album-youtube-' + result.id).remove();
                }
            }
            let modal = document.querySelector('#formModal');
            Modal.getInstance(modal).hide();
        });
    }

    async openEditModal(event) {
        event.preventDefault();
        if ($(event.target).closest('a').data('id')) {
            let id = $(event.target).closest('a').data('id');
            let modal = document.querySelector('#formModal');
            let m = Modal.getInstance(modal);
            if (m != null) {
                m.hide();
            }
            modal = new Modal('#formModal', {keyboard: false});
            modal.show();
            $(document).find('.modal-body').html(await $.ajax('/fr/music/album/edit/' + id));
        }
    }

    editPicture(event) {
        let url = $(event.target).closest('a').data('url');
        $('input[name="albumpicture"]').val(url);
        $('form[name="form_album_edit"]').submit();
    }
}
