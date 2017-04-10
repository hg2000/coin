
/**
 * First we will load all of this project's JavaScript dependencies which
 * includes Vue and other libraries. It is a great starting point when
 * building robust, powerful web applications using Vue and Laravel.
 */

require('./bootstrap');
import VueRouter from 'vue-router';
import VueResource from 'vue-resource';

Vue.use(VueRouter);

const Volumes = Vue.component('volumes', require('./components/Volumes.vue'));
const History = Vue.component('history', require('./components/History.vue'));

const routes = [
  { path: '/', component: Volumes },
  { path: '/volumes', component: Volumes },
  { path: '/history', component: History }
]


const router = new VueRouter({
  routes
})

const app = new Vue({
    router,
    el: '#app'
});
