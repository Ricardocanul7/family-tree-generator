@extends('layouts.app')

@section('title', __('Family Tree'))

@push('styles')
<style>
    #tree-container {
        width: 100%;
        height: 80vh;
        background: #f8fafc;
        overflow: hidden;
    }
    .dark #tree-container {
        background: #1e293b;
    }
    .node-card {
        cursor: pointer;
        transition: transform 0.2s;
    }
    .node-card:hover {
        transform: translateY(-2px);
    }
    .node-card.male { border-left: 4px solid #3b82f6; }
    .node-card.female { border-left: 4px solid #ec4899; }
    .link {
        fill: none;
        stroke: #cbd5e1;
        stroke-width: 2px;
    }
    .dark .link {
        stroke: #475569;
    }
    .controls {
        position: absolute;
        top: 1rem;
        right: 0;
        display: flex;
        gap: 0.5rem;
        z-index: 10;
    }
    .controls button {
        background: white;
        border: 1px solid #e2e8f0;
        border-radius: 0.5rem;
        padding: 0.5rem;
        cursor: pointer;
        box-shadow: 0 1px 3px rgba(0,0,0,0.1);
        transition: all 0.2s;
    }
    .dark .controls button {
        background: #334155;
        border-color: #475569;
        color: #e2e8f0;
    }
    .controls button:hover {
        background: #f1f5f9;
        box-shadow: 0 2px 6px rgba(0,0,0,0.15);
    }
    .dark .controls button:hover {
        background: #475569;
    }
    .loading {
        display: flex;
        justify-content: center;
        align-items: center;
        height: 80vh;
        font-size: 1.25rem;
        color: #64748b;
    }
    .dark .loading {
        color: #94a3b8;
    }
    @media print {
        nav, .max-w-7xl, .controls, #person-modal { display: none !important; }
        #tree-container {
            position: fixed !important;
            top: 0 !important;
            left: 0 !important;
            width: 100% !important;
            height: 100vh !important;
            background: white !important;
            overflow: visible !important;
        }
        #tree-container svg { width: 100%; height: 100%; }
        body { background: white !important; }
    }
</style>
@endpush

@section('content')
<div class="max-w-7xl mx-auto px-4 py-6">
    <div class="bg-white rounded-xl shadow-sm border p-6 mb-6">
        <h1 class="text-2xl font-bold text-gray-800">{{ __('Family Tree') }}</h1>
        <p class="text-gray-600 mt-1">{{ __('Explore your interactive family tree. Zoom, pan and click on nodes to see more information.') }}</p>
        @if($people->count() === 0)
            <div class="mt-4 p-4 bg-yellow-50 border border-yellow-200 rounded-lg">
                <p class="text-yellow-800">{{ __('No people registered.') }} <a href="/admin" class="underline font-medium">{{ __('Go to admin panel') }}</a> {{ __('to add family members.') }}</p>
            </div>
        @endif
    </div>

</div>

<div class="relative">
    <div class="-mx-4 sm:-mx-6 lg:-mx-8">
        <div id="tree-container">
            <div class="loading">
                <svg class="animate-spin h-8 w-8 text-blue-500 mr-3" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                </svg>
                {{ __('Loading family tree...') }}
            </div>
        </div>
    </div>
    <div class="max-w-7xl mx-auto px-4 absolute inset-0 pointer-events-none">
        <div class="controls pointer-events-auto">
            <button onclick="zoomIn()" title="{{ __('Zoom in') }}">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                </svg>
            </button>
            <button onclick="zoomOut()" title="{{ __('Zoom out') }}">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 12H4"/>
                </svg>
            </button>
            <button onclick="resetZoom()" title="{{ __('Reset') }}">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                </svg>
            </button>
            <div class="w-px h-6 bg-gray-300 dark:bg-gray-600 self-center"></div>
            <button onclick="window.print()" title="{{ __('Print') }}">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/>
                </svg>
            </button>
            <button onclick="exportSVG()" title="{{ __('Export SVG') }}">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                </svg>
            </button>
            <button onclick="exportPDF()" title="{{ __('Export PDF') }}">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"/>
                </svg>
            </button>
        </div>
    </div>
</div>

<!-- Modal -->
<div id="person-modal" class="fixed inset-0 bg-black bg-opacity-50 hidden items-center justify-center z-50">
    <div class="bg-white rounded-xl shadow-2xl max-w-md w-full mx-4 max-h-[80vh] overflow-y-auto">
        <div id="modal-content" class="p-6">
        </div>
    </div>
</div>

@if($people->count() > 0)
<script src="https://d3js.org/d3.v7.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/jspdf@2.5.1/dist/jspdf.umd.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/svg2pdf.js@2.2.4/dist/svg2pdf.umd.min.js"></script>
<script>
    const i18n = {
        noData: '{{ __("No data available") }}',
        errorLoading: '{{ __("Error loading tree") }}',
        exportError: '{{ __("Export error") }}',
        children: '{{ __("children") }}',
        birth: '{{ __("Birth:") }}',
        death: '{{ __("Death:") }}',
        gender: '{{ __("Gender:") }}',
        male: '{{ __("Male") }}',
        female: '{{ __("Female") }}',
        notSpecified: '{{ __("Not specified") }}',
        childrenLabel: '{{ __("Children:") }}',
        biography: '{{ __("Biography") }}',
        viewFullTree: '{{ __("View full tree of") }}',
        n: '{{ __("N.") }}',
    };

    async function loadTree() {
        try {
            const response = await fetch('/api/tree/full');
            const data = await response.json();
            if (!data) {
                document.getElementById('tree-container').innerHTML = '<div class="loading">' + i18n.noData + '</div>';
                return;
            }
            renderTree(data);
        } catch (error) {
            document.getElementById('tree-container').innerHTML = '<div class="loading text-red-500">' + i18n.errorLoading + '</div>';
        }
    }

    function renderTree(data) {
        const container = document.getElementById('tree-container');
        container.innerHTML = '';

        const width = container.clientWidth;
        const height = container.clientHeight;

        const svg = d3.select('#tree-container')
            .append('svg')
            .attr('width', width)
            .attr('height', height);

        const g = svg.append('g');

        const treeLayout = d3.tree()
            .nodeSize([220, 280])
            .separation((a, b) => (a.parent === b.parent ? 1 : 1.5));

        const root = d3.hierarchy(data, d => d.children);
        treeLayout(root);

        // Center the tree
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
        const initialX = (width - treeWidth * scale) / 2 - treeBounds.minX * scale;
        const initialY = 60;

        const zoom = d3.zoom()
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

        // Draw links
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

        // Draw nodes
        const nodes = g.append('g')
            .selectAll('g')
            .data(root.descendants())
            .enter()
            .append('g')
            .attr('transform', d => `translate(${d.x},${d.y})`)
            .on('click', (event, d) => showPersonInfo(d.data));

        // Card background
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

        // Photo (circle)
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

        // Default avatar fallback circle
        nodes.append('circle')
            .attr('cx', -65)
            .attr('cy', 5)
            .attr('r', 22)
            .attr('fill', d => d.data.gender === 'female' ? '#fbcfe8' : '#bfdbfe')
            .attr('opacity', 0);

        // Name
        nodes.append('text')
            .attr('x', -35)
            .attr('y', -5)
            .attr('font-size', '13px')
            .attr('font-weight', 'bold')
            .attr('fill', '#1e293b')
            .text(d => d.data.name.length > 18 ? d.data.name.substring(0, 16) + '...' : d.data.name);

        // Birth/death dates
        nodes.append('text')
            .attr('x', -35)
            .attr('y', 13)
            .attr('font-size', '11px')
            .attr('fill', '#64748b')
            .text(d => {
                if (d.data.birth_date && d.data.death_date) return `${d.data.birth_date} - ${d.data.death_date}`;
                if (d.data.birth_date) return i18n.n + ' ' + d.data.birth_date;
                return '';
            });

        // Children count badge
        nodes.append('text')
            .attr('x', -35)
            .attr('y', 30)
            .attr('font-size', '11px')
            .attr('fill', '#94a3b8')
            .text(d => d.data.children_count > 0 ? `${d.data.children_count} ${i18n.children}` : '');

        // Toggle button for nodes with children
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

            // Rebuild card content for new nodes
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
                        if (d.data.birth_date) return i18n.n + ' ' + d.data.birth_date;
                        return '';
                    });

                group.append('text')
                    .attr('x', -35)
                    .attr('y', 30)
                    .attr('font-size', '11px')
                    .attr('fill', '#94a3b8')
                    .text(d.data.children_count > 0 ? `${d.data.children_count} hijos` : '');

                if (d.children && d.children.length > 0) {
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
                        .text('−')
                        .on('click', (event, node) => { event.stopPropagation(); toggleNode(node); });
                }
            });
        }
    }

    function showPersonInfo(person) {
        const modal = document.getElementById('person-modal');
        const content = document.getElementById('modal-content');

        const genderLabel = person.gender === 'male' ? i18n.male : person.gender === 'female' ? i18n.female : i18n.notSpecified;
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
                        ${person.birth_date ? '<span class="font-medium">' + i18n.birth + '</span> ' + person.birth_date : ''}
                        ${person.death_date ? '<br><span class="font-medium">' + i18n.death + '</span> ' + person.death_date : ''}
                    </p>
                    <p class="text-sm text-gray-600">
                        <span class="font-medium">${i18n.gender}</span> ${genderLabel}
                    </p>
                    <p class="text-sm text-gray-600">
                        <span class="font-medium">${i18n.childrenLabel}</span> ${person.children_count || 0}
                    </p>
                </div>
            </div>
            ${person.biography ? `
                <div class="mb-4">
                    <h3 class="font-semibold text-gray-700 mb-1">${i18n.biography}</h3>
                    <p class="text-sm text-gray-600">${person.biography}</p>
                </div>
            ` : ''}
            <a href="/tree/${person.id}" class="inline-flex items-center text-sm text-blue-600 hover:text-blue-800">
                <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                </svg>
                ${i18n.viewFullTree} ${person.first_name}
            </a>
        `;

        modal.classList.remove('hidden');
        modal.classList.add('flex');
        modal.addEventListener('click', (e) => {
            if (e.target === modal) closeModal();
        });
    }

    function closeModal() {
        const modal = document.getElementById('person-modal');
        modal.classList.add('hidden');
        modal.classList.remove('flex');
    }

    // Handle keyboard escape
    document.addEventListener('keydown', (e) => {
        if (e.key === 'Escape') closeModal();
    });

    function exportSVG() {
        const svg = document.querySelector('#tree-container svg');
        if (!svg) return;
        const isDark = document.documentElement.classList.contains('dark');
        const linkColor = isDark ? '#475569' : '#cbd5e1';
        svg.querySelectorAll('.link').forEach(path => {
            path.setAttribute('fill', 'none');
            path.setAttribute('stroke', linkColor);
            path.setAttribute('stroke-width', '2');
        });
        const serializer = new XMLSerializer();
        let source = serializer.serializeToString(svg);
        source = '<' + '?xml version="1.0" encoding="UTF-8"?>\n' + source;
        const blob = new Blob([source], { type: 'image/svg+xml;charset=utf-8' });
        const link = document.createElement('a');
        link.download = 'family-tree.svg';
        link.href = URL.createObjectURL(blob);
        link.click();
        URL.revokeObjectURL(link.href);
    }

    async function exportPDF() {
        const svg = document.querySelector('#tree-container svg');
        if (!svg) return;
        try {
            const rect = svg.getBoundingClientRect();
            const { jsPDF } = window.jspdf;
            const pdf = new jsPDF(rect.width > rect.height ? 'landscape' : 'portrait', 'pt', [rect.width, rect.height]);
            await svg2pdf(svg, pdf, {});
            pdf.save('family-tree.pdf');
        } catch (e) {
            document.getElementById('tree-container').innerHTML = '<div class="loading text-red-500">' + i18n.exportError + '</div>';
        }
    }

    loadTree();
</script>
@endif
@endSection
