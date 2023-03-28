import { Controller } from '@hotwired/stimulus';
import $ from 'jquery';

const rss = [];
const radios = [];
const links = [];

export default class extends Controller {

    static values = {
        'rss': Object,
        'radios': Object,
        'links': Object,
    };

    connect() {
        rss.push('<option value="">Choose a Category</option>');
        $.each(this.rssValue, function(key, value) {
            rss.push('<option value="' + key + '">' + value + '</option>');
        });

        radios.push('<option value="">Choose a Category</option>');
        $.each(this.radiosValue, function(key, value) {
            radios.push('<option value="' + key + '">' + value + '</option>');
        });

        links.push('<option value="">Choose a Category</option>');
        $.each(this.linksValue, function(key, value) {
            links.push('<option value="' + key + '">' + value + '</option>');
        });

        let type = $('[name="flux[type]"]').val();
        if ('1' === type) {
            $('#channel').hide();
            this.handleMoodSelect(rss);
        } else if ('2' === type ) {
            $('#file').hide();
            $('#mood').hide();
        } else if ('3' === type) {
            $('#file').hide();
            $('#channel').hide();
            this.handleMoodSelect(radios);
        } else if ('4' === type) {
            $('#file').hide();
            $('#channel').hide();
            this.handleMoodSelect(links);
        } else if ('6' === type) {
            $('#file').hide();
            $('#channel').hide();
            $('#mood').hide();
        }
    }

    typeChange() {
        if ('1' === $('[name="flux[type]"]').val()) {
            $('#file').fadeIn('slow');
            $('[name="flux[channel]"]').val(null);
            $('#channel').fadeOut('slow');
            $('[name="flux[mood]"]').empty().append(rss);
            $('#mood').fadeIn('slow');
        } else if ('2' === $('[name="flux[type]"]').val()) {
            $('[name="flux[file]"]').val(null);
            $('#file').fadeOut('slow');
            $('#channel').fadeIn('slow');
            $('[name="flux[mood]"]').val(null);
            $('#mood').fadeOut('slow');
        } else if ('3' === $('[name="flux[type]"]').val()) {
            $('[name="flux[file]"]').val(null);
            $('#file').fadeOut('slow');
            $('[name="flux[channel]"]').val(null);
            $('#channel').fadeOut('slow');
            $('[name="flux[mood]"]').empty().append(radios);
            $('#mood').fadeIn('slow');
        } else if ('4' === $('[name="flux[type]"]').val()) {
            $('[name="flux[file]"]').val(null);
            $('#file').fadeOut('slow');
            $('[name="flux[channel]"]').val(null);
            $('#channel').fadeOut('slow');
            $('[name="flux[mood]"]').empty().append(links);
            $('#mood').fadeIn('slow');
        } else if ('6' === $('[name="flux[type]"]').val()) {
            $('[name="flux[file]"]').val(null);
            $('#file').fadeOut('slow');
            $('[name="flux[channel]"]').val(null);
            $('#channel').fadeOut('slow');
            $('[name="flux[mood]"]').val(null);
            $('#mood').fadeOut('slow');
        }
    }

    handleMoodSelect(type) {
        let val = $('[name="flux[mood]"]').val();
        $('[name="flux[mood]"]').empty().append(type);
        $('[name="flux[mood]"]').val(val);
    }
}