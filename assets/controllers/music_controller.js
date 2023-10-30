import { Controller } from '@hotwired/stimulus';
import $ from 'jquery';
import 'magnific-popup';
import DataTable from 'datatables.net';
import {Modal} from "bootstrap";

export default class extends Controller {

    static values = {
        columns: Array,
        pathUrl: String,
        pageLength: Number
    }

    static targets = ['modal', 'modalBody'];

    connect() {
        let columns = [];
        this.columnsValue.forEach(function(item){
            columns.push({data : item});
        });

        let table = new DataTable('#container-datas', {
            processing: true,
            serverSide: true,
            select: true,
            responsive: {
                details: false
            },
            ajax: this.pathUrlValue,
            sAjaxDataProp: "data",
            lengthMenu: [[100, 250, 500, 1000], [100, 250, 500, 1000]],
            pageLength: this.pageLengthValue,
            columns: columns,
            retrieve: true,
            order: [[1, 'asc']]
        });


        $(document).on('click', '.video-link', function(event) {
            $(document).magnificPopup({
                delegate: '.video-link',
                type: 'iframe',
                iframe: {
                    patterns: {
                        youtube: {
                            index: 'youtube.com',
                            id: 'v=',
                            src: '//www.youtube.com/embed/%id%?autoplay=1&iv_load_policy=3'
                        }
                    }
                }
            });
            event.preventDefault();
        });

        $(document).on('click', '.playlist-link', function(event) {
            $(document).magnificPopup({
                delegate: '.playlist-link',
                type: 'iframe',
                iframe: {
                    patterns: {
                        youtube: {
                            index: 'youtube.com',
                            id: 'v=',
                            src: '//www.youtube.com/embed/videoseries?list=%id%'
                        }
                    }
                }
            });
            event.preventDefault();
        });

        $(document).on('click', '.no-link', async function(event) {
            let id = $(event.target).closest('a').data('id');
            let auteur = $(event.target).closest('a').data('auteur');
            let titre = $(event.target).closest('a').data('titre');
            await $.ajax({
                url: '/fr/music/link/' + id,
                method: 'POST',
                data:  {auteur: auteur, titre: titre},
                processData: true,
                dataType	: 'json', // what type of data do we expect back from the server
                error       : function(data) {
                    console.log(data.responseText);
                },
                success     : function(data, textStatus, xhr) {
                    if (xhr.status === 200) {
                        $(event.target).closest('a').attr('href', 'https://www.youtube.com/watch?v=' + data.youtube_key);
                        $(event.target).closest('a').removeClass('no-link');
                        $(event.target).closest('a').addClass('video-link icon-youtube');
                        $('#formModal').modal('hide');
                    }
                }
            });
        });

        $(document).on('click', '.save-track', async function(event){
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
                $('a#track-youtube-' + result.id).attr('href', 'https://www.youtube.com/watch?v=' + result.youtube_key);
                if ($('a#track-youtube-' + result.id).hasClass('no-link')) {
                    $('a#track-youtube-' + result.id).removeClass('no-link');
                }
                if (!$('a#track-youtube-' + result.id).hasClass('video-link icon-youtube')) {
                    $('a#track-youtube-' + result.id).addClass('video-link icon-youtube');
                }
                $('#formModal').modal('hide');
            });
        });

        $(document).on('click', '#track-xchange', function() {
            let id = $('#track-xchange').data('id');
            let search = $('#track-xchange').data('search');
            $('input[name="search_api"]').val(search);
            $('input[name="id_track"]').val(id);
            $('form[name="form_api_search"]').submit();
        });
    }

    async openModal(event) {
        let query = '?';
        let url = '/fr/music/tracks_album';
        if ($(event.target).closest('a').data('artist')) {
            let artist = $(event.target).closest('a').data('artist') ?? 0;
            artist = artist.replaceAll('&', '%26');
            query += 'artist=' + artist;
        }
        if ($(event.target).closest('a').data('album')) {
            let album = $(event.target).closest('a').data('album') ?? 0;
            album = album.replaceAll('&', '%26');
            query += query != '?' ? '&' : '';
            query += 'album=' + album;
        } else {
            url = '/fr/music/artist';
        }
        const modal = new Modal('#formModal', {keyboard: false});
        modal.show();
        $(document).find('.modal-body').html(await $.ajax(url + query));
        event.preventDefault()
    }

    async openEditModal(event) {
        if ($(event.target).closest('a').data('id')) {
            let id = $(event.target).closest('a').data('id');
            const modal = new Modal('#formModal', {keyboard: false});
            modal.show();
            $(document).find('.modal-body').html(await $.ajax('/fr/music/track/edit/' + id));
        }
        event.preventDefault()
    }
}