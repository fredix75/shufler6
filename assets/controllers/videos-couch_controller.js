import { Controller } from '@hotwired/stimulus';
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
 * @typedef YT.Player
 * @see https://developers.google.com/youtube/iframe_api_reference
 * */

/**
 * A factory function used to produce an instance of YT.Player and queue function calls and proxy events of the resulting object.
 *
 * @param {YT.Player|HTMLElement|String} elementId Either An existing YT.Player instance,
 * the DOM element or the id of the HTML element where the API will insert an <iframe>.
 * @param {YouTubePlayer~options} options See `options` (Ignored when using an existing YT.Player instance).
 * @param {boolean} strictState A flag designating whether or not to wait for
 * an acceptable state when calling supported functions. Default: `false`.
 * See `FunctionStateMap.js` for supported functions and acceptable states.
 * @returns {Object}
 */
import YouTubePlayer from 'youtube-player';

export default class extends Controller {
    static values = {
        videos: Array
    };
    connect() {
        let player;

        player = YouTubePlayer('player', {
            height: '600px',
            width: '100%'
        });

        player.on('ready', e => {
            e.target.loadPlaylist(this.videosValue);
        })
    }


}