// Tema: sigue al sistema hasta que el usuario elige con el botón. La elección se guarda en el navegador.
const root = document.documentElement;
const storageKey = 'gg-theme';

const isDark = () => (root.dataset.theme || (window.matchMedia('(prefers-color-scheme: dark)').matches ? 'dark' : 'light')) === 'dark';

const paintToggles = () => {
    document.querySelectorAll('[data-theme-toggle]').forEach((button) => {
        button.textContent = isDark() ? 'Modo claro' : 'Modo oscuro';
    });
};

try {
    const saved = localStorage.getItem(storageKey);
    if (saved) root.dataset.theme = saved;
} catch {
    // Sin almacenamiento: el tema sigue al sistema.
}

document.addEventListener('click', (event) => {
    if (!event.target.closest('[data-theme-toggle]')) return;

    const next = isDark() ? 'light' : 'dark';
    root.dataset.theme = next;
    try {
        localStorage.setItem(storageKey, next);
    } catch {
        // Sin almacenamiento: vale solo para esta página.
    }
    paintToggles();
});

paintToggles();
