import { Controller } from '@hotwired/stimulus';
import $ from "jquery";
import {Modal} from "bootstrap";

/*
* The following line makes this controller "lazy": it won't be downloaded until needed
* See https://github.com/symfony/stimulus-bridge#lazy-controllers
*/
/* stimulusFetch: 'lazy' */
export default class extends Controller {

    async displayContent(event) {
        $('.block-content-album').remove();
        let artist = $(event.target).closest('a').data('artist');
        artist = artist.replaceAll('&', '%26');
        let album = $(event.target).closest('a').data('album');
        album = album.toString().replaceAll('&', '%26');
        let url = '/fr/music/tracks_album';
        let query = '?artist=' + artist + '&album=' + album;
        let content = await $.ajax(url + query);
        $(content).insertAfter($(event.target).closest('.album'));
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
            if (result.youtube_key != '') {
                if ($('a#album-youtube-' + result.id).length > 0) {
                    $('a#album-youtube-' + result.id).attr('href', 'https://www.youtube.com/watch?v=' + result.youtube_key);
                } else {
                    let btn = '<a id="album-youtube-' + result.id +'" class="playlist-link icon-youtube" href="https://www.youtube.com/watch?v=' + result.youtube_key + '" data-id="' + result.youtube_key + '" data-action="music#popupPlaylist" title="video Playlist">\n' +
                        '<i class="bi bi-youtube"></i>\n' +
                        '</a>';
                    $('#album-btn').prepend(btn);
                }
            }
            $('#formModal').modal('hide');
        });
    }

    async openEditModal(event) {
        event.preventDefault();
        if ($(event.target).closest('a').data('id')) {
            let id = $(event.target).closest('a').data('id');
            const modal = new Modal('#formModal', {keyboard: false});
            modal.show();
            $(document).find('.modal-body').html(await $.ajax('/fr/music/album/edit/' + id));
        }
    }
}
