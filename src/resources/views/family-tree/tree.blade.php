@extends('layouts.app')

@section('title', 'Árbol de ' . $rootPerson->full_name)

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
</style>
@endpush

@section('content')
<div class="max-w-7xl mx-auto px-4 py-6">
    <div class="bg-white rounded-xl shadow-sm border p-6 mb-6">
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-2xl font-bold text-gray-800">Árbol de {{ $rootPerson->full_name }}</h1>
                <p class="text-gray-600 mt-1">Visualizando el árbol familiar desde {{ $rootPerson->first_name }}.</p>
            </div>
            <a href="{{ route('family-tree.index') }}" class="text-blue-600 hover:text-blue-800 text-sm">
                ← Ver árbol completo
            </a>
        </div>
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
                Cargando árbol...
            </div>
        </div>
    </div>
    <div class="max-w-7xl mx-auto px-4 absolute inset-0 pointer-events-none">
        <div class="controls pointer-events-auto">
            <button onclick="zoomIn()" title="Acercar">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                </svg>
            </button>
            <button onclick="zoomOut()" title="Alejar">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 12H4"/>
                </svg>
            </button>
            <button onclick="resetZoom()" title="Restablecer">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                </svg>
            </button>
        </div>
    </div>
</div>

<div id="person-modal" class="fixed inset-0 bg-black bg-opacity-50 hidden items-center justify-center z-50">
    <div class="bg-white rounded-xl shadow-2xl max-w-md w-full mx-4 max-h-[80vh] overflow-y-auto">
        <div id="modal-content" class="p-6"></div>
    </div>
</div>

