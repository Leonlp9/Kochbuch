document.querySelectorAll('.number-input').forEach((element) => {
    element.querySelector('.down').addEventListener('click', function() {
        element.querySelector('input[type=number]').stepDown()
        element.querySelector('input[type=number]').dispatchEvent(new Event('change'));
    });
    element.querySelector('.up').addEventListener('click', function() {
        element.querySelector('input[type=number]').stepUp()
        element.querySelector('input[type=number]').dispatchEvent(new Event('change'));
    });
    element.querySelector('input[type=number]').addEventListener('change', function() {
        if (this.value > parseInt(this.max)) {
            this.value = this.max;
            this.dispatchEvent(new Event('change'));
        }
        if (this.value < parseInt(this.min)) {
            this.value = this.min;
            this.dispatchEvent(new Event('change'));
        }
    });
});

document.querySelectorAll('.horizontalScrollBarJS').forEach((element) => {
    let scrollAmount = 0;
    let isScrolling = false;
    let isDragging = false;
    let startX;
    let scrollLeft;

    element.addEventListener('wheel', function(event) {
        if (event.deltaY !== 0) {
            event.preventDefault();
            scrollAmount += event.deltaY;
            if (!isScrolling) {
                isScrolling = true;
                requestAnimationFrame(smoothScroll);
            }
        }
    });

    element.addEventListener('mousedown', (event) => {
        isDragging = true;
        startX = event.pageX - element.offsetLeft;
        scrollLeft = element.scrollLeft;
        element.classList.add('active');
    });

    element.addEventListener('mouseleave', () => {
        isDragging = false;
        element.classList.remove('active');
    });

    element.addEventListener('mouseup', () => {
        isDragging = false;
        element.classList.remove('active');
    });

    element.addEventListener('mousemove', (event) => {
        if (!isDragging) return;
        event.preventDefault();
        const x = event.pageX - element.offsetLeft;
        const walk = (x - startX) * 1.5; // The multiplier can be adjusted for sensitivity
        element.scrollLeft = scrollLeft - walk;
    });

    function smoothScroll() {
        if (scrollAmount !== 0) {
            const scrollStep = scrollAmount / 10;
            element.scrollBy({
                left: scrollStep,
                behavior: 'auto'
            });
            scrollAmount -= scrollStep;
            if (Math.abs(scrollAmount) < 1) {
                scrollAmount = 0;
            }
            requestAnimationFrame(smoothScroll);
        } else {
            isScrolling = false;
        }
    }
});

document.documentElement.setAttribute('theme', "pink-rounder");