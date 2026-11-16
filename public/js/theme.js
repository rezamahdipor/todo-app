(function () {
    const THEMES = ['light', 'dark', 'pink'];
    const root = document.documentElement;
    const buttons = document.querySelectorAll('[data-theme-choice]');

    function apply(theme) {
        if (!THEMES.includes(theme)) theme = 'light';

        root.setAttribute('data-theme', theme);

        try {
            localStorage.setItem('theme', theme);
        } catch (e) {}

        buttons.forEach((b) => {
            b.classList.toggle('active', b.dataset.themeChoice === theme);
        });
    }

    buttons.forEach((b) => {
        b.addEventListener('click', () => apply(b.dataset.themeChoice));
    });

    apply(root.getAttribute('data-theme'));
})();