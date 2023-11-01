import { Controller } from '@hotwired/stimulus';
import $ from "jquery";

/*
* The following line makes this controller "lazy": it won't be downloaded until needed
* See https://github.com/symfony/stimulus-bridge#lazy-controllers
*/
/* stimulusFetch: 'lazy' */
export default class extends Controller {

    async displayContent(event) {
        $('.block-content-album').remove();
        let artist = $(event.target).closest('a').data('artist');
        let album = $(event.target).closest('a').data('album');
        let url = '/fr/music/tracks_album';
        let query = '?artist=' + artist + '&album=' + album;
        let content = await $.ajax(url + query);
        $(content).insertAfter($(event.target).closest('.album'));
    }

}
