import { Controller } from '@hotwired/stimulus';
import $ from 'jquery';

export default class extends Controller {
    static values = {
        'periods': Array
    };

    connect() {
        if ('2' !== $('[name="video[categorie]"]').val()) {
            $('#genre').hide();
        }
    }

    categorieChange() {
        if ('2' !== $('[name="video[categorie]"]').val()) {
            $('[name="video[genre]"]').val(null);
            $('#genre').fadeOut('slow');
        } else {
            $('#genre').fadeIn('slow');
        }
    }

    selectPeriod() {
        let year = $('[name="video[annee]"]').val();
        year = parseInt(year);
        if (year < 1900) {
            return;
        }

        let periode = null;
        $.each(this.periodsValue, function(i, v) {
            if (v === '<1939') {
                if (year < 1940) {
                    periode = v;
                }
                return;
            }
            let r1 = v.substring(0,4);
            let r2 = v.substring(5,9);
            if (year >= r1 && year <= r2) {
                periode = v;
                return;
            }
        });

        if (!periode) {
            return;
        }

        $('[name="video[periode]"]').val(periode);
    }

    completeInfos() {
        let lien = $('[name="video[lien]"]').val();
        let match, videoKey, plateforme;
        if (match = lien.match(/(?:https?:\/{2})?(?:w{3}\.)?youtu(?:be)?\.(?:com|be)(?:\/watch\?v=|\/embed\/)([^\s&]+)/)) {
            plateforme = 'youtube';
            videoKey = match[1];
        } else if (lien.indexOf('vimeo') > 0) {
            plateforme = 'vimeo';
            let part = lien.split('/');
            if (part != 'undefined') {
                videoKey = part[part.length - 1];
            }
        }

        //@todo Trigger completion année -> selectPeriod()
        $.get('/video/getVideoInfos/' + plateforme + '/' + videoKey, function (result) {
            if(result && result.title) {
                if ("" === $('[name="video[auteur]"]').val()) {
                    $('[name="video[auteur]"]').val(result.title);
                }
                if ("" === $('[name="video[titre]"]').val()) {
                    $('[name="video[titre]"]').val(result.title);
                }

                if ("" === $('[name="video[annee]"]').val() && result.upload_date) {
                    $('[name="video[annee]"]').val((new Date(result.upload_date)).getFullYear()).change();
                }
            }
        }, 'json');
    }
}