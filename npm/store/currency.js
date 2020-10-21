export default {
  state: {
    oCurrencyMatrix: {}
  },
  mutations: {
    setCurrencyMatrix(state, payload) {
      state.oCurrencyMatrix = payload;
    }
  },
  actions: {
    setCurrencyMatrix({commit}, payload) {
      if (payload instanceof Object) {
        commit('setCurrencyMatrix', payload);
      }
    }
  },
  getters: {
    oCurrencyMatrix(state) {
      return state.oCurrencyMatrix;
    }
  }
};