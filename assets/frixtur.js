import './stimulus_bootstrap.js';
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

// Mémo pour faire une variable globale après import de la $
// window.$ = window.jQuery = $
import './styles/frixtur/material-photo-galery.css';
import './styles/frixtur/frixtur.scss';
import './styles/frixtur/home.scss';
import './styles/frixtur/artist.scss';
import './styles/frixtur/artists_list.scss';
import './styles/pagination.scss';
