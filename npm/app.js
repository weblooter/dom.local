import Vue from 'vue';

import Vuetify from 'vuetify';
import 'vuetify/dist/vuetify.min.css';

Vue.use(Vuetify);
const vuetify = new Vuetify({});

import Vuex     from 'vuex';

Vue.use(Vuex);
import currency from './store/currency';

const store = new Vuex.Store({
  modules: {
    currency
  }
});

import CurrencyCalc from './components/CurrencyCalc';

document.addEventListener('DOMContentLoaded', () => {
  let app = new Vue({
    el: '#currencycalc',
    components: {
      CurrencyCalc
    },
    vuetify,
    store
  });
});