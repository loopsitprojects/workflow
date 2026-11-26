import './bootstrap';
import Chart from 'chart.js/auto';
import { marked } from 'marked';

marked.setOptions({
    gfm: true,
    breaks: true,
});

window.Chart = Chart;
window.marked = marked;
