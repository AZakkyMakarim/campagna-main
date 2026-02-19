import './bootstrap';
import $ from 'jquery'
window.$ = $
window.$ = window.jQuery = $

import 'select2/dist/css/select2.css';
import select2 from 'select2';
select2(window.$);

import 'dropify/dist/js/dropify.min.js'

import '@fortawesome/fontawesome-free/css/all.min.css'

import Swal from 'sweetalert2'
window.Swal = Swal

import './echo';

import * as FilePond from 'filepond'
import FilePondPluginImagePreview from 'filepond-plugin-image-preview'
import FilePondPluginFileValidateType from 'filepond-plugin-file-validate-type';
import FilePondPluginFileValidateSize from 'filepond-plugin-file-validate-size';

FilePond.registerPlugin(
    FilePondPluginFileValidateType,
    FilePondPluginFileValidateSize,
    FilePondPluginImagePreview
)

window.FilePond = FilePond;

import Alpine from 'alpinejs'
window.Alpine = Alpine
Alpine.start()

