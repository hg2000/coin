<template>
<div class="container-fluid">
  <div class="row">
    <div class="col-md-12">
      <div class="panel panel-default" v-if="trades != 0">
        <div class="panel-heading">Trade History</div>

        <div class="panel-body">
          <p>
            <table class="table table-striped table-responsive table-hover">
              <thead>
                <tr>
                  <th>Date</th>
                  <th>platformId</th>
                  <th>Type</th>
                  <th>Currency Pair</th>
                  <th>Rate (COIN/BTC)</th>
                  <th>Volume Coin</th>
                  <th>Value (BTC)</th>
                  <th>Value ({{ fiat }})</th>
                  <th>Purchase Rate ({{ fiat }}/BTC)</th>
                </tr>
              </thead>
              <tbody>
                <tr v-for="trade in trades">
                  <td>{{ trade.date }}</td>
                  <td>{{ trade.platform_id }}</td>
                  <td>{{ trade.type}}</td>
                  <td>{{ trade.source_currency}}{{ trade.target_currency}}</td>
                  <td>{{ formatCoin(trade.rate) }}</td>
                  <td>{{ formatCoin(trade.volume) }}</td>
                  <td>{{ formatCoin(trade.value_btc) }}</td>
                  <td>{{ formatFiat(trade.value_fiat) }}</td>
                  <td>{{ formatFiat(trade.purchase_rate_fiat_btc) }}</td>
                </tr>
              </tbody>
            </table>
          </p>
        </div>
      </div>
      <div v-if="error == 0">
        <div v-if="trades == 0" class="alert alert-warning">
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
</template>

<script>
export default {
  props: [
    'fiatsymbol',
    'fiat'
  ],
  data: function() {
    return {
      trades: 0,
      error: 0
    }
  },
  methods: {
    makeRequest: function() {

      this.$http.get('/api/trade_history/').then(response => {
        this.trades = response.body;
      }, response => {
        this.error = response.body;
      });
    },
    formatFiat: function(n) {
        return numeral(n).format('0,0.00') + " " +this.fiatsymbol;
    },
    formatCoin: function(n) {
      return numeral(n).format('0,0.0000');
    },
  },
  mounted() {
    this.makeRequest();
  },
}
</script>
