(function () {
    const root = document.documentElement;
    const buttons = document.querySelectorAll('[data-theme-choice]');

    function apply(theme) {
        if (theme === 'auto') {
            root.removeAttribute('data-theme');
        } else {
            root.setAttribute('data-theme', theme);
        }

        try {
            localStorage.setItem('theme', theme === 'auto' ? '' : theme);
        } catch (e) {}

        buttons.forEach((b) => {
            const current = root.getAttribute('data-theme') || 'auto';
            b.classList.toggle('active', b.dataset.themeChoice === current);
        });
    }

    buttons.forEach((b) => {
        b.addEventListener('click', () => apply(b.dataset.themeChoice));
    });

    // وضعیت اولیه‌ی دکمه‌ها
    apply(root.getAttribute('data-theme') || 'auto');
})();