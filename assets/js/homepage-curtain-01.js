(function () {
    'use strict';

    var selector = '[data-aznet-curtain-cinematic]';

    function clamp(value, min, max) {
        return Math.min(Math.max(value, min), max);
    }

    function init(hero) {
        var image = hero.querySelector('.wp-block-cover__image-background, .aznet-theme-curtain01-hero__image');
        if (!image) {
            return;
        }

        var reducedMotion = window.matchMedia('(prefers-reduced-motion: reduce)');
        if (reducedMotion.matches) {
            hero.classList.add('is-cinematic-ready');
            return;
        }

        var finePointer = window.matchMedia('(pointer: fine)').matches;
        var currentX = 0;
        var currentY = 0;
        var currentScroll = 0;
        var targetX = 0;
        var targetY = 0;
        var targetScroll = 0;
        var frame = 0;

        function render() {
            currentX += (targetX - currentX) * 0.12;
            currentY += (targetY - currentY) * 0.12;
            currentScroll += (targetScroll - currentScroll) * 0.1;

            hero.style.setProperty('--aznet-curtain-shift-x', currentX.toFixed(2) + 'px');
            hero.style.setProperty('--aznet-curtain-shift-y', currentY.toFixed(2) + 'px');
            hero.style.setProperty('--aznet-curtain-scroll-shift', currentScroll.toFixed(2) + 'px');

            if (
                Math.abs(targetX - currentX) > 0.05 ||
                Math.abs(targetY - currentY) > 0.05 ||
                Math.abs(targetScroll - currentScroll) > 0.05
            ) {
                frame = window.requestAnimationFrame(render);
            } else {
                frame = 0;
            }
        }

        function requestRender() {
            if (!frame) {
                frame = window.requestAnimationFrame(render);
            }
        }

        function updateScrollDepth() {
            var rect = hero.getBoundingClientRect();
            if (rect.bottom < 0 || rect.top > window.innerHeight) {
                return;
            }

            var heroCenter = rect.top + (rect.height / 2);
            var viewportCenter = window.innerHeight / 2;
            var depth = clamp((heroCenter - viewportCenter) / Math.max(window.innerHeight, 1), -1, 1);
            var mobileFactor = window.innerWidth < 768 ? 0.35 : 1;

            targetScroll = depth * -10 * mobileFactor;
            requestRender();
        }

        if (finePointer) {
            hero.addEventListener('pointermove', function (event) {
                var rect = hero.getBoundingClientRect();
                if (!rect.width || !rect.height) {
                    return;
                }

                var x = ((event.clientX - rect.left) / rect.width) - 0.5;
                var y = ((event.clientY - rect.top) / rect.height) - 0.5;

                targetX = clamp(x * 14, -7, 7);
                targetY = clamp(y * 10, -5, 5);
                requestRender();
            }, { passive: true });

            hero.addEventListener('pointerleave', function () {
                targetX = 0;
                targetY = 0;
                requestRender();
            }, { passive: true });
        }

        window.addEventListener('scroll', updateScrollDepth, { passive: true });
        window.addEventListener('resize', updateScrollDepth, { passive: true });

        updateScrollDepth();
        window.requestAnimationFrame(function () {
            hero.classList.add('is-cinematic-ready');
        });
    }

    function boot() {
        document.querySelectorAll(selector).forEach(init);
    }

    if ('loading' === document.readyState) {
        document.addEventListener('DOMContentLoaded', boot, { once: true });
    } else {
        boot();
    }
}());
