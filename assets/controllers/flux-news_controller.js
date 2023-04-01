import { Controller } from '@hotwired/stimulus';
import $ from 'jquery';

export default class extends Controller {

    connect() {
        $.each($('.accordion'), function (index, value) {
            let id = $(value).attr('id');
            let url = $(value).data('url');
            getData(url, id, 1);
        });
    }
}
function getData(url, id, page) {
    $.get('/flux/handle',
        {url: url, page: page},
        function(data){
            bindContent(id, data);
        }
    );
}

function bindContent(id, data) {
    let content = JSON.parse(data);
    console.log(data);
    let div = $('#' + id);
    $.each(content, function(index, value) {
        if (null === value) {
            return;
        }
        let date = new Date(value.pubDate).toLocaleDateString();
        let structure = '<div class="accordion-item">\n' +
            '                        <h2 class="accordion-header" id="heading-' + id + '-' + index + '">\n' +
            '                            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapse-' + id + '-' + index + '" aria-expanded="true" aria-controls="collapse-' + id + '-' + index + '"><span class="badge bg-secondary text-end" style="font-size:60%;">' + date + '</span>&nbsp;&nbsp;' + value.title + '</button>\n' +
            '                        </h2>' +
            '                        <div id="collapse-' + id + '-' + index + '" class="accordion-collapse collapse" data-bs-parent="#' + id + '">\n' +
            '                            <div class="accordion-body accordion">\n';
        if (typeof value.description != 'undefined') {
            structure += '<p>' + value.description + '</p>';
        }
        structure += '</div></div></div>';
        div.append(structure);
    });
}