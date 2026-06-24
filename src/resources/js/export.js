export async function exportSVG(containerId) {
    const container = document.getElementById(containerId);
    const svgEl = container.querySelector('svg');
    if (!svgEl) return;

    const isDark = document.documentElement.classList.contains('dark');
    const linkColor = isDark ? '#475569' : '#cbd5e1';

    svgEl.querySelectorAll('.link').forEach(path => {
        path.setAttribute('fill', 'none');
        path.setAttribute('stroke', linkColor);
        path.setAttribute('stroke-width', '2');
    });

    await Promise.all(Array.from(svgEl.querySelectorAll('image')).map(async (img) => {
        const href = img.getAttribute('href');
        if (href && !href.startsWith('data:')) {
            try {
                const response = await fetch(href);
                const blob = await response.blob();
                const reader = new FileReader();
                return new Promise(resolve => {
                    reader.onload = () => { img.setAttribute('href', reader.result); resolve(); };
                    reader.readAsDataURL(blob);
                });
            } catch (e) {}
        }
    }));

    const serializer = new XMLSerializer();
    let source = serializer.serializeToString(svgEl);
    source = '<?xml version="1.0" encoding="UTF-8"?>\n' + source;

    const blob = new Blob([source], { type: 'image/svg+xml;charset=utf-8' });
    const link = document.createElement('a');
    link.download = 'family-tree.svg';
    link.href = URL.createObjectURL(blob);
    link.click();
    URL.revokeObjectURL(link.href);
}
