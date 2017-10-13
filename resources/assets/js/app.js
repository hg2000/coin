require('./bootstrap');

import VueRouter from 'vue-router';

window.Vue = Vue

Vue.use(VueRouter);
Vue.use(require('vue-resource'))

const Revenue = Vue.component('revenue', require('./components/Revenue.vue'));
const Portfolio = Vue.component('portfolio', require('./components/Portfolio.vue'));
const History = Vue.component('history', require('./components/History.vue'));
const Clear = Vue.component('clear', require('./components/Clear.vue'));

const routes = [
  { path: '/', component: Revenue },
  { path: '/revenue', component: Revenue },
  { path: '/portfolio', component: Portfolio },
  { path: '/history', component: History },
  { path: '/clear', component: Clear },
]


const router = new VueRouter({
  routes
})

const app = new Vue({
    router,
    el: '#app'
});
