import { appendNodeCard } from './tree/builder.js';
import { refreshToggles } from './tree/toggle.js';
import { createUpdater } from './tree/updater.js';
import { setupZoom } from './zoom.js';
import { showPersonInfo, closeModal } from './modal.js';
import { exportSVG } from './export.js';
import {
    TREE_NODE_SIZE_X, TREE_NODE_SIZE_Y,
    TREE_SEPARATION_SIBLING, TREE_SEPARATION_COUSIN,
    ZOOM_MARGIN_X, ZOOM_MARGIN_Y, INITIAL_Y_OFFSET,
} from './tree/constants.js';

export function createTree(config) {
    const {
        apiUrl,
        translations,
        containerId = 'tree-container',
        modalId = 'person-modal',
        modalContentId = 'modal-content',
    } = config;

    let updater;

    async function loadTree() {
        try {
            const response = await fetch(apiUrl);
            const data = await response.json();
            if (!data) {
                document.getElementById(containerId).innerHTML =
                    `<div class="loading">${translations.noData}</div>`;
                return;
            }
            renderTree(data);
        } catch (error) {
            document.getElementById(containerId).innerHTML =
                `<div class="loading text-red-500">${translations.errorLoading}</div>`;
        }
    }

    function renderTree(data) {
        const container = document.getElementById(containerId);
        container.innerHTML = '';

        const width = container.clientWidth;
        const height = container.clientHeight;

        const svg = d3.select(`#${containerId}`)
            .append('svg')
            .attr('width', width)
            .attr('height', height);

        const g = svg.append('g');

        const treeLayout = d3.tree()
            .nodeSize([TREE_NODE_SIZE_X, TREE_NODE_SIZE_Y])
            .separation((a, b) => (
                a.parent === b.parent ? TREE_SEPARATION_SIBLING : TREE_SEPARATION_COUSIN
            ));

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
        const scale = Math.min(
            width / (treeWidth + ZOOM_MARGIN_X),
            height / (treeHeight + ZOOM_MARGIN_Y),
            1
        );
        const initialX = (width - treeWidth * scale) / 2 - treeBounds.minX * scale;
        const initialY = INITIAL_Y_OFFSET;

        setupZoom(svg, g, initialX, initialY);

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

        const nodesContainer = g.append('g').attr('class', 'nodes-container');
        const nodes = nodesContainer
            .selectAll('g')
            .data(root.descendants())
            .enter()
            .append('g')
            .attr('class', 'node-group')
            .attr('transform', d => `translate(${d.x},${d.y})`);

        appendNodeCard(nodes, translations);
        refreshToggles(nodes);

        updater = createUpdater({ g, nodesContainer, root, treeLayout }, translations);

        g.on('click', (event) => {
            const target = d3.select(event.target);
            const nodeGroup = d3.select(event.target.closest('g.node-group'));
            if (nodeGroup.empty()) return;
            const datum = nodeGroup.datum();
            if (!datum) return;

            if (target.classed('toggle-btn') || target.classed('toggle-symbol')) {
                updater.toggleNode(datum);
            } else {
                showPersonInfo(datum.data, translations, modalId, modalContentId);
            }
        });
    }

    window.closeModal = () => closeModal(modalId);
    window.exportSVG = () => exportSVG(containerId);

    document.addEventListener('keydown', (e) => {
        if (e.key === 'Escape') closeModal(modalId);
    });

    loadTree();
}

window.createTree = createTree;

if (window.__TREE_CONFIG) {
    createTree(window.__TREE_CONFIG);
}
