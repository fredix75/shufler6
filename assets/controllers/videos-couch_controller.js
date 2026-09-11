import $ from 'jquery';

/**
 * @typedef options
 * @see https://developers.google.com/youtube/iframe_api_reference#Loading_a_Video_Player
 * @param {Number} width
 * @param {Number} height
 * @param {String} videoId
 * @param {Object} playerVars
 * @param {Object} events
 */



/**
 * A factory function used to produce an instance of YT.Player and queue function calls and proxy events of the resulting object.
 *
 * the DOM element or the id of the HTML element where the API will insert an <iframe>.
 * @param {YouTubePlayer~options} options See `options` (Ignored when using an existing YT.Player instance).
 * @param {boolean} strictState A flag designating whether or not to wait for
 * an acceptable state when calling supported functions. Default: `false`.
 * See `FunctionStateMap.js` for supported functions and acceptable states.
 * @returns {Object}
 */
import YouTubePlayer from 'youtube-player';
import PieceController from "./piece_controller.js";

export default class extends PieceController {
    static values = {
        videos: Array
    };

    connect() {
        this.player = YouTubePlayer('player', {
            width: '100%',
            height: '500px'
        });

        this.player.on('ready', e => {
            e.target.loadPlaylist(this.videosValue);
        });

        this.player.on('error', e => {
            this.player.nextVideo();
        });

        this.player.on('stateChange', e => {
            if (e.data === 1) {
                let key = e.target.getVideoData().video_id;
                if (key) {
                    $('#title')
                        .find('#' + key).css('display', 'flex')
                        .siblings().css('display', 'none');
                }
            }
        });

        if ($('[name="categorie"]').length > 0) {
            if ('2' !== $('[name="categorie"]').val()) {
                $('#genre').hide();
            }

            $(document).on('change', '#categorie', function () {
                if ('2' !== $('[name="categorie"]').val()) {
                    $('[name="genre"]').val(null);
                    $('#genre').fadeOut('slow');
                } else {
                    $('#genre').fadeIn('slow');
                }
            });
        }
    }

    prev(event) {
        if (this.player) {
            this.player.previousVideo();
        }
        event.preventDefault();
    }

    next(event) {
        if (this.player) {
            this.player.nextVideo();
        }
        event.preventDefault();
    }

}
