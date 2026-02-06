import { Controller } from '@hotwired/stimulus';
import $ from 'jquery';

export default class extends Controller {

    static values = {
        news: Object,
        radios: Object,
        liens: Object,
    };

    connect() {
        let type = $('select[name="flux_form[type]"]').val();

        if ('1' === type) {
            $('#channel').hide();
            $('#description').hide();
            this.handleMoodSelect(this.newsValue);
        } else if ('2' === type ) {
            $('#file').hide();
            $('#mood').hide();
            $('#description').hide();
        } else if ('3' === type) {
            $('#file').hide();
            $('#channel').hide();
            $('#description').hide();
            this.handleMoodSelect(this.radiosValue);
        } else if ('4' === type) {
            $('#file').hide();
            $('#channel').hide();
            this.handleMoodSelect(this.liensValue);
        } else {
            $('#file').hide();
            $('#channel').hide();
            $('#mood').hide();
            $('#description').hide();
        }
    }

    typeChange() {
        if ('1' === $('[name="flux_form[type]"]').val()) {
            $('#file').fadeIn('slow');
            $('[name="flux_form[channel]"]').val(null);
            $('#channel').fadeOut('slow');
            $('#description').fadeOut('slow');
            this.handleMoodSelect(this.newsValue, true);
            $('#mood').fadeIn('slow');
        } else if ('2' === $('[name="flux_form[type]"]').val()) {
            $('[name="flux_form[file]"]').val(null);
            $('#file').fadeOut('slow');
            $('#channel').fadeIn('slow');
            $('#description').fadeOut('slow');
            $('[name="flux_form[mood]"]').val(null);
            $('#mood').fadeOut('slow');
        } else if ('3' === $('[name="flux_form[type]"]').val()) {
            $('[name="flux_form[file]"]').val(null);
            $('#file').fadeOut('slow');
            $('[name="flux_form[channel]"]').val(null);
            $('#channel').fadeOut('slow');
            $('#description').fadeOut('slow');
            this.handleMoodSelect(this.radiosValue, true);
            $('#mood').fadeIn('slow');
        } else if ('4' === $('[name="flux_form[type]"]').val()) {
            $('[name="flux_form[file]"]').val(null);
            $('#file').fadeOut('slow');
            $('[name="flux_form[channel]"]').val(null);
            $('#channel').fadeOut('slow');
            this.handleMoodSelect(this.liensValue, true);
            $('#mood').fadeIn('slow');
            $('#description').fadeIn('slow');
        } else if ('5' === $('[name="flux_form[type]"]').val()) {
            $('[name="flux_form[file]"]').val(null);
            $('#file').fadeOut('slow');
            $('[name="flux_form[channel]"]').val(null);
            $('#channel').fadeOut('slow');
            $('[name="flux_form[mood]"]').val(null);
            $('#mood').fadeOut('slow');
            $('#description').fadeOut('slow');
        }
    }

    handleMoodSelect(optionsByType, clear = false) {
        let select = document.getElementById('flux_form_mood');
        let control = select.tomselect;
        if (clear) {
            control.clear();
        }
        control.clearOptions();
        let tab = [];
        Object.entries(optionsByType).forEach(([key, value]) => {
            tab.push({'value': key, text: value});
        });
        control.addOptions(Object.entries(tab));
    }
}
