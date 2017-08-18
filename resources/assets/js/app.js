
/**
 * First we will load all of this project's JavaScript dependencies which
 * includes Vue and other libraries. It is a great starting point when
 * building robust, powerful web applications using Vue and Laravel.
 */

require('./bootstrap');
import VueRouter from 'vue-router';
window.Vue = Vue

Vue.use(VueRouter);
Vue.use(require('vue-resource'))

const Portfolio = Vue.component('portfolio', require('./components/Portfolio.vue'));
const History = Vue.component('history', require('./components/History.vue'));
const Coin = Vue.component('coin', require('./components/Coin.vue'));
const Clear = Vue.component('coin', require('./components/Clear.vue'));


const routes = [
  { path: '/', component: Portfolio },
  { path: '/portfolio', component: Portfolio },
  { path: '/history', component: History },
  { path: '/coin/:id', component: Coin },
  { path: '/clear', component: Clear },
]


const router = new VueRouter({
  routes
})

const app = new Vue({
    router,
    el: '#app'
});
