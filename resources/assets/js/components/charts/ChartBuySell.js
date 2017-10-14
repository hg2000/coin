export default {
  template: '<div :id="elementId" class="morris-bar-chart"></div>',
  props: ['elementId', 'buyVolume', 'sellVolume', 'currencyLabel'],

  mounted() {

    var values = [{
        y: 'Buy Volume',
        a: formatFiat(this.buyVolume),
      },
      {
        y: 'Sell volume',
        a : formatFiat(this.sellVolume),
      }
    ];

    window.morrisPortfolioCurrency = Morris.Bar({
      element: this.elementId,
      data: values,
      xkey: 'y',
      ykeys: ['a'],
      labels: [this.currencyLabel]
    });

    function formatFiat(n) {
      return numeral(n)
        .format('0.00') + " ";
    }
  }


}
