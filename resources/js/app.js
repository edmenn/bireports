import './bootstrap';
import '@tabler/core/dist/css/tabler.min.css';
import '@fortawesome/fontawesome-free/css/all.min.css';
import * as bootstrap from 'bootstrap';
import $ from 'jquery';

import Alpine from 'alpinejs';

window.Alpine = Alpine;
window.bootstrap = bootstrap;
window.$ = window.jQuery = $;

$.fn.modal = function (action) {
    this.each((_, element) => {
        const modal = bootstrap.Modal.getOrCreateInstance(element);
        if (action === 'hide') modal.hide();
        else if (action === 'toggle') modal.toggle();
        else modal.show();
    });
    return this;
};

$.fn.tooltip = function () {
    this.each((_, element) => bootstrap.Tooltip.getOrCreateInstance(element));
    return this;
};

document.addEventListener('DOMContentLoaded', () => {
    document.querySelectorAll('[data-toggle]').forEach(element => {
        element.dataset.bsToggle ||= element.dataset.toggle;
    });
    document.querySelectorAll('[data-target]').forEach(element => {
        element.dataset.bsTarget ||= element.dataset.target;
    });
    document.querySelectorAll('[data-dismiss]').forEach(element => {
        element.dataset.bsDismiss ||= element.dataset.dismiss;
    });
    document.querySelectorAll('[data-bs-toggle="tooltip"], [data-toggle="tooltip"]').forEach(element => {
        bootstrap.Tooltip.getOrCreateInstance(element);
    });
});

Alpine.start();
