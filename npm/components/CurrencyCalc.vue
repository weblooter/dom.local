<template lang="pug">
  div
    v-app
      v-container
        v-row
          v-col(cols="12" md="6").mb-3.grey--text.text--darken-4
            v-form
              v-container
                v-row
                  v-col
                    v-text-field(label="Обменять" v-model="iPrice" color="blue-grey" :rules="[rules.number, rules.required]" )
                v-row
                  v-col
                    v-select(label="Из" color="blue-grey" v-model="sFrom" :items="aCurrencyItemsFrom" :rules="[rules.required]" )
                  v-col.text-center.align-self-center
                    v-btn(icon color="blue-grey" @click="replaceCurrencies")
                      v-icon mdi-find-replace
                  v-col.text-right
                    v-select(label="В" color="blue-grey" v-model="sTo" :items="aCurrencyItemsTo" :rules="[rules.required]")
          v-col(cols="12" md="6").mb-3
            template(v-if="isFormValid")
              template(v-if="isMatrixValid")
                v-card(flat)
                  v-toolbar(flat dense color="blue-grey" dark)
                    v-toolbar-title.text-body-1 Вы получите
                  v-card-text.grey--text.text--darken-4
                    div.text-h6 {{calcedSum}}
                    div.text-caption {{currentCurrencyRate}}
              template(v-else)
                div.error.white--text.pa-3.d-flex.align-center.justify-start
                  v-icon(color="white" ) mdi-cellphone-information
                  | Невозможно купить данную валюту
            template(v-else)
              div.error.white--text.pa-3.d-flex.align-center.justify-start
                v-icon(color="white" ) mdi-cellphone-information
                | Заполните все значения
</template>

<script>
  import {mapGetters} from 'vuex';

  export default {
    name: 'CurrencyCalc',
    data() {
      return {
        iPrice: '1',
        sFrom: '',
        sTo: '',

        rules: {
          required: v => !!v || 'Поле обязательно',
          number: v => (/^([\d]+)$/.test(v) || 'Введите число')
        }
      };
    },
    methods: {
      replaceCurrencies() {
        let sFrom = this.sFrom;
        this.sFrom = this.sTo;
        this.sTo = sFrom;
      },
      getMiddleCurrency() {
        let sMiddleCurrency = false;
        [].forEach.call(Object.keys(this.oCurrencyMatrix?.[this.sFrom]), (k) => {
          if (this.oCurrencyMatrix?.[k]?.[this.sTo] !== undefined) {
            sMiddleCurrency = k;
          }
        });

        return sMiddleCurrency;
      }
    },
    computed: {
      ...mapGetters([
        'oCurrencyMatrix'
      ]),

      /**
       *
       * @return {Object}
       */
      aCurrencyItemsFrom() {
        let oRet = [];
        [].forEach.call(Object.keys(this.oCurrencyMatrix), (v) => {
          oRet.push({
            text: v,
            value: v,
            disabled: (v === this.sTo)
          });
        });
        return oRet;
      },

      /**
       *
       * @return {Object}
       */
      aCurrencyItemsTo() {
        let oRet = [];
        [].forEach.call(Object.keys(this.oCurrencyMatrix), (v) => {
          oRet.push({
            text: v,
            value: v,
            disabled: (v === this.sFrom)
          });
        });

        oRet.push({
          text: 'Битая валюта',
          value: 'ZZZ',
          disabled: false
        });

        return oRet;
      },

      /**
       *
       * @returns {boolean}
       */
      isFormValid() {
        return (
            this.rules.required(this.iPrice) === true
            && this.rules.number(this.iPrice) === true
            && this.rules.required(this.sFrom) === true
            && this.rules.required(this.sTo) === true
        );
      },
      /**
       * @return {boolean}
       */
      isMatrixValid() {
        return this.isMatrixDirection || this.isMatrixTopic;
      },
      /**
       * @return {boolean}
       */
      isMatrixDirection() {
        return this.oCurrencyMatrix?.[this.sFrom]?.[this.sTo] !== undefined;
      },
      /**
       * @return {boolean}
       */
      isMatrixTopic() {
        return this.getMiddleCurrency() !== false;
      },

      /**
       *
       * @return {string}
       */
      calcedSum() {
        if (this.isMatrixDirection) {
          return (+this.oCurrencyMatrix?.[this.sFrom]?.[this.sTo] * +this.iPrice).toFixed(2) + ' ' + this.sTo;
        }
        else if (this.isMatrixTopic) {
          let sMiddleCurrency = this.getMiddleCurrency(),
              sRet = (+(this.oCurrencyMatrix?.[this.sFrom]?.[sMiddleCurrency] * +this.iPrice) *
                  this.oCurrencyMatrix?.[sMiddleCurrency]?.[this.sTo]).toFixed(2);
          return sRet + ' ' + this.sTo;
        }
      },

      /**
       *
       * @return {string}
       */
      currentCurrencyRate() {
        if (this.isMatrixDirection) {
          return '1 ' + this.sFrom + ' = ' + (+this.oCurrencyMatrix?.[this.sFrom]?.[this.sTo].toFixed(4)) + ' ' +
              this.sTo;
        }
        else if (this.isMatrixTopic) {
          let sMiddleCurrency = this.getMiddleCurrency();
          return '1 ' + this.sFrom + ' = ' + (+this.oCurrencyMatrix?.[this.sFrom]?.[sMiddleCurrency].toFixed(4)) + ' ' +
              sMiddleCurrency + ' = ' + (+this.oCurrencyMatrix?.[sMiddleCurrency]?.[this.sTo] *
                  (+this.oCurrencyMatrix?.[this.sFrom]?.[sMiddleCurrency].toFixed(4))).toFixed(4) + ' ' +
              this.sTo;
        }
      }
    },
    created() {
      if (document.querySelectorAll('#currencyCalcData').length > 0) {
        this.$store.dispatch('setCurrencyMatrix', JSON.parse(document.querySelector('#currencyCalcData').textContent));
      }
    }
  };
</script>

<style scoped>

</style>