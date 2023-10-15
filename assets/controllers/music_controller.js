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
    }

    async openModal(event) {
        let query = '?';
        let url = '/fr/music/tracks_album';
        if ($(event.target).closest('a').data('artist')) {
            let artist = $(event.target).closest('a').data('artist') ?? 0;
            query += 'artist=' + artist;
        }
        if ($(event.target).closest('a').data('album')) {
            let album = $(event.target).closest('a').data('album') ?? 0;
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
}