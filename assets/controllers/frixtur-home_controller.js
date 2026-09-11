import { Controller } from '@hotwired/stimulus';
import $ from 'jquery';

/*
* The following line makes this controller "lazy": it won't be downloaded until needed
* See https://symfony.com/bundles/StimulusBundle/current/index.html#lazy-stimulus-controllers
*/

/* stimulusFetch: 'lazy' */
export default class extends Controller {

    connect() {
// Gallery image hover
        $( ".img-wrapper" ).hover(
            function() {
                $(this).find(".img-overlay").animate({opacity: 1}, 600);
            }, function() {
                $(this).find(".img-overlay").animate({opacity: 0}, 600);
            }
        );
    }
}
