export default {
  methods: {
    formatFiat: function(n) {
      return numeral(n).format('0,0.00') + " " + this.fiatsymbol;
    },
    formatCoin: function(n) {
      return numeral(n).format('0,0.0000');
    },
    formatPercent: function(n) {
      return numeral(n).format('0.00') + " %";
    }
  }
}
