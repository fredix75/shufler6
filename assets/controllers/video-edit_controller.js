import { Controller } from '@hotwired/stimulus';
import $ from 'jquery';

export default class extends Controller {
    static values = {
        'periods': Array
    };

    connect() {
        if ('2' !== $('[name="video_form[categorie]"]').val()) {
            $('#genre').hide();
        }
        this.completeInfos();
    }

    categorieChange() {
        if ('2' !== $('[name="video_form[categorie]"]').val()) {
            $('[name="video_form[genre]"]').val(null);
            $('#genre').fadeOut('slow');
        } else {
            $('#genre').fadeIn('slow');
        }
    }

    selectPeriod() {
        let year = $('[name="video_form[annee]"]').val();
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

        $('[name="video_form[periode]"]').val(periode);
    }

    completeInfos() {
        let lien = $('[name="video_form[lien]"]').val();
        let match, videoKey, plateforme;
        if (match = lien.match(/(?:https?:\/{2})?(?:w{3}\.)?youtu(?:be)?\.(?:com|be)\/(?:watch\?v=|\/embed\/)?([^\s&]+)/)) {
            plateforme = 'youtube';
            let part = match[1].split('/');
            videoKey = part[part.length - 1];
        } else if (lien.indexOf('vimeo') > 0) {
            plateforme = 'vimeo';
            let part = lien.split('/');
            if (part !== 'undefined') {
                videoKey = part[part.length - 1];
            }
        }
        if (plateforme && videoKey) {
            //@todo Trigger completion année -> selectPeriod()
            $.get('/fr/video/getVideoInfos/' + plateforme + '/' + videoKey, function (result) {
                if(result && result.title) {
                    if ("" === $('[name="video_form[auteur]"]').val()) {
                        $('[name="video_form[auteur]"]').val(result.title);
                    }
                    if ("" === $('[name="video_form[titre]"]').val()) {
                        $('[name="video_form[titre]"]').val(result.title);
                    }

                    if ("" === $('[name="video_form[annee]"]').val() && result.upload_date) {
                        $('[name="video_form[annee]"]').val((new Date(result.upload_date)).getFullYear()).change();
                    }
                }
            }, 'json');
        }
    }

    xchange(event) {
        let search = $(event.target).closest('a').data('search');
        let id = $(event.target).closest('a').data('id');
        $('input[name="search_api"]').val(search);
        $('input[name="id_video"]').val(id);
        $('form[name="form_api_search"]').submit();
    }
}