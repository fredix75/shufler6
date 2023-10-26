import { Controller } from '@hotwired/stimulus';
import $ from 'jquery';
import TomSelect from "tom-select";

export default class extends Controller {

    toggleSlide(event) {
        $('#slider').slideToggle();
    }

}