<script src="https://d3js.org/d3.v7.min.js"></script>
<script>
    const url = '{{ route("family-tree.data", $rootPerson) }}';

    async function loadTree() {
        try {
            const response = await fetch(url);
            const data = await response.json();
            renderTree(data);
        } catch (error) {
            document.getElementById('tree-container').innerHTML = '<div class="loading text-red-500">Error al cargar el árbol</div>';
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

        const treeLayout = d3.tree().nodeSize([220, 280]);

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
        const initialX = (width - treeWidth * scale) / 2 - treeBounds.minX * scale;
        const initialY = 60;

        const zoom = d3.zoom()
            .scaleExtent([0.1, 3])
            .on('zoom', (event) => g.attr('transform', event.transform));

        svg.call(zoom);
        svg.call(zoom.transform, d3.zoomIdentity.translate(initialX, initialY).scale(1));

        window.zoomIn = () => svg.transition().duration(300).call(zoom.scaleBy, 1.3);
        window.zoomOut = () => svg.transition().duration(300).call(zoom.scaleBy, 0.7);
        window.resetZoom = () => svg.transition().duration(500).call(zoom.transform, d3.zoomIdentity.translate(initialX, initialY).scale(1));

        g.append('g')
            .selectAll('path')
            .data(root.links())
            .enter()
            .append('path')
            .attr('fill', 'none')
            .attr('stroke', '#cbd5e1')
            .attr('stroke-width', 2)
            .attr('d', d3.linkVertical().x(d => d.x).y(d => d.y));

        const nodes = g.append('g')
            .selectAll('g')
            .data(root.descendants())
            .enter()
            .append('g')
            .attr('transform', d => `translate(${d.x},${d.y})`)
            .on('click', (event, d) => showPersonInfo(d.data));

        nodes.append('rect')
            .attr('x', -90).attr('y', -30)
            .attr('width', 180).attr('height', 70)
            .attr('rx', 8)
            .attr('fill', d => d.data.gender === 'female' ? '#fdf2f8' : '#eff6ff')
            .attr('stroke', d => d.data.gender === 'female' ? '#ec4899' : '#3b82f6')
            .attr('stroke-width', 1.5);

        nodes.append('defs')
            .append('clipPath')
            .attr('id', d => `clip-${d.data.id}`)
            .append('circle').attr('cx', -65).attr('cy', 5).attr('r', 22);

        nodes.append('image')
            .attr('x', -87).attr('y', -17)
            .attr('width', 44).attr('height', 44)
            .attr('clip-path', d => `url(#clip-${d.data.id})`)
            .attr('href', d => d.data.photo)
            .attr('preserveAspectRatio', 'xMidYMid slice')
            .on('error', function() { this.remove(); });

        nodes.append('text')
            .attr('x', -35).attr('y', -5)
            .attr('font-size', '13px').attr('font-weight', 'bold').attr('fill', '#1e293b')
            .text(d => d.data.name.length > 18 ? d.data.name.substring(0, 16) + '...' : d.data.name);

        nodes.append('text')
            .attr('x', -35).attr('y', 13)
            .attr('font-size', '11px').attr('fill', '#64748b')
            .text(d => {
                if (d.data.birth_date && d.data.death_date) return `${d.data.birth_date} - ${d.data.death_date}`;
                if (d.data.birth_date) return `N. ${d.data.birth_date}`;
                return '';
            });

        nodes.append('text')
            .attr('x', -35).attr('y', 30)
            .attr('font-size', '11px').attr('fill', '#94a3b8')
            .text(d => d.data.children_count > 0 ? `${d.data.children_count} hijos` : '');

        nodes.filter(d => d.children && d.children.length > 0)
            .append('circle')
            .attr('cx', 80).attr('cy', 5).attr('r', 10)
            .attr('fill', '#e2e8f0').attr('stroke', '#94a3b8').attr('stroke-width', 1)
            .attr('cursor', 'pointer')
            .on('click', (event, d) => { event.stopPropagation(); toggleNode(d); });

        nodes.filter(d => d.children && d.children.length > 0)
            .append('text')
            .attr('x', 80).attr('y', 9)
            .attr('text-anchor', 'middle').attr('font-size', '14px').attr('fill', '#475569')
            .attr('cursor', 'pointer').text('−')
            .on('click', (event, d) => { event.stopPropagation(); toggleNode(d); });

        function toggleNode(d) {
            if (d.children) { d._children = d.children; d.children = null; }
            else { d.children = d._children; d._children = null; }
            update(root);
        }

        function update(source) {
            treeLayout(root);

            const links = g.selectAll('path')
                .data(root.links(), d => `${d.source.data.id}-${d.target.data.id}`);

            links.enter().append('path')
                .attr('fill', 'none').attr('stroke', '#cbd5e1').attr('stroke-width', 2)
                .merge(links)
                .transition().duration(500)
                .attr('d', d3.linkVertical().x(d => d.x).y(d => d.y));

            links.exit().remove();

            const nodeGroups = g.selectAll('g.node-group')
                .data(root.descendants(), d => d.data.id);

            const newNodes = nodeGroups.enter().append('g')
                .attr('class', 'node-group')
                .attr('transform', d => `translate(${d.x},${d.y})`)
                .style('opacity', 0)
                .on('click', (event, d) => showPersonInfo(d.data));

            newNodes.merge(nodeGroups)
                .transition().duration(500)
                .attr('transform', d => `translate(${d.x},${d.y})`)
                .style('opacity', 1);

            nodeGroups.exit().transition().duration(500).style('opacity', 0).remove();

            newNodes.each(function(d) {
                const g = d3.select(this);
                g.append('rect').attr('x', -90).attr('y', -30).attr('width', 180).attr('height', 70).attr('rx', 8)
                    .attr('fill', d.data.gender === 'female' ? '#fdf2f8' : '#eff6ff')
                    .attr('stroke', d.data.gender === 'female' ? '#ec4899' : '#3b82f6').attr('stroke-width', 1.5);
                g.append('defs').append('clipPath').attr('id', `clip-${d.data.id}`)
                    .append('circle').attr('cx', -65).attr('cy', 5).attr('r', 22);
                g.append('image').attr('x', -87).attr('y', -17).attr('width', 44).attr('height', 44)
                    .attr('clip-path', `url(#clip-${d.data.id})`).attr('href', d.data.photo)
                    .attr('preserveAspectRatio', 'xMidYMid slice').on('error', function() { d3.select(this).remove(); });
                g.append('text').attr('x', -35).attr('y', -5).attr('font-size', '13px').attr('font-weight', 'bold').attr('fill', '#1e293b')
                    .text(d.data.name.length > 18 ? d.data.name.substring(0, 16) + '...' : d.data.name);
                g.append('text').attr('x', -35).attr('y', 13).attr('font-size', '11px').attr('fill', '#64748b')
                    .text(() => {
                        if (d.data.birth_date && d.data.death_date) return `${d.data.birth_date} - ${d.data.death_date}`;
                        if (d.data.birth_date) return `N. ${d.data.birth_date}`;
                        return '';
                    });
                g.append('text').attr('x', -35).attr('y', 30).attr('font-size', '11px').attr('fill', '#94a3b8')
                    .text(d.data.children_count > 0 ? `${d.data.children_count} hijos` : '');
                if (d.children && d.children.length > 0) {
                    g.append('circle').attr('cx', 80).attr('cy', 5).attr('r', 10)
                        .attr('fill', '#e2e8f0').attr('stroke', '#94a3b8').attr('stroke-width', 1)
                        .attr('cursor', 'pointer').on('click', (event, node) => { event.stopPropagation(); toggleNode(node); });
                    g.append('text').attr('x', 80).attr('y', 9).attr('text-anchor', 'middle').attr('font-size', '14px')
                        .attr('fill', '#475569').attr('cursor', 'pointer').text('−')
                        .on('click', (event, node) => { event.stopPropagation(); toggleNode(node); });
                }
            });
        }
    }

    function showPersonInfo(person) {
        const modal = document.getElementById('person-modal');
        const content = document.getElementById('modal-content');
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
                        ${person.birth_date ? '<span class="font-medium">Nacimiento:</span> ' + person.birth_date : ''}
                        ${person.death_date ? '<br><span class="font-medium">Fallecimiento:</span> ' + person.death_date : ''}
                    </p>
                    <p class="text-sm text-gray-600"><span class="font-medium">Género:</span> ${person.gender === 'male' ? 'Masculino' : person.gender === 'female' ? 'Femenino' : 'No especificado'}</p>
                    <p class="text-sm text-gray-600"><span class="font-medium">Hijos:</span> ${person.children_count || 0}</p>
                </div>
            </div>
            ${person.biography ? `<div class="mb-4"><h3 class="font-semibold text-gray-700 mb-1">Biografía</h3><p class="text-sm text-gray-600">${person.biography}</p></div>` : ''}
        `;
        modal.classList.remove('hidden');
        modal.classList.add('flex');
        modal.addEventListener('click', (e) => { if (e.target === modal) closeModal(); });
    }

    function closeModal() {
        const modal = document.getElementById('person-modal');
        modal.classList.add('hidden');
        modal.classList.remove('flex');
    }

    document.addEventListener('keydown', (e) => { if (e.key === 'Escape') closeModal(); });

    loadTree();
</script>
@endsection
