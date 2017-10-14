export default {
  template: '<div :id="elementId" class="morris-bar-chart"></div>',
  props: ['elementId',  'currentValue', 'tradingRevenue'],

  mounted() {

    window.morrisPortfolioCurrency = Morris.Donut({
      element: this.elementId,
      data: [
        {label: "Trading Revenue", value: formatFiat(this.tradingRevenue) },
        {label: "Current Coin Value", value: formatFiat(this.currentValue) },
      ]

    });

    function formatFiat(n) {
      return numeral(n)
        .format('0.00') + " ";
    }
  }


}
