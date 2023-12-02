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

import 'tom-select'
import TomSelect from "tom-select"
document.querySelectorAll('.select2').forEach((el)=>{
    let settings = {};
    new TomSelect(el,settings);
});


import { Modal, Tooltip, Toast, Popover } from 'bootstrap';
import { Application } from '@hotwired/stimulus'
import Autocomplete from 'stimulus-autocomplete'
import 'datatables.net-dt/css/jquery.dataTables.css';

const application = Application.start()
application.register('autocomplete', Autocomplete)