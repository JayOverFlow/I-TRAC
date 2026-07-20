// import jquery from 'jquery';
// window.$ = window.jQuery = jquery;
import '../css/app.css'; // This line makes Vite process the app.css file
import * as bootstrap from 'bootstrap';
window.bootstrap = bootstrap;

import Swal from 'sweetalert2';
window.Swal = Swal;

import PerfectScrollbar from 'perfect-scrollbar';
window.PerfectScrollbar = PerfectScrollbar;

import './app/loader.js';
// import './app/perfect-scrollbar.min.js';
import './app/mousetrap.min.js';
import './app/waves.min.js';
import './app/theme.js';
import './app/app.js';