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

    prev(event) {
        let url = $(event.target).closest('.news').find('.accordion').data('url');
        let id = $(event.target).closest('.news').find('.accordion').attr('id');
        let page = parseInt($(event.target).closest('a.left').data('page'));
        getData(url, id, page);
        $(event.target).closest('.pod-nav').find('a.right').data('page', page + 1);
        if (page > 1) {
            $(event.target).closest('a.left').data('page', page - 1);
        } else {
            $(event.target).closest('a.left').addClass('disabled');
        }
        event.preventDefault();
    }

    next(event) {
        let url = $(event.target).closest('.news').find('.accordion').data('url');
        let id = $(event.target).closest('.news').find('.accordion').attr('id');
        let page = parseInt($(event.target).closest('a.right').data('page'));
        getData(url, id, page);
        $(event.target).closest('a.right').data('page', page + 1);
        if (page > 1) {
            $(event.target).closest('.pod-nav').find('a.left').data('page', page - 1);
            $(event.target).closest('.pod-nav').find('a.left').removeClass('disabled');
        } else {
            if (!$(event.target).closest('.pod-nav').find('a.left').hasClass('disabled')) {
                $(event.target).closest('.pod-nav').find('a.left').data('page', page - 1);
                $(event.target).closest('.pod-nav').find('a.left').addClass('disabled');
            }
        }
        event.preventDefault();
    }
}
function getData(url, id, page) {
    getLoading(id);
    $.get('/flux/handle',
        {url: url, page: page},
        function(data){
            bindContent(id, data);
        }
    );
}

function bindContent(id, data) {
    let content = JSON.parse(data);
    let div = $('#' + id);
    div.html('');
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
        structure += '<a href="' + value.link + '" class="btn btn-info" target="_blank">Lire l\'article</a>';
        if (typeof value.description != 'undefined') {
            structure += '<p>' + value.description + '</p>';
        }
        value.media = (value.enclosure !== undefined && value.enclosure["@attributes"].url != null) ? value.enclosure["@attributes"].url : (value.media ?? null);

        if (value.media != null) {
            structure += '<div class="row">';
            structure += '<img src="' + value.media + '"  style="width:100%;" />';
            structure += '</div><br />';
        }
        structure += '</div></div></div>';
        div.append(structure);
    });

    $(".accordion-body").find('img').css('width', '100%');
}

function getLoading(id) {
    let div = $('#' + id);
    div.html('<div class="spinner-border" role="status">' +
        '        <span class="visually-hidden">Loading...</span>' +
        '    </div>'
    );
}