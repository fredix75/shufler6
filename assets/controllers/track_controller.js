import { Controller } from '@hotwired/stimulus';
import DataTable from 'datatables.net';

/*
* The following line makes this controller "lazy": it won't be downloaded until needed
* See https://github.com/symfony/stimulus-bridge#lazy-controllers
*/
/* stimulusFetch: 'lazy' */
export default class extends Controller {
    connect() {
        new DataTable('#tracks', {
            responsive: {
                details: true
            },
            lengthMenu: [[100, 250, 500, 1000], [100, 250, 500, 1000]],
            order: [[6, 'asc'], [1, 'asc'], [4, 'asc'], [3, 'asc']]
        });
    }
}
