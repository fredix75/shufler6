import { Controller } from '@hotwired/stimulus';
import $ from 'jquery';

const news = [];
const radios = [];
const liens = [];

export default class extends Controller {

    static values = {
        'news': Object,
        'radios': Object,
        'liens': Object,
    };

    connect() {
        news.push('<option value="">Choose a Category</option>');
        $.each(this.newsValue, function(key, value) {
            news.push('<option value="' + key + '">' + value + '</option>');
        });

        radios.push('<option value="">Choose a Category</option>');
        $.each(this.radiosValue, function(key, value) {
            radios.push('<option value="' + key + '">' + value + '</option>');
        });

        liens.push('<option value="">Choose a Category</option>');
        $.each(this.liensValue, function(key, value) {
            liens.push('<option value="' + key + '">' + value + '</option>');
        });

        let type = $('[name="flux[type]"]').val();
        if ('1' === type) {
            $('#channel').hide();
            this.handleMoodSelect(news);
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
            this.handleMoodSelect(liens);
        } else if ('5' === type) {
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
            $('[name="flux[mood]"]').empty().append(news);
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
            $('[name="flux[mood]"]').empty().append(liens);
            $('#mood').fadeIn('slow');
        } else if ('5' === $('[name="flux[type]"]').val()) {
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