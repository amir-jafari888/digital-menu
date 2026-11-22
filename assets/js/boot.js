/* Entrance motion is progressive enhancement: the page is complete without it. */
(function () {
    'use strict';

    var root = document.documentElement;
    root.classList.add('m-enter');
    window.setTimeout(function () {
        root.classList.add('m-enter-done');
    }, 1150);
}());
