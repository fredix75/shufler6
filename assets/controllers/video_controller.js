import { Controller } from '@hotwired/stimulus';
import $ from 'jquery';
import 'magnific-popup'

export default class extends Controller {

    connect() {
        const YOUTUBE = 'youtube.com';
        const YOUTUBE_EMBED_PREFIX = '//www.youtube.com/embed/';
        const YOUTUBE_EMBED_SUFFIX = '?autoplay=1&iv_load_policy=3';
        const DAILYMOTION = 'dailymotion.com';
        const DAILYMOTION_EMBED = 'https://www.dailymotion.com/embed/video/';

        if ($('.video-link').length > 0) {
            $('.video-link').magnificPopup({
                type: 'iframe',
                iframe: {
                    patterns: {
                        dailymotion: {
                            index: DAILYMOTION,
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
                            src: DAILYMOTION_EMBED + '%id%'
                        },
                        youtube: {
                            index: YOUTUBE,
                            id: 'v=',
                            src: YOUTUBE_EMBED_PREFIX + '%id%' + YOUTUBE_EMBED_SUFFIX,
                        }
                    }
                }
            });
        }

        if ($('.lienPl').length > 0) {
            $('.lienPl').magnificPopup({
                items: {
                    index : YOUTUBE,
                    src: '<iframe style="margin:auto;" width="800" height="500" src="https://www.youtube.com/embed/videoseries?list=' + $('.lienPl').attr('href') + '" frameborder="0" allowfullscreen></iframe>',
                    type: 'inline'
                }
            });
        }
    }
}