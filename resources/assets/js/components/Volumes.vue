<template>
<div class="container-fluid">

  <div class="row">
    <div class="col-md-12">
      <div class="panel panel-default" v-if="balances != 0">
        <div class="panel-heading">Coin Volume Status</div>

        <div class="panel-body">
          <p>
            <table class="table table-striped table-responsive table-hover">
              <thead>
                <tr>
                  <th class="border-right">Currency</th>
                  <th>Volume</th>
                  <th>Current Rate (Coin/BTC)</th>
                  <th class="border-right">Current Rate (Coin/{{ fiat }})</th>


                  <th>Avg purchase rate(Coin/{{ fiat }})</th>
                  <th>Avg purchase rate(BTC/COIN)</th>

                  <th>Purchase Value (BTC)</td>
                    <th>Current Value (BTC)</th>
                    <th class="border-right">Revenue (BTC)</th>


                    <th>Purchase value ({{ fiat}})</th>
                    <th>Current Value ({{ fiat }})</th>
                    <th>Revenue ({{ fiat }})</th>

                </tr>
              </thead>

              <tbody>
                <tr v-for="item in balances">
                  <td class="border-right">{{ item.currency }}</td>
                  <td>{{ formatCoin(item.volume) }}</td>
                  <td>{{ formatCoin(item.currentRateBtc) }}</td>
                  <td class="border-right">{{ formatFiat(item.currentRateFiat) }}</td>

                  <td>{{ formatFiat(item.averagePurchaseRateCoinFiat) }}</td>
                  <td class="border-right">{{ formatCoin(item.averageBuyRateBtcCoin) }}</td>

                  <td>{{ formatCoin(item.purchaseValueBtc) }}</td>
                  <td>{{ formatCoin(item.currentValueBtc) }}</td>
                  <td class="border-right">{{ formatCoin(item.revenueBTC) }}</td>

                  <td>{{ formatFiat(item.purchaseValueFiat) }}</td>
                  <td>{{ formatFiat(item.currentValueFiat) }}</td>
                  <td>{{ formatFiat(item.revenueFiat) }}</td>

                </tr>
              </tbody>
              <tbody>
                <tr class="info">
                  <td></td>
                  <td></td>
                  <td></td>
                  <td></td>
                  <td></td>
                  <td></td>
                  <td></td>
                  <td></td>
                  <td></td>
                  <td></td>
                  <td>{{ formatFiat(sum.currenValueFiat) }}</td>
                  <td>{{ formatFiat(sum.currentRevenueFiat) }}</td>
                </tr>
              </tbody>

            </table>

          </p>
        </div>
      </div>
    </div>
    <div v-if="error == 0">
      <div v-if="sum == 0" class="alert alert-warning">
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



  <div class="row">
    <div class="col-md-12">
      <div class="panel panel-default" v-if="sum != 0">
        <div class="panel-heading">Balance</div>

        <div class="panel-body">
          <p>
            <table class="table">
              <thead>
                <tr>
                  <th></th>
                  <th>BTC</th>
                  <th>{{ fiat }}</th>
                </tr>
              </thead>
              <tbody>
                <tr>
                  <th>BTC/{{ fiat }} Trades</th>
                  <td>{{ formatCoin(sum.tradingRevenueBtc) }} B</td>
                  <td>{{ formatFiat(sum.tradingRevenueFiat) }}</td>
                </tr>
                <tr>
                  <th>Current Coin Value</th>
                  <td>{{ formatCoin(sum.currenValueBtc) }} B</td>
                  <td>{{ formatFiat(sum.currenValueFiat) }}</td>
                </tr>
                <tr class="info">
                  <th>Total Revenue</th>
                  <td>{{ formatCoin(sum.totalRevenueBtc) }} B</td>
                  <td>{{ formatFiat(sum.totalRevenueFiat) }}</td>
                </tr>
              </tbody>
            </table>

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
      error: 0
    }
  },
  methods: {
    makeRequest: function() {
      this.$http.get('/api/volumes/').then(response => {
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

  },
  mounted() {
    this.makeRequest();
  }
}
</script>
