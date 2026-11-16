(function () {
    const THEMES = ['light', 'dark', 'pink', 'mint', 'ocean', 'lavender', 'sunset'];
    const root = document.documentElement;
    const choices = document.querySelectorAll('[data-theme-choice]');
    const wheel = document.querySelector('.theme-wheel');

    function spinWheel() {
        if (!wheel) return;
        wheel.classList.remove('spin');
        void wheel.getBoundingClientRect(); // ری‌استارت انیمیشن
        wheel.classList.add('spin');
    }

    function apply(theme, withSpin = false) {
        if (!THEMES.includes(theme)) theme = 'light';

        root.setAttribute('data-theme', theme);

        try {
            localStorage.setItem('theme', theme);
        } catch (e) {}

        choices.forEach((c) => {
            const active = c.dataset.themeChoice === theme;
            c.classList.toggle('active', active);
            c.setAttribute('aria-pressed', active ? 'true' : 'false');
        });

        if (withSpin) spinWheel();
    }

    choices.forEach((c) => {
        c.addEventListener('click', () => apply(c.dataset.themeChoice, true));

        c.addEventListener('keydown', (e) => {
            if (e.key === 'Enter' || e.key === ' ') {
                e.preventDefault();
                apply(c.dataset.themeChoice, true);
            }
        });
    });

    apply(root.getAttribute('data-theme'));
})();