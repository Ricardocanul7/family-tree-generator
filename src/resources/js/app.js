document.addEventListener('DOMContentLoaded', () => {
    if (document.documentElement.classList.contains('dark')) {
        document.querySelectorAll('.moon-icon, .sun-icon').forEach(el => el.classList.toggle('hidden'));
    }
});

function toggleDarkMode() {
    const html = document.documentElement;
    const isDark = html.classList.contains('dark');
    if (isDark) {
        html.classList.remove('dark');
        localStorage.setItem('theme', 'light');
    } else {
        html.classList.add('dark');
        localStorage.setItem('theme', 'dark');
    }
    document.querySelectorAll('.moon-icon, .sun-icon').forEach(el => el.classList.toggle('hidden'));
}

window.toggleDarkMode = toggleDarkMode;
