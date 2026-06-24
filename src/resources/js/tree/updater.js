import {
    LINK_DURATION, NODE_DURATION,
} from './constants.js';
import { appendNodeCard } from './builder.js';
import { refreshToggles } from './toggle.js';

export function createUpdater({ g, nodesContainer, root, treeLayout }, translations) {
    function toggleNode(d) {
        if (d.children) {
            d._children = d.children;
            d.children = null;
        } else {
            d.children = d._children;
        }
        update(root);
    }

    function update() {
        treeLayout(root);

        const links = g.selectAll('path.link')
            .data(root.links(), d => `${d.source.data.id}-${d.target.data.id}`);

        links.exit().remove();

        links.enter()
            .append('path')
            .attr('class', 'link')
            .merge(links)
            .transition()
            .duration(LINK_DURATION)
            .attr('d', d3.linkVertical()
                .x(d => d.x)
                .y(d => d.y)
            );

        const nodeGroups = nodesContainer.selectAll('g.node-group')
            .data(root.descendants(), d => d.data.id);

        nodeGroups.exit().remove();

        const newNodes = nodeGroups.enter()
            .append('g')
            .attr('class', 'node-group')
            .attr('transform', d => `translate(${d.x},${d.y})`)
            .style('opacity', 0);

        appendNodeCard(newNodes, translations);

        newNodes.merge(nodeGroups)
            .transition()
            .duration(NODE_DURATION)
            .attr('transform', d => `translate(${d.x},${d.y})`)
            .style('opacity', 1);

        refreshToggles(nodesContainer.selectAll('g.node-group'));
    }

    return { toggleNode, update };
}
