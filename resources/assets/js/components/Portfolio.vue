<template>
<div>

  <div class="container-fluid">
    <div class="row">
      <div class="col-xs-12">

        <div class="panel panel-default" v-if="balances != 0">
          <div class="panel-heading">Coin Portfolio</div>
          <div class="panel-body">

            <div class="clearfix">
              <div class="btn-group currency-buttons pull-right" role="group" aria-label="Select base currency">
                <button v-on:click="changeBaseCurrency(fiat)" type="button" class="btn btn-default" :class="activeCurrencyClass">{{fiatsymbol}}</button>
                <button v-on:click="changeBaseCurrency('BTC')" type="button" class="btn btn-default">B</button>
              </div>
            </div>




            <div class="table-responsive portfolio-table">
              <table class="table table-striped table-responsive table-hover">
                <thead>
                  <tr>
                    <th>Currency</th>
                    <th>Rate 14 Days)</th>
                    <th>Current Rate</th>
                    <th>Avg purchase rate</th>
                    <th>Purchase Value</td>
                      <th>Current Value</th>
                      <th>Revenue</th>
                      <th>Revenue Rate</th>

                      <th>Volume</th>
                      <th>Chart</th>
                  </tr>
                </thead>
                <tbody v-if="baseCurrency == fiat">
                  <tr v-for="item in balances">
                    <td class="border-right"> {{ item.currency }}</td>
                    <td class="border-right" style="width: 150px; padding: 0 2px">
                      <graph :elementId="'btcgraph7' + item.id" :rates="dailyRates" :currency="item.currency" :title="formatPercent(item.rateDiffSevenDaysAgoFiat)" :target="1" :width="220" :height="110">
                      </graph>
                    </td>

                    <td class="border-right">{{ formatFiat(item.currentRateFiat) }}</td>
                    <td class="border-right">{{ formatFiat(item.averagePurchaseRateCoinFiat) }}</td>
                    <td class="border-right">{{ formatFiat(item.purchaseValueFiat) }}</td>
                    <td class="border-right">{{ formatFiat(item.currentValueFiat) }}</td>
                    <td class="border-right">{{ formatFiat(item.revenueFiat) }}</td>
                    <td class="border-right">{{ formatPercent(item.revenueRateFiat) }}</td>


                    <td class="border-right">{{ formatCoin(item.volume) }}</td>
                    <td class="border-right"><a :href="item.chartUrl" target="_blank">TradingView</a></td>
                  </tr>

                  <tr class="info">
                    <th>Total</th>
                    <th></th>
                    <th></th>
                    <th></th>
                    <th></th>
                    <th>{{ formatFiat(sum.purchaseValueFiat)}}</th>
                    <th>{{ formatFiat(sum.currentValueFiat) }}</th>
                    <th>{{ formatFiat(sum.currentRevenueFiat) }}</th>
                    <th>{{ formatPercent(sum.tradingRevenueRateFiat) }}</th>
                    <th></th>
                  </tr>
                </tbody>

                <tbody v-if="baseCurrency == 'BTC'">
                  <tr v-for="item in balances">
                    <td class="border-right"> {{ item.currency }}</td>
                    <td class="border-right" style="width: 150px; padding: 0 2px">
                      <graph :elementId="'btcgraph7' + item.id" :rates="dailyRates" :currency="item.currency" :title="formatPercent(item.rateDiffSevenDaysAgoBtc)" :target="1" :width="220" :height="110">
                      </graph>
                    </td>

                    <td class="border-right">{{ formatCoin(item.currentRateBtc) }} B</td>
                    <td class="border-right">{{ formatCoin(item.averagePurchaseRateCoinBtc) }} B</td>
                    <td class="border-right">{{ formatCoin(item.purchaseValueBtc) }} B</td>
                    <td class="border-right">{{ formatCoin(item.currentValueBtc) }} B</td>
                    <td class="border-right">{{ formatCoin(item.revenueBtc) }} B</td>
                    <td class="border-right">{{ formatPercent(item.revenueRateBtc) }}</td>


                    <td class="border-right">{{ formatCoin(item.volume) }} B</td>
                    <td class="border-right"><a :href="item.chartUrl" target="_blank">TradingView</a></td>
                  </tr>

                  <tr class="info">
                    <th>Total</th>
                    <th></th>
                    <th></th>
                    <th></th>
                    <th></th>
                    <th>{{ formatCoin(sum.purchaseValueBtc)}} B</th>
                    <th>{{ formatCoin(sum.currentValueBtc) }} B</th>
                    <th>{{ formatCoin(sum.currentRevenueBtc) }} B</th>
                    <th>{{ formatPercent(sum.tradingRevenueRateBtc) }}</th>
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
            <strong>Error: {{error}}</strong><br> {{trace}}

            <br>
          </p>
        </div>

      </div>
    </div>
  </div>
</div>
</template>

<script>
import VueCharts from 'vue-chartjs'
import format from '../mixins/format.js';
import Graph from '../components/Graph.js';

export default {
  props: [
    'fiatsymbol',
    'fiat'
  ],
  components: {
    Graph
  },
  mixins: [format],
  data: function() {
    return {
      balances: 0,
      sum: 0,
      error: 0,
      trace: '',
      balances: 0,
      dailyRates: [],
      baseCurrency: this.fiat,
      activeCurrencyClass: 'active'
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
        this.dailyRates = response.body.dailyRateAverage;
      }, response => {
        var parsed = JSON.parse(response.body);
        this.error = parsed.message;
        this.trace = parsed.trace;
      });

    },
    changeBaseCurrency: function(currency) {
      this.activeCurrencyClass = 'no';
      this.baseCurrency = currency;
    }
  },
  mounted() {
    this.makeRequest();
  }
}
</script>
