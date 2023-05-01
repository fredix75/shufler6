import { Controller } from '@hotwired/stimulus';
import $ from 'jquery';
import 'magnific-popup'

export default class extends Controller {

    connect() {
        if ($('.video-link').length > 0) {
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
                                    if(m[4] !== undefined) {
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
        }


    }

    popupPlaylist(event) {
        $(document).ready(function() {
            let key = $(event.target).closest('a').data('id');
            $(event.target).closest('a').magnificPopup({
                items: {
                    index: 'youtube.com',
                    src: '<iframe style="margin:auto;" width="800" height="500" src="https://www.youtube.com/embed/videoseries?list=' + key + '" frameborder="0" allowfullscreen></iframe>',
                    type: 'inline'
                }
            });
        });
        event.preventDefault();
    }

    edit(event) {
        let url = $(event.target).closest('a').data('url');
        $('input[name="videokey"]').val(url);
        $('form[name="form_edit"]').submit();
    }

    deleteKey(event) {
        $('input[name="id_video"]').val(0);
        $('.link_edit').find('i').removeClass('bi-arrow-left-right');
        $('.link_edit').find('i').addClass('bi-patch-plus');
        $('form[name="form_edit"]').attr('action', '/video/edit/0');
        $(event.target).closest('a').hide();
        event.preventDefault();
    }
}