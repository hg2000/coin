<template>
<div>

  <div class="container-fluid">
    <div class="row">
      <div class="col-xs-12">

        <div class="panel panel-default" v-if="balances != 0">
          <div class="panel-heading">Revenue</div>
          <div class="panel-body">

            <div class="table-responsive">
              <table class="table table-striped table-responsive table-hover">
                <thead>
                  <tr>
                    <th></th>
                    <th>BTC</th>
                    <th>{{ fiat }}</th>
                  </tr>
                </thead>
                <tbody>
                  <tr>
                    <th>BTC/{{ fiat }} Trade Revenue</th>
                    <td>{{ formatCoin(sum.tradingRevenueBtc) }} B</td>
                    <td>{{ formatFiat(sum.tradingRevenueFiat) }}</td>
                  </tr>
                  <tr>
                    <th>Buy Volume</th>
                    <td>{{ formatCoin(sum.buyVolumeBtc) }} B</td>
                    <td>{{ formatFiat(sum.buyVolumeFiat) }}</td>
                  </tr>
                  <tr>
                    <th>Sell Volume</th>
                    <td>{{ formatCoin(sum.sellVolumeBtc) }} B</td>
                    <td>{{ formatFiat(sum.sellVolumeFiat) }}</td>
                  </tr>
                  <tr>
                    <th>Current Coin Value</th>
                    <td>{{ formatCoin(sum.currentValueBtc) }} B</td>
                    <td>{{ formatFiat(sum.currentValueFiat) }}</td>
                  </tr>
                  <tr class="info">
                    <th>Total Revenue</th>
                    <td>{{ formatCoin(sum.totalRevenueBtc) }} B</td>
                    <td>{{ formatFiat(sum.totalRevenueFiat) }}</td>
                  </tr>
                </tbody>
              </table>
            </div>
            <!--end of .table-responsive-->
          </div>
        </div>
        <!-- end of panel -->

        <div v-if="error == 0">
          <div v-if="balances == 0" class="alert alert-warning">
            <p>
              Please Wait
            </p>
          </div>
        </div>
        <div v-if="error != 0" class="alert alert-danger">
          <p>
            {{error}}
          </p>
        </div>

      </div>
    </div>
  </div>
</div>
</template>

<script>
export default {
  props: [
    'fiatsymbol',
    'fiat'
  ],
  data: function() {
    return {
      balances: 0,
      sum: 0,
      error: 0,
      balances: 0
    }
  },
  methods: {
    makeRequest: function() {
      this.$http.get('/api/portfolio/', {
        headers: {
          'Accept': 'application/json'
        }
      }).then(response => {
        this.balances = response.body.balances;
        this.sum = response.body.sum;
      }, response => {
        this.error = response.body;
      });

    },
    formatFiat: function(n) {
      return numeral(n).format('0,0.00') + " " + this.fiatsymbol;
    },
    formatCoin: function(n) {
      return numeral(n).format('0,0.0000');
    },
    formatPercent: function(n) {
      return numeral(n).format('0.00') + " %";
    },

  },
  mounted() {
    console.log(1);
    this.makeRequest();
  }
}
</script>
