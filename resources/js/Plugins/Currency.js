import { createCurrencyInput } from 'vue-currency-input'

export default {
  install(app) {
    const options = {
      globalOptions: {
        currency: 'IDR',
        locale: 'id-ID',
        currencyDisplay: 'hidden', // tidak tampil Rp di input
        precision: 0,              // tanpa desimal
        valueAsInteger: true,      // hasil v-model = angka murni
        allowNegative: false
      }
    }
    app.use(createCurrencyInput(options))
  }
}
