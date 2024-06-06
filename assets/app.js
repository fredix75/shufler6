import './bootstrap.js';
/*
 * Welcome to your app's main JavaScript file!
 *
 * We recommend including the built version of this JavaScript file
 * (and its CSS file) in your base layout (base.html.twig).
 */

// any CSS you import will output into a single css file (app.scss in this case)
//import './vendor/bootstrap/bootstrap.index.js';

import './vendor/bootstrap/dist/css/bootstrap.min.css';
import './vendor/bootstrap-icons/font/bootstrap-icons.css';
import './styles/app.scss';

// Mémo pour faire une variable globale après import de la $
// window.$ = window.jQuery = $

import TomSelect from "tom-select"
document.querySelectorAll('.select2').forEach((el)=>{
    let settings = {};
    new TomSelect(el,settings);
});

import './vendor/tom-select/dist/css/tom-select.css';
import './vendor/magnific-popup/dist/magnific-popup.min.css';

import './vendor/datatables.net-dt/css/dataTables.dataTables.min.css';