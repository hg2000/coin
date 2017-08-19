<template>
<div>

  <div class="container-fluid">
    <div class="row">
      <div class="col-xs-12">

        <div class="panel panel-default" v-if="balances != 0">
          <div class="panel-heading">Coin Portfolio</div>
          <div class="panel-body">

            <div class="table-responsive">
              <table class="table table-striped table-responsive table-hover">
                <thead>
                  <tr>
                    <th class="border-right">Currency</th>
                    <th>Volume</th>
                    <th>Current Rate (Coin/BTC)</th>

                    <th>Current Rate (Coin/{{ fiat }})</th>
                    <th>Rate Diff 1 Day (Coin/{{ fiat }})</th>
                    <th class="border-right">Rate Diff 7 Days ago (Coin/{{ fiat }})</th>

                    <th>Avg purchase rate(Coin/{{ fiat }})</th>
                    <th>Avg purchase rate(BTC/COIN)</th>

                    <th>Purchase Value (BTC)</td>
                      <th>Current Value (BTC)</th>
                      <th>Revenue (BTC)</th>
                      <th class="border-right">Revenue Rate (BTC)</th>

                      <th>Purchase value ({{ fiat}})</th>
                      <th>Current Value ({{ fiat }})</th>
                      <th>Revenue ({{ fiat }})</th>
                      <th>Revenue (%)</th>
                      <th>Chart</th>
                  </tr>
                </thead>
                <tbody>
                  <tr v-for="item in balances">
                    <td class="border-right"> {{ item.currency }}</td>
                    <td>{{ formatCoin(item.volume) }}</td>
                    <td>{{ formatCoin(item.currentRateBtc) }}</td>

                    <td>{{ formatFiat(item.currentRateFiat) }}</td>
                    <td>{{ formatPercent(item.rateDiffDayFiat)}}</td>
                    <td class="border-right">{{ formatPercent(item.rateDiffSevenDaysAgoFiat)}}</td>

                    <td>{{ formatFiat(item.averagePurchaseRateCoinFiat) }}</td>
                    <td class="border-right">{{ formatCoin(item.averagePurchaseRateBtcCoin) }}</td>

                    <td>{{ formatCoin(item.purchaseValueBtc) }}</td>
                    <td>{{ formatCoin(item.currentValueBtc) }}</td>
                    <td>{{ formatCoin(item.currentRevenueBtc) }}</td>
                    <td class="border-right">{{ formatPercent(item.revenueRateBtc) }}</td>

                    <td>{{ formatFiat(item.purchaseValueFiat) }}</td>
                    <td>{{ formatFiat(item.currentValueFiat) }}</td>
                    <td>{{ formatFiat(item.revenueFiat) }}</td>
                    <td>{{ formatPercent(item.revenueRateFiat) }}</td>
                    <td><a :href="item.chartUrl" target="_blank">Link</a></td>
                  </tr>

                  <tr class="info">
                    <th>Total</th>
                    <th></th>
                    <th></th>
                    <th></th>
                    <th></th>
                    <th></th>

                    <th></th>
                    <th></th>
                    <th>{{ formatCoin(sum.purchaseValueBtc)}} B</th>
                    <th>{{ formatCoin(sum.currentValueBtc) }} B</th>
                    <th>{{ formatCoin(sum.currentRevenueBtc) }} B</th>
                    <th>{{ formatPercent(sum.tradingRevenueRateBtc) }}</th>

                    <th>{{ formatFiat(sum.purchaseValueFiat)}}</th>
                    <th>{{ formatFiat(sum.currentValueFiat) }}</th>
                    <th>{{ formatFiat(sum.currentRevenueFiat) }}</th>
                    <th>{{ formatPercent(sum.tradingRevenueRateFiat) }}</th>
                    <th></th>
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
import format from '../mixins/format.js';
export default {
  props: [
    'fiatsymbol',
    'fiat'
  ],
  mixins: [format],
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

    }
  },
  mounted() {
    this.makeRequest();
  }
}
</script>
