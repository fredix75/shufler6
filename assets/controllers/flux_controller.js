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

    launch(event) {
        let url = $(event.target).closest('a').attr('href');
        let type = $(event.target).closest('a').data('type');
        let text, audio;
        if ('radio' === type) {
            audio = '<iframe src="' + url + '" style="width:100%;height:100px;"></iframe>';
            $('#slider').show();
        } else {
            let title = $(event.target).closest('.accordion-item').find('.accordion-button');
            let podcast = title.closest('.flux-widget').data('title');
            audio = '<audio controls="controls" autoplay class="col-12 col-xs-12 col-sm-12 col-md-12"><source src="' + url +'" type="' + type + '">Votre navigateur ne supporte pas l\'élément <code>audio</code>.</audio>';
            text = '<div class="pres">' + podcast + '<br />' + title.html() + '</div>';
        }
        $('#audio-stick').html(audio).append(text);
        event.preventDefault();
    }

    prev(event) {
        let url = $(event.target).closest('.flux').find('.accordion').data('url');
        let id = $(event.target).closest('.flux').find('.accordion').attr('id');
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
        let url = $(event.target).closest('.flux').find('.accordion').data('url');
        let id = $(event.target).closest('.flux').find('.accordion').attr('id');
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

    download(event) {
        let url = $(event.target).closest('a').data('url');
        $(event.target).closest('a').html('<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span>');
        $.get('/fr/download/resource',
            {url: url},
            function(data){
                $(event.target).closest('a').html('<i class="bi bi-download"></i>');
                $(event.target).closest('a').attr('href', '/uploads/'+data);
                $(event.target).closest('a').css('color', 'green');
                $(event.target).closest('a').removeAttr('data-action');
            }
        );
        event.preventDefault();
    }
}
function getData(url, id, page) {
    let type = $('#' + id).data('type');
    getLoading(id);
    $.get('/fr/flux/handle',
        {id: id, url: url, page: page, type: type},
        function(data){
            bindContent(id, data);
        }
    );
}

function bindContent(id, data) {
    let div = $('#' + id);
    div.html(data);
    $(".accordion-body").find('img').css('width', '100%');
}

function getLoading(id) {
    $('#slider').show();
    let div = $('#' + id);
    div.html('<div class="spinner-border" role="status">' +
        '        <span class="visually-hidden">Loading...</span>' +
        '    </div>'
    );
}