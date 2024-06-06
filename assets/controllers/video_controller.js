import { Controller } from '@hotwired/stimulus';
import 'magnific-popup';
import DataTable from "datatables.net";
import $ from 'jquery';

export default class extends Controller {
    connect() {
        $('.video-link').magnificPopup({
            type: 'iframe',
            iframe: {
                patterns: {
                    dailymotion: {
                        index: 'dailymotion.com',
                        id: function (url) {
                            var m = url.match(/^.+dailymotion.com\/(embed\/video|hub)\/([^_]+)[^#]*(#video=([^_&]+))?/);
                            if (m !== null) {
                                console.log(m);
                                if (m[4] !== undefined) {
                                    return m[4];
                                }
                                return m[2];
                            }
                            return null;
                        },
                        src: 'https://www.dailymotion.com/embed/video/%id%'
                    },
                    youtube: {
                        index: 'youtube.com',
                        id: 'v=',
                        src: '//www.youtube.com/embed/%id%?autoplay=1&iv_load_policy=3',
                    }
                }
            }
        });

        $(document).magnificPopup({
            delegate: '.playlist-link',
            type: 'iframe',
            iframe: {
                patterns: {
                    youtube: {
                        index: 'youtube.com/',
                        id: 'v=',
                        src: '//www.youtube.com/embed/videoseries?list=%id%'
                    }
                }
            }
        });

        new DataTable('#videos', {
            responsive: {
                details: true
            },
            lengthMenu: [[100, 250, 500, 1000], [100, 250, 500, 1000]],
            order: [[1, 'asc'], [2, 'asc']]
        });
    }

    edit(event) {
        let url = $(event.target).closest('a').data('url');
        $('input[name="videokey"]').val(url);
        $('form[name="form_video_edit"]').submit();
    }

    editTrack(event) {
        let url = $(event.target).closest('a').data('url');
        $('input[name="trackkey"]').val(url);
        $('form[name="form_track_edit"]').submit();
    }

    editAlbum(event) {
        let url = $(event.target).closest('a').data('url');
        $('input[name="albumkey"]').val(url);
        $('form[name="form_album_edit"]').submit();
    }

    deleteKey(event) {
        $('input[name="id_video"]').val(0);
        $('.link_edit').find('i').removeClass('bi-arrow-left-right');
        $('.link_edit').find('i').addClass('bi-patch-plus');
        $('form[name="form_video_edit"]').attr('action', '/fr/video/edit/0');
        $(event.target).closest('a').hide();
        event.preventDefault();
    }
}