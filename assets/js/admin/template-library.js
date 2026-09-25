(function () {
    'use strict';

    function normalize(value) {
        return String(value || '').toLocaleLowerCase();
    }

    function initLibrary(root) {
        var search = root.querySelector('[data-aznet-template-search]');
        var category = root.querySelector('[data-aznet-template-category]');
        var cards = Array.prototype.slice.call(root.querySelectorAll('[data-template-search]'));

        if (!search || !category || !cards.length) {
            return;
        }

        function applyFilter() {
            var query = normalize(search.value).trim();
            var selectedCategory = category.value;

            cards.forEach(function (card) {
                var haystack = normalize(card.getAttribute('data-template-search'));
                var cardCategory = card.getAttribute('data-template-category') || '';
                var matchesQuery = !query || haystack.indexOf(query) !== -1;
                var matchesCategory = !selectedCategory || cardCategory === selectedCategory;
                card.hidden = !(matchesQuery && matchesCategory);
            });
        }

        search.addEventListener('input', applyFilter);
        category.addEventListener('change', applyFilter);
    }

    document.addEventListener('DOMContentLoaded', function () {
        document.querySelectorAll('[data-aznet-template-library]').forEach(initLibrary);
    });
}());
