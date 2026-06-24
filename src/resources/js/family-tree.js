export function createTree(config) {
    const {
        apiUrl,
        translations,
        containerId = 'tree-container',
        modalId = 'person-modal',
        modalContentId = 'modal-content',
    } = config;

    let svg, zoom, initialX, initialY;

    async function loadTree() {
        try {
            const response = await fetch(apiUrl);
            const data = await response.json();
            if (!data) {
                document.getElementById(containerId).innerHTML = `<div class="loading">${translations.noData}</div>`;
                return;
            }
            renderTree(data);
        } catch (error) {
            document.getElementById(containerId).innerHTML = `<div class="loading text-red-500">${translations.errorLoading}</div>`;
        }
    }

    function renderTree(data) {
        const container = document.getElementById(containerId);
        container.innerHTML = '';

        const width = container.clientWidth;
        const height = container.clientHeight;

        svg = d3.select(`#${containerId}`)
            .append('svg')
            .attr('width', width)
            .attr('height', height);

        const g = svg.append('g');

        const treeLayout = d3.tree()
            .nodeSize([220, 280])
            .separation((a, b) => (a.parent === b.parent ? 1 : 1.5));

        const root = d3.hierarchy(data, d => d.children);
        treeLayout(root);

        const treeBounds = root.descendants().reduce(
            (acc, d) => ({
                minX: Math.min(acc.minX, d.x),
                maxX: Math.max(acc.maxX, d.x),
                minY: Math.min(acc.minY, d.y),
                maxY: Math.max(acc.maxY, d.y),
            }),
            { minX: Infinity, maxX: -Infinity, minY: Infinity, maxY: -Infinity }
        );

        const treeWidth = treeBounds.maxX - treeBounds.minX;
        const treeHeight = treeBounds.maxY - treeBounds.minY;
        const scale = Math.min(width / (treeWidth + 200), height / (treeHeight + 200), 1);
        initialX = (width - treeWidth * scale) / 2 - treeBounds.minX * scale;
        initialY = 60;

        zoom = d3.zoom()
            .scaleExtent([0.1, 3])
            .on('zoom', (event) => {
                g.attr('transform', event.transform);
            });

        svg.call(zoom);
        svg.call(zoom.transform, d3.zoomIdentity.translate(initialX, initialY).scale(1));

        window.zoomIn = () => {
            svg.transition().duration(300).call(zoom.scaleBy, 1.3);
        };
        window.zoomOut = () => {
            svg.transition().duration(300).call(zoom.scaleBy, 0.7);
        };
        window.resetZoom = () => {
            svg.transition().duration(500).call(
                zoom.transform,
                d3.zoomIdentity.translate(initialX, initialY).scale(1)
            );
        };

        g.append('g')
            .selectAll('path')
            .data(root.links())
            .enter()
            .append('path')
            .attr('class', 'link')
            .attr('d', d3.linkVertical()
                .x(d => d.x)
                .y(d => d.y)
            );

        const nodes = g.append('g')
            .selectAll('g')
            .data(root.descendants())
            .enter()
            .append('g')
            .attr('class', 'node-group')
            .attr('transform', d => `translate(${d.x},${d.y})`)
            .on('click', (event, d) => showPersonInfo(d.data));

        nodes.append('rect')
            .attr('x', -90)
            .attr('y', -30)
            .attr('width', 180)
            .attr('height', 70)
            .attr('rx', 8)
            .attr('fill', d => d.data.gender === 'female' ? '#fdf2f8' : '#eff6ff')
            .attr('stroke', d => d.data.gender === 'female' ? '#ec4899' : '#3b82f6')
            .attr('stroke-width', 1.5)
            .attr('class', 'node-card')
            .attr('data-gender', d => d.data.gender || 'unknown');

        nodes.append('defs')
            .append('clipPath')
            .attr('id', d => `clip-${d.data.id}`)
            .append('circle')
            .attr('cx', -65)
            .attr('cy', 5)
            .attr('r', 22);

        nodes.append('image')
            .attr('x', -87)
            .attr('y', -17)
            .attr('width', 44)
            .attr('height', 44)
            .attr('clip-path', d => `url(#clip-${d.data.id})`)
            .attr('href', d => d.data.photo)
            .attr('preserveAspectRatio', 'xMidYMid slice')
            .on('error', function() { this.remove(); });

        nodes.append('circle')
            .attr('cx', -65)
            .attr('cy', 5)
            .attr('r', 22)
            .attr('fill', d => d.data.gender === 'female' ? '#fbcfe8' : '#bfdbfe')
            .attr('opacity', 0);

        nodes.append('text')
            .attr('x', -35)
            .attr('y', -5)
            .attr('font-size', '13px')
            .attr('font-weight', 'bold')
            .attr('fill', '#1e293b')
            .text(d => d.data.name.length > 18 ? d.data.name.substring(0, 16) + '...' : d.data.name);

        nodes.append('text')
            .attr('x', -35)
            .attr('y', 13)
            .attr('font-size', '11px')
            .attr('fill', '#64748b')
            .text(d => {
                if (d.data.birth_date && d.data.death_date) return `${d.data.birth_date} - ${d.data.death_date}`;
                if (d.data.birth_date) return `${translations.n} ${d.data.birth_date}`;
                return '';
            });

        nodes.append('text')
            .attr('x', -35)
            .attr('y', 30)
            .attr('font-size', '11px')
            .attr('fill', '#94a3b8')
            .text(d => d.data.children_count > 0 ? `${d.data.children_count} ${translations.children}` : '');

        nodes.filter(d => d.children && d.children.length > 0)
            .append('circle')
            .attr('cx', 80)
            .attr('cy', 5)
            .attr('r', 10)
            .attr('fill', '#e2e8f0')
            .attr('stroke', '#94a3b8')
            .attr('stroke-width', 1)
            .attr('cursor', 'pointer')
            .on('click', (event, d) => {
                event.stopPropagation();
                toggleNode(d);
            });

        nodes.filter(d => d.children && d.children.length > 0)
            .append('text')
            .attr('x', 80)
            .attr('y', 9)
            .attr('text-anchor', 'middle')
            .attr('font-size', '14px')
            .attr('fill', '#475569')
            .attr('cursor', 'pointer')
            .attr('class', 'toggle-symbol')
            .text('−')
            .on('click', (event, d) => {
                event.stopPropagation();
                toggleNode(d);
            });

        function toggleNode(d) {
            if (d.children) {
                d._children = d.children;
                d.children = null;
            } else {
                d.children = d._children;
                d._children = null;
            }
            update(root);
        }

        function update(source) {
            treeLayout(root);

            const links = g.selectAll('path.link')
                .data(root.links(), d => `${d.source.data.id}-${d.target.data.id}`);

            links.enter()
                .append('path')
                .attr('class', 'link')
                .merge(links)
                .transition()
                .duration(500)
                .attr('d', d3.linkVertical()
                    .x(d => d.x)
                    .y(d => d.y)
                );

            links.exit().remove();

            const nodeGroups = g.selectAll('g.node-group')
                .data(root.descendants(), d => d.data.id);

            const newNodes = nodeGroups.enter()
                .append('g')
                .attr('class', 'node-group')
                .attr('transform', d => `translate(${d.x},${d.y})`)
                .style('opacity', 0)
                .on('click', (event, d) => showPersonInfo(d.data));

            newNodes.merge(nodeGroups)
                .transition()
                .duration(500)
                .attr('transform', d => `translate(${d.x},${d.y})`)
                .style('opacity', 1);

            nodeGroups.exit()
                .transition()
                .duration(500)
                .style('opacity', 0)
                .remove();

            newNodes.each(function(d) {
                const group = d3.select(this);

                group.append('rect')
                    .attr('x', -90)
                    .attr('y', -30)
                    .attr('width', 180)
                    .attr('height', 70)
                    .attr('rx', 8)
                    .attr('fill', d.data.gender === 'female' ? '#fdf2f8' : '#eff6ff')
                    .attr('stroke', d.data.gender === 'female' ? '#ec4899' : '#3b82f6')
                    .attr('stroke-width', 1.5);

                group.append('defs')
                    .append('clipPath')
                    .attr('id', `clip-${d.data.id}`)
                    .append('circle')
                    .attr('cx', -65)
                    .attr('cy', 5)
                    .attr('r', 22);

                group.append('image')
                    .attr('x', -87)
                    .attr('y', -17)
                    .attr('width', 44)
                    .attr('height', 44)
                    .attr('clip-path', `url(#clip-${d.data.id})`)
                    .attr('href', d.data.photo)
                    .attr('preserveAspectRatio', 'xMidYMid slice')
                    .on('error', function() { d3.select(this).remove(); });

                group.append('text')
                    .attr('x', -35)
                    .attr('y', -5)
                    .attr('font-size', '13px')
                    .attr('font-weight', 'bold')
                    .attr('fill', '#1e293b')
                    .text(d.data.name.length > 18 ? d.data.name.substring(0, 16) + '...' : d.data.name);

                group.append('text')
                    .attr('x', -35)
                    .attr('y', 13)
                    .attr('font-size', '11px')
                    .attr('fill', '#64748b')
                    .text(() => {
                        if (d.data.birth_date && d.data.death_date) return `${d.data.birth_date} - ${d.data.death_date}`;
                        if (d.data.birth_date) return `${translations.n} ${d.data.birth_date}`;
                        return '';
                    });

                group.append('text')
                    .attr('x', -35)
                    .attr('y', 30)
                    .attr('font-size', '11px')
                    .attr('fill', '#94a3b8')
                    .text(d.data.children_count > 0 ? `${d.data.children_count} ${translations.children}` : '');

                if ((d.children && d.children.length > 0) || (d._children && d._children.length > 0)) {
                    group.append('circle')
                        .attr('cx', 80)
                        .attr('cy', 5)
                        .attr('r', 10)
                        .attr('fill', '#e2e8f0')
                        .attr('stroke', '#94a3b8')
                        .attr('stroke-width', 1)
                        .attr('cursor', 'pointer')
                        .on('click', (event, node) => { event.stopPropagation(); toggleNode(node); });

                    group.append('text')
                        .attr('x', 80)
                        .attr('y', 9)
                        .attr('text-anchor', 'middle')
                        .attr('font-size', '14px')
                        .attr('fill', '#475569')
                        .attr('cursor', 'pointer')
                        .attr('class', 'toggle-symbol')
                        .text(d.children ? '−' : '+')
                        .on('click', (event, node) => { event.stopPropagation(); toggleNode(node); });
                }
            });

            // Update toggle symbols for all nodes (enter + update)
            nodeGroups.merge(newNodes).each(function(d) {
                const symbol = d3.select(this).select('text.toggle-symbol');
                if (!symbol.empty()) {
                    symbol.text(d.children ? '−' : '+');
                }
            });
        }
    }

    function showPersonInfo(person) {
        const modal = document.getElementById(modalId);
        const content = document.getElementById(modalContentId);

        const genderLabel = person.gender === 'male' ? translations.male : person.gender === 'female' ? translations.female : translations.notSpecified;

        content.innerHTML = `
            <div class="flex justify-between items-start mb-4">
                <h2 class="text-xl font-bold text-gray-800">${person.name}</h2>
                <button onclick="closeModal()" class="text-gray-400 hover:text-gray-600">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>
            <div class="flex items-center space-x-4 mb-4">
                <img src="${person.photo}" alt="${person.name}" class="w-20 h-20 rounded-full object-cover border-2 ${person.gender === 'female' ? 'border-pink-300' : 'border-blue-300'}"
                    onerror="this.src='https://ui-avatars.com/api/?name=${encodeURIComponent(person.name)}&size=200&background=random'">
                <div>
                    <p class="text-sm text-gray-600">
                        ${person.birth_date ? '<span class="font-medium">' + translations.birth + '</span> ' + person.birth_date : ''}
                        ${person.death_date ? '<br><span class="font-medium">' + translations.death + '</span> ' + person.death_date : ''}
                    </p>
                    <p class="text-sm text-gray-600">
                        <span class="font-medium">${translations.gender}</span> ${genderLabel}
                    </p>
                    <p class="text-sm text-gray-600">
                        <span class="font-medium">${translations.childrenLabel}</span> ${person.children_count || 0}
                    </p>
                </div>
            </div>
            ${person.biography ? `
                <div class="mb-4">
                    <h3 class="font-semibold text-gray-700 mb-1">${translations.biography}</h3>
                    <p class="text-sm text-gray-600">${person.biography}</p>
                </div>
            ` : ''}
            <a href="/tree/${person.id}" class="inline-flex items-center text-sm text-blue-600 hover:text-blue-800">
                <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                </svg>
                ${translations.viewFullTree} ${person.first_name}
            </a>
        `;

        modal.classList.remove('hidden');
        modal.classList.add('flex');
        modal.addEventListener('click', (e) => {
            if (e.target === modal) closeModal();
        });
    }

    function closeModal() {
        const modal = document.getElementById(modalId);
        modal.classList.add('hidden');
        modal.classList.remove('flex');
    }

    document.addEventListener('keydown', (e) => {
        if (e.key === 'Escape') closeModal();
    });

    async function exportSVG() {
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

    window.closeModal = closeModal;
    window.exportSVG = exportSVG;

    loadTree();
}

window.createTree = createTree;

if (window.__TREE_CONFIG) {
    createTree(window.__TREE_CONFIG);
}
