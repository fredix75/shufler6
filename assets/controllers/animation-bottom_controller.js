import { Controller } from '@hotwired/stimulus';
import $ from 'jquery';

export default class extends Controller {

    connect() {
        let rootclass = 'bi bi-dice-';
        setInterval(function() {
            $('#animation-bottom').children('i').each(function () {
                let sclass = $(this).attr('class');
                $(this).removeClass(sclass);
                let rdmNumer =  Math.floor(Math.random() * (6 - 1 + 1) + 1);
                let newClass = rootclass + rdmNumer;
                $(this).addClass(newClass);
            });
        }, 300);
    }
}