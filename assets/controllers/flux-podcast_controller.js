import { Controller } from '@hotwired/stimulus';
import $ from 'jquery';

export default class extends Controller {

    connect() {
        $.each($('#accordionPodcast').children('.accordion-item').children('.accordion-collapse'), function( index, value ) {
            let id = $(value).attr('id');
            let url = $(value).data('url');
            getData(url, id,1);
        });
    }

    launch(event) {
        let url = $(event.target).closest('a').attr('href');
        let type = $(event.target).closest('a').data('type');
        let id = $(event.target).closest('.audio').attr('id');
        bindFile(id, url, type);
    }

    prev(event) {
        let url = $(event.target).closest('.accordion-collapse').data('url');
        let id = $(event.target).closest('.accordion-collapse').attr('id');
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
        let url = $(event.target).closest('.accordion-collapse').data('url');
        let id = $(event.target).closest('.accordion-collapse').attr('id');
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
    $.get('/flux/podcasts',
        {url: url, page: page},
        function(data){
            bindContent(id, data);
        }
    );
}

function bindContent(id, data) {
    let content = JSON.parse(data);
    let div = $('#' + id).children('.accordion-body');
    let structure = '<div class="accordion" id="subAccordion-' + id + '">';
    div.html(structure);
    $.each(content, function(index, value) {
        if (null === value) {
            return;
        }
        let date = new Date(value.pubDate).toLocaleDateString();
        let substructure = '<div class="accordion-item">\n' +
            '                        <h2 class="accordion-header" id="subheading-' + id + '-' + index + '">\n' +
            '                            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapse-' + id + '-' + index + '" aria-expanded="true" aria-controls="collapse-' + id + '-' + index + '"><span class="badge bg-secondary text-end" style="font-size:60%;">' + date + '</span>&nbsp;&nbsp;' + value.title + '</button>\n' +
            '                        </h2>' +
            '                        <div id="collapse-' + id + '-' + index + '" class="accordion-collapse collapse" data-bs-parent="#subAccordion-' + id + '">\n' +
            '                            <div class="accordion-body subAccordion">\n';
        if (value.enclosure !== undefined && value.enclosure["@attributes"].url != null) {
            substructure += '<div id="sound-' + id + '-' + index + '" class="audio text-center"><a href="' + value.enclosure["@attributes"].url + '" type="button" class="btn btn-secondary" data-type="' + value.enclosure["@attributes"].type + '" onclick="return false;" data-action="flux-podcast#launch" style="width:100%;"><i class="bi bi-play-circle-fill"></i></a></div><br />';
        }
        if (typeof value.description != 'undefined') {
            substructure += '<p>' + value.description + '</p>';
        }
        substructure += '</div></div></div>';
        div.append(substructure);
    });
    div.append('</div>');
}

function bindFile(id, url, type) {
    let audio = '<audio controls="controls" autoplay class="col-xs-12 col-sm-12 col-md-12"><source src="' + url +'" type="' + type + '">Votre navigateur ne supporte pas l\'élément <code>audio</code>.</audio>';
    $('#' + id).html(audio);
}