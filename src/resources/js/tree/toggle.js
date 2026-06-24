import {
    TOGGLE_CX, TOGGLE_CY, TOGGLE_R, TOGGLE_TEXT_Y_OFFSET,
    TOGGLE_FILL, TOGGLE_STROKE, TOGGLE_STROKE_WIDTH,
    TOGGLE_TEXT_FILL, TOGGLE_FONT_SIZE,
} from './constants.js';

export function refreshToggles(selection) {
    selection.each(function (d) {
        const g = d3.select(this);

        g.selectAll('.toggle-btn, .toggle-symbol').remove();

        const hasKids = (d.children && d.children.length > 0)
            || (d._children && d._children.length > 0);
        if (!hasKids) return;

        g.append('circle')
            .attr('class', 'toggle-btn')
            .attr('cx', TOGGLE_CX)
            .attr('cy', TOGGLE_CY)
            .attr('r', TOGGLE_R)
            .attr('fill', TOGGLE_FILL)
            .attr('stroke', TOGGLE_STROKE)
            .attr('stroke-width', TOGGLE_STROKE_WIDTH)
            .attr('cursor', 'pointer');

        g.append('text')
            .attr('class', 'toggle-symbol')
            .attr('x', TOGGLE_CX)
            .attr('y', TOGGLE_CY + TOGGLE_TEXT_Y_OFFSET)
            .attr('text-anchor', 'middle')
            .attr('font-size', TOGGLE_FONT_SIZE)
            .attr('fill', TOGGLE_TEXT_FILL)
            .attr('cursor', 'pointer')
            .text(d.children ? '−' : '+');
    });
}
