
export default {
  template: '<div :id="elementId" class="morris-chart" style="height: 35px;"></div>',
  props: ['title', 'elementId', 'rates', 'currency', 'target', 'width', 'height'],

  mounted() {
    var dates = [];
    this.rates.forEach(function(item) {
      dates.push(item.date);
    });

    var values = [];
    var currency = this.currency;
    var target = this.target;
    var minValue = 99999999999999999999;

    this.rates.forEach(function(item) {
      if (typeof item['rates'][currency] != 'undefined') {
        var sample = {};
        sample.period = item['date'];
        sample.currency = item['rates'][currency][target];
        if (currency == 'BTC') {
          sample.currency = formatFiat(sample.currency);
        } else {
          sample.currency = formatCoin(sample.currency);
        }
        if (sample.currency < minValue) {
          minValue = sample.currency;
        }
        values.push(sample);
      }
    });

    minValue = minValue - (minValue / 100 * 10);

    window.morrisPortfolioCurrency = Morris.Area({
      element: this.elementId,
      padding: 5,
      behaveLikeLine: true,
      grid: false,
      axes: false,
      resize: false,
      smooth: false,
      pointSize: 1,
      lineWidth: 1,
      fillOpacity: 0.4,
      ymin: minValue,
      data: values,

      lineColors: ['#45B29D'],
      xkey: 'period',
      redraw: true,
      ykeys: ['currency'],
      labels: ['Avg. Day Rate'],
      hideHover: 'auto'

    });

    function formatCoin(n) {
      return numeral(n).format('0.0000');
    }
    function formatFiat(n) {
      return numeral(n).format('0.00') + " ";
    }
  }


}
