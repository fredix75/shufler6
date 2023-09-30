/*
 * Welcome to your app's main JavaScript file!
 *
 * We recommend including the built version of this JavaScript file
 * (and its CSS file) in your base layout (base.html.twig).
 */

// any CSS you import will output into a single css file (app.scss in this case)
import './styles/app.scss';

// start the Stimulus application
import './bootstrap';

import $ from 'jquery';

require('select2')
$('.select2').select2({
    ajax: {
        url: $('.select2').data('remote'),
        dataType: 'json',
        processResults: function (data) {
            return {
                results: data
            };
        }
        // Additional AJAX parameters go here; see the end of this chapter for the full code of this example
    }
});


import { Modal, Tooltip, Toast, Popover } from 'bootstrap';
import { Application } from '@hotwired/stimulus'
import Autocomplete from 'stimulus-autocomplete'

const application = Application.start()
application.register('autocomplete', Autocomplete)