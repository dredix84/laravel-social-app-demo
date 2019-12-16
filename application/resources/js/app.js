/**
 * First we will load all of this project's JavaScript dependencies which
 * includes Vue and other libraries. It is a great starting point when
 * building robust, powerful web applications using Vue and Laravel.
 */

require('./bootstrap');

window.Vue = require('vue');
import VueRouter from 'vue-router';
import BootstrapVue from 'bootstrap-vue';
import { Form, HasError, AlertError } from 'vform';
import VueSweetalert2 from 'vue-sweetalert2';
window.Form = Form;


Vue.use(VueRouter);
Vue.use(BootstrapVue);
Vue.component(HasError.name, HasError);
Vue.component(AlertError.name, AlertError);
Vue.use(require('vue-moment'));
Vue.use(VueSweetalert2);


const routes = [
    {path: '/posts', component: require('./components/posts').default},
    {path: '/profile', component: require('./components/profile').default}
];

const router = new VueRouter({
    routes
});

/**
 * The following block of code may be used to automatically register your
 * Vue components. It will recursively scan this directory for the Vue
 * components and automatically register them with their "basename".
 *
 * Eg. ./components/ExampleComponent.vue -> <example-component></example-component>
 */

// const files = require.context('./', true, /\.vue$/i)
// files.keys().map(key => Vue.component(key.split('/').pop().split('.')[0], files(key).default))

Vue.component('example-component', require('./components/ExampleComponent.vue').default);

/**
 * Next, we will create a fresh Vue application instance and attach it to
 * the page. Then, you may begin adding components to this application
 * or customize the JavaScript scaffolding to fit your unique needs.
 */

const app = new Vue({
    el: '#app',
    router,
    data: {
        searchTerm: ''
    }
});
