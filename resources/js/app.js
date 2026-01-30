// import jquery from 'jquery';
// window.$ = window.jQuery = jquery;
import '../css/app.css'; // This line makes Vite process the app.css file
// import './bootstrap/bootstrap.bundle.min.js';
import 'bootstrap/dist/js/bootstrap.bundle.min.js';
import * as bootstrap from 'bootstrap';
window.bootstrap = bootstrap;
import './app/loader.js';
import './app/perfect-scrollbar.min.js';
import './app/mousetrap.min.js';
import './app/waves.min.js';
import './app/app.js';