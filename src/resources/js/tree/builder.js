import {
    NODE_WIDTH, NODE_HEIGHT, NODE_RX, NODE_X, NODE_Y,
    PHOTO_X, PHOTO_Y, PHOTO_SIZE, PHOTO_CX, PHOTO_CY, PHOTO_R,
    TEXT_X_NAME, TEXT_Y_NAME, TEXT_X_DATES, TEXT_Y_DATES,
    TEXT_X_CHILDREN, TEXT_Y_CHILDREN,
    TEXT_FONT_SIZE_NAME, TEXT_FONT_SIZE_DATES, TEXT_FONT_SIZE_CHILDREN,
    TEXT_COLOR_NAME, TEXT_COLOR_DATES, TEXT_COLOR_CHILDREN,
    MAX_NAME_LENGTH, NAME_TRUNCATE_SUFFIX,
    COLORS, STROKES, PHOTO_BG,
} from './constants.js';

export function appendNodeCard(selection, translations) {
    selection.append('rect')
        .attr('x', NODE_X)
        .attr('y', NODE_Y)
        .attr('width', NODE_WIDTH)
        .attr('height', NODE_HEIGHT)
        .attr('rx', NODE_RX)
        .attr('fill', d => COLORS[d.data.gender] || COLORS.default)
        .attr('stroke', d => STROKES[d.data.gender] || STROKES.default)
        .attr('stroke-width', 1.5)
        .attr('class', 'node-card')
        .attr('data-gender', d => d.data.gender || 'unknown');

    selection.append('defs')
        .append('clipPath')
        .attr('id', d => `clip-${d.data.id}`)
        .append('circle')
        .attr('cx', PHOTO_CX)
        .attr('cy', PHOTO_CY)
        .attr('r', PHOTO_R);

    selection.append('image')
        .attr('x', PHOTO_X)
        .attr('y', PHOTO_Y)
        .attr('width', PHOTO_SIZE)
        .attr('height', PHOTO_SIZE)
        .attr('clip-path', d => `url(#clip-${d.data.id})`)
        .attr('href', d => d.data.photo)
        .attr('preserveAspectRatio', 'xMidYMid slice')
        .on('error', function () { this.remove(); });

    selection.append('circle')
        .attr('cx', PHOTO_CX)
        .attr('cy', PHOTO_CY)
        .attr('r', PHOTO_R)
        .attr('fill', d => PHOTO_BG[d.data.gender] || PHOTO_BG.default)
        .attr('opacity', 0);

    selection.append('text')
        .attr('x', TEXT_X_NAME)
        .attr('y', TEXT_Y_NAME)
        .attr('font-size', TEXT_FONT_SIZE_NAME)
        .attr('font-weight', 'bold')
        .attr('fill', TEXT_COLOR_NAME)
        .text(d => d.data.name.length > MAX_NAME_LENGTH
            ? d.data.name.substring(0, MAX_NAME_LENGTH - 2) + NAME_TRUNCATE_SUFFIX
            : d.data.name);

    selection.append('text')
        .attr('x', TEXT_X_DATES)
        .attr('y', TEXT_Y_DATES)
        .attr('font-size', TEXT_FONT_SIZE_DATES)
        .attr('fill', TEXT_COLOR_DATES)
        .text(d => {
            if (d.data.birth_date && d.data.death_date) {
                return `${d.data.birth_date} - ${d.data.death_date}`;
            }
            if (d.data.birth_date) {
                return `${translations.n} ${d.data.birth_date}`;
            }
            return '';
        });

    selection.append('text')
        .attr('x', TEXT_X_CHILDREN)
        .attr('y', TEXT_Y_CHILDREN)
        .attr('font-size', TEXT_FONT_SIZE_CHILDREN)
        .attr('fill', TEXT_COLOR_CHILDREN)
        .text(d => d.data.children_count > 0
            ? `${d.data.children_count} ${translations.children}`
            : '');
}
