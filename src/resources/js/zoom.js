import {
    ZOOM_MIN, ZOOM_MAX,
    ZOOM_STEP_IN, ZOOM_STEP_OUT,
    ZOOM_DURATION, ZOOM_RESET_DURATION,
} from './tree/constants.js';

export function setupZoom(svg, g, initialX, initialY) {
    const zoom = d3.zoom()
        .scaleExtent([ZOOM_MIN, ZOOM_MAX])
        .on('zoom', (event) => {
            g.attr('transform', event.transform);
        });

    svg.call(zoom);
    svg.call(zoom.transform, d3.zoomIdentity.translate(initialX, initialY).scale(1));

    window.zoomIn = () => {
        svg.transition().duration(ZOOM_DURATION).call(zoom.scaleBy, ZOOM_STEP_IN);
    };
    window.zoomOut = () => {
        svg.transition().duration(ZOOM_DURATION).call(zoom.scaleBy, ZOOM_STEP_OUT);
    };
    window.resetZoom = () => {
        svg.transition().duration(ZOOM_RESET_DURATION).call(
            zoom.transform,
            d3.zoomIdentity.translate(initialX, initialY).scale(1)
        );
    };

    return zoom;
}
