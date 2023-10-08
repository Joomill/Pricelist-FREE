/*
 *  package: Joomla Price List component
 *  copyright: Copyright (c) 2023. Jeroen Moolenschot | Joomill
 *  license: GNU General Public License version 2 or later
 *  link: https://www.joomill-extensions.com
 */

(function () {
    'use strict';

    document.addEventListener('DOMContentLoaded', function () {
        // Get the elements
        var elements = document.querySelectorAll('.select-link');

        for (var i = 0, l = elements.length; l > i; i += 1) {
            // Listen for click event
            elements[i].addEventListener('click', function (event) {
                event.preventDefault();
                var functionName = event.target.getAttribute('data-function');

                window.parent[functionName](event.target.getAttribute('data-id'), event.target.getAttribute('data-title'), null, null, event.target.getAttribute('data-uri'), event.target.getAttribute('data-language'), null);

                if (window.parent.Joomla.Modal) {
                    window.parent.Joomla.Modal.getCurrent().close();
                }
            });
        }
    });
})();
