/**
 * First we will load all of this project's JavaScript dependencies which
 * includes Vue and other libraries. It is a great starting point when
 * building robust, powerful web applications using Vue and Laravel.
 */

require('./bootstrap');
require('./VueCsvImport');
import VueRouter from 'vue-router'



window.Vue = require('vue');
//import Vue from 'vue'
//import VueRouter from 'vue-router'

//Vue.use(VueRouter)


/**
 * Next, we will create a fresh Vue application instance and attach it to
 * the page. Then, you may begin adding components to this application
 * or customize the JavaScript scaffolding to fit your unique needs.
 */
Vue.component('upload', require('./components/upload'));
Vue.component('vuecsvimport', require('./components/VueCsvImport'));
/*Vue.component('todo', require('./components/Todo'))
Vue.component('new-todo', require('./components/NewTodo.vue'))
Vue.component('todo-list', require('./components/TodoList'))
Vue.component('todo-app', require('./components/TodoApp'))
Vue.component('Tasks', require('./components/Tasks'))*/
Vue.component('dbnames', require('./components/dbnames'))


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
