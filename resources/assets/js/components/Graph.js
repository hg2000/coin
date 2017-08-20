export default {
  template: '<div><canvas :id="elementId" class="chart-canvas" width="200" height="100" style="width: 200px; height: 100px"></canvas></div>',
  props: ['title', 'elementId', 'rates', 'currency', 'target'],

  mounted() {
    var dates = [];
    this.rates.forEach(function(item) {
      dates.push(item.date);

    });

    var values = [];
    var currency = this.currency;
    var target = this.target;
    this.rates.forEach(function(item) {

      if (typeof item['rates'][currency] != 'undefined') {
        values.push(item['rates'][currency][target]);
      }
    });

    var ctx = document.getElementById(this.elementId)
      .getContext('2d');
    var myChart = new Chart(ctx, {
      type: 'line',
      data: {
        labels: dates,
        datasets: [{
          data: values,
          borderColor: "#3e95cd",
          fill: true
        }]
      },
      options: {
        legend: {
          display: false
        },
        title: {
         display: true,
         text: this.title
       }
      }
    });
  }
}
