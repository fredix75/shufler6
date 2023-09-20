import { Controller } from '@hotwired/stimulus';
import $ from 'jquery';

export default class extends Controller {

    connect() {
        $.each($('.accordion'), function (index, value) {
            let id = $(value).attr('id');
            let channelId = $(value).data('channel');
            getData(channelId, id, 1);
        });
    }

    prev(event) {
        let id = $(event.target).closest('.channel').find('.accordion').attr('id');
        let page = parseInt($(event.target).closest('a.left').data('page'));
        handlePagination(id, page);
        $(event.target).closest('.pod-nav').find('a.right').data('page', page + 1);
        if (page > 1) {
            $(event.target).closest('a.left').data('page', page - 1);
        } else {
            $(event.target).closest('a.left').addClass('disabled');
        }
        event.preventDefault();
    }

    next(event) {
        let id = $(event.target).closest('.channel').find('.accordion').attr('id');
        let page = parseInt($(event.target).closest('a.right').data('page'));
        handlePagination(id, page);
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

    editChannel(event) {
        let key = $(event.target).closest('.list-group-item').find('.lienPl').data('id');
        let image = $(event.target).closest('.channel').find('.channel-picture img').attr('src');
        $('input[name="channelkey"]').val(key);
        $('input[name="channelpicture"]').val(image);
        $('form[name="form_edit"]').submit();
        event.preventDefault();
    }
}

function getData(channelId, id) {
    $.get('/fr/other/channel/handle',
        {id: channelId},
        function(data){
            bindContent(id, data);
        }
    );
}

function bindContent(id, data) {
    let div = $('#' + id);
    div.html(data);
    $.each(div.find('li'), function(index, value) {
        if ($(value).data('page') != 1) {
            $(value).addClass('d-none');
        }
    });
}

function handlePagination(id, page) {
    $.each($('#' + id).find('li'), function(index, value) {
        if ($(value).data('page') != page && !$(value).hasClass('d-none')) {
            $(value).addClass('d-none');
        } else if ($(value).data('page') == page && $(value).hasClass('d-none')) {
            $(value).removeClass('d-none');
        }
    });
}