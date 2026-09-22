(function () {
    'use strict';

    var selector = '[data-aznet-curtain-cinematic]';
    var slideDelay = 7000;

    function clamp(value, min, max) {
        return Math.min(Math.max(value, min), max);
    }

    function init(hero) {
        var reducedMotion = window.matchMedia('(prefers-reduced-motion: reduce)');
        var finePointer = window.matchMedia('(pointer: fine)').matches;
        var slides = Array.prototype.slice.call(
            hero.querySelectorAll('.aznet-theme-curtain01-hero__library .wp-block-cover')
        );
        var image = hero.querySelector('.wp-block-cover__image-background, .aznet-theme-curtain01-hero__image');
        var slideTimer = 0;
        var activeSlide = 0;
        var dots = [];

        if (!image) {
            return;
        }

        if (slides.length) {
            var slideHost = slides[0].parentElement;
            var sharedHost = slideHost && slides.every(function (slide) {
                return slide.parentElement === slideHost;
            });

            if (sharedHost) {
                slideHost.classList.add('aznet-theme-curtain01-hero__slide-stage');
            }

            slides.forEach(function (slide, index) {
                slide.classList.add('aznet-theme-curtain01-hero__slide');
                slide.setAttribute('data-aznet-curtain-slide', String(index + 1));
                slide.classList.toggle('is-active', 0 === index);
                slide.setAttribute('aria-hidden', 0 === index ? 'false' : 'true');
            });
        }

        function clearSlideTimer() {
            if (slideTimer) {
                window.clearTimeout(slideTimer);
                slideTimer = 0;
            }
        }

        function setActiveSlide(index, userInitiated) {
            if (slides.length < 2) {
                return;
            }

            activeSlide = (index + slides.length) % slides.length;

            slides.forEach(function (slide, slideIndex) {
                var active = slideIndex === activeSlide;
                slide.classList.toggle('is-active', active);
                slide.setAttribute('aria-hidden', active ? 'false' : 'true');
            });

            dots.forEach(function (dot, dotIndex) {
                var active = dotIndex === activeSlide;
                dot.classList.toggle('is-active', active);
                dot.setAttribute('aria-pressed', active ? 'true' : 'false');
            });

            if (userInitiated) {
                scheduleNextSlide();
            }
        }

        function scheduleNextSlide() {
            clearSlideTimer();

            if (slides.length < 2 || reducedMotion.matches || document.hidden) {
                return;
            }

            slideTimer = window.setTimeout(function () {
                setActiveSlide(activeSlide + 1, false);
                scheduleNextSlide();
            }, slideDelay);
        }

        if (slides.length > 1) {
            hero.classList.add('has-cinematic-slides');

            var controls = document.createElement('div');
            controls.className = 'aznet-theme-curtain01-hero__dots';
            controls.setAttribute('role', 'group');
            controls.setAttribute('aria-label', 'Chọn ảnh Hero');

            slides.forEach(function (slide, index) {
                var dot = document.createElement('button');
                dot.type = 'button';
                dot.className = 'aznet-theme-curtain01-hero__dot' + (0 === index ? ' is-active' : '');
                dot.setAttribute('aria-label', 'Ảnh Hero ' + (index + 1));
                dot.setAttribute('aria-pressed', 0 === index ? 'true' : 'false');
                dot.addEventListener('click', function () {
                    setActiveSlide(index, true);
                });
                dots.push(dot);
                controls.appendChild(dot);
            });

            var library = hero.querySelector('.aznet-theme-curtain01-hero__library');
            if (library) {
                library.appendChild(controls);
            }

            hero.addEventListener('pointerenter', clearSlideTimer, { passive: true });
            hero.addEventListener('pointerleave', scheduleNextSlide, { passive: true });
            hero.addEventListener('focusin', clearSlideTimer);
            hero.addEventListener('focusout', scheduleNextSlide);
            document.addEventListener('visibilitychange', function () {
                if (document.hidden) {
                    clearSlideTimer();
                } else {
                    scheduleNextSlide();
                }
            });

            scheduleNextSlide();
        }

        if (reducedMotion.matches) {
            hero.classList.add('is-cinematic-ready');
            return;
        }

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
