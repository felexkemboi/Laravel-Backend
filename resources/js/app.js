
require('./VueCsvImport');
import VueRouter from 'vue-router'

window.Vue = require('vue');
window.axios = require('axios');
window._ = require('lodash');
window.Popper = require('popper.js').default;

Vue.component('vuecsvimport', require('./components/VueCsvImport'));


import store from '../js/store'
import { VueCsvImportPlugin } from "./VueCsvImport";
Vue.config.silent = true


import VueFormWizard from 'vue-form-wizard'
import 'vue-form-wizard/dist/vue-form-wizard.min.css'
import  'vue-themify-icons';

Vue.use(VueFormWizard)
Vue.use(VueRouter)
Vue.use(VueCsvImportPlugin);

const app = new Vue({
    el: '#app',
    store
});
