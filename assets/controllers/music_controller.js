import { Controller } from '@hotwired/stimulus';
import $ from 'jquery';
import 'magnific-popup';
import DataTable from 'datatables.net';

export default class extends Controller {

    static values = {
        columns: Array,
        pathUrl: String,
        pageLength: Number
    }

    connect() {
        let columns = [];
        this.columnsValue.forEach(function(item){
            columns.push({data : item});
        });

        let table = new DataTable('#container-datas', {
            processing: true,
            serverSide: true,
            select: true,
            responsive: {
                details: false
            },
            ajax: this.pathUrlValue,
            sAjaxDataProp: "data",
            lengthMenu: [[100, 250, 500, 1000], [100, 250, 500, 1000]],
            pageLength: this.pageLengthValue,
            columns: columns,
            retrieve: true
        });


    }

}