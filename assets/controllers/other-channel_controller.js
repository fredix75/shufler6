import { Controller } from '@hotwired/stimulus';
import $ from 'jquery';

export default class extends Controller {

    prev(event) {
        let id = $(event.target).closest('.card-body').find('.accordion').attr('id');
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
        let id = $(event.target).closest('.card-body').find('.accordion').attr('id');
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
        let key = $(event.target).closest('.list-group-item').find('.playlist-link').attr('href');
        let image = $(event.target).closest('.card').find('.card-header img').attr('src');
        $('input[name="channelkey"]').val(key);
        $('input[name="channelpicture"]').val(image);
        $('form[name="form_video_edit"]').submit();
        event.preventDefault();
    }
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
