<template>
<div>
  <!-- Right Sidebar Start -->
  <div class="right side-menu">

    <div class="tab-content">
      <div class="tab-pane active" id="feed">
        <div class="tab-inner slimscroller">

          <div class="clearfix"></div>
          <div class="panel-group" id="collapse">
            <div class="panel panel-default">
              <div class="panel-heading bg-green-3">
                <h4 class="panel-title">
                 <a data-toggle="collapse" data-parent="#accordion" href="#remails">
                   <i class="icon-mail"></i> Settings

                 </a>
               </h4>
              </div>

              <div class="panel-collapse collapse in">
                <div class="panel-body">
                  <div class="btn-group" role="group" aria-label="Select base currency">
                    Last refresh: {{lastRefreshDateTime}}
                  <button v-on:click="refresh" class="btn btn-primary" type="button">
                      <span class="glyphicon glyphicon-repeat" aria-hidden="true"></span> Refresh
                    </button><br>
                  </div>
                </div>
              </div>


              <div class="panel-collapse collapse in">
                <div class="panel-body">
                  <div class="btn-group" role="group" aria-label="Select base currency">
                    Base Currency
                    <div class="clearfix"></div>
                    <a v-on:click="changeBaseCurrency(fiat)" type="button" class="btn btn-primary" :class="getActiveClass(fiat)">{{fiatsymbol}}</a>
                    <a v-on:click="changeBaseCurrency('BTC')" type="button" class="btn btn-primary" :class="getActiveClass('BTC')">&#3647;</a>
                  </div>
                </div>
              </div>

              <div class="panel-collapse collapse in">
                <div class="panel-body">
                  <div class="btn-group" role="group" aria-label="Filter">
                    Filter
                    <div class="clearfix"></div>
                    <label>
                      <input type="checkbox" id="checkbox-volume-filter" v-model="filter.avaible">
                      Show coins with volume only
                    </label>
                  </div>
                </div>
              </div>


            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
  <!-- Right Sidebar End -->

  <div class="content-full">
    <div class="content">

      <div class="row">
        <div class="col-md-12">

          <div class="widget" v-if="!isLoading() && balances !==0">
            <div class="widget-header">
              <h2>Portfolio</h2>
            </div>
            <div class="widget-content">

              <div class="table-responsive portfolio-table">
                <table class="table">
                  <thead>
                    <tr>
                      <th>Currency</th>
                      <th>Avg. Rate</th>
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
                    <template v-for="item in balances">
                      <tr :class="applyFilterAvaible(item)">
                        <td class="border-right"> {{ item.currency }}</td>
                        <td class="border-right" style="width: 150px; padding: 0 2px">
                          <chartDayRates :elementId="'btcgraph7' + item.id" :rates="dailyRates" :currency="item.currency" :title="formatPercent(item.rateDiffSevenDaysAgoFiat)" :target="1" :width="120" :height="110">
                          </chartDayRates>
                        </td>

                        <td class="border-right">{{ formatFiat(item.currentRateFiat) }}</td>
                        <td class="border-right">{{ formatFiat(item.averagePurchaseRateCoinFiat) }}</td>
                        <td class="border-right">{{ formatFiat(item.purchaseValueFiat) }}</td>
                        <td class="border-right">{{ formatFiat(item.currentValueFiat) }}</td>
                        <td class="border-right">{{ formatFiat(item.revenueFiat) }}</td>
                        <td class="border-right">{{ formatPercent(item.revenueRateFiat) }}</td>


                        <td class="border-right">{{ formatCoin(item.volume) }}</td>
                        <td><a :href="item.chartUrl" target="_blank">TradingView</a></td>
                      </tr>
                    </template>

                    <tr class="info">
                      <th>Total</th>
                      <th></th>
                      <th></th>
                      <th class="border-right"></th>
                      <th class="border-right">{{ formatFiat(sum.purchaseValueFiat)}}</th>
                      <th class="border-right">{{ formatFiat(sum.currentValueFiat) }}</th>
                      <th class="border-right">{{ formatFiat(sum.currentRevenueFiat) }}</th>
                      <th class="border-right">{{ formatPercent(sum.tradingRevenueRateFiat) }}</th>
                      <th></th>
                      <th></th>
                    </tr>
                  </tbody>

                  <tbody v-if="baseCurrency == 'BTC'">

                    <template v-for="item in balances">
                        <tr :class="applyFilterAvaible(item)">
                          <td class="border-right"> {{ item.currency }}</td>
                          <td class="border-right" style="width: 150px; padding: 0 2px">
                            <chartDayRates :elementId="'btcgraph7' + item.id" :rates="dailyRates" :currency="item.currency" :title="formatPercent(item.rateDiffSevenDaysAgoFiat)" :target="1" :width="120" :height="110">
                            </chartDayRates>
                          </td>
                          <td class="border-right">{{ formatCoin(item.currentRateBtc) }} ฿</td>
                          <td class="border-right">{{ formatCoin(item.averagePurchaseRateCoinBtc) }} ฿</td>
                          <td class="border-right">{{ formatCoin(item.purchaseValueBtc) }} ฿</td>
                          <td class="border-right">{{ formatCoin(item.currentValueBtc) }} ฿</td>
                          <td class="border-right">{{ formatCoin(item.revenueBtc) }} ฿</td>
                          <td class="border-right">{{ formatPercent(item.revenueRateBtc) }}</td>

                          <td class="border-right">{{ formatCoin(item.volume) }}</td>
                          <td><a :href="item.chartUrl" target="_blank">TradingView</a></td>

                        </tr>
                  </template>

                    <tr class="info">
                      <th>Total</th>
                      <th></th>
                      <th></th>
                      <th class="border-right"></th>
                      <th class="border-right">{{ formatCoin(sum.purchaseValueBtc)}} ฿</th>
                      <th class="border-right">{{ formatCoin(sum.currentValueBtc) }} ฿</th>
                      <th class="border-right">{{ formatCoin(sum.currentRevenueBtc) }} ฿</th>
                      <th class="border-right">{{ formatPercent(sum.tradingRevenueRateBtc) }}</th>
                      <th></th>
                      <th></th>
                    </tr>

                  </tbody>

                </table>
              </div>
              <!--end of .table-responsive-->
            </div>
          </div>
          <!-- end of panel -->

          <div class="spinner" v-if="isLoading()"></div>

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
</div>
</template>

<script>
import format from '../../mixins/format.js';
import base from '../../mixins/base.js';
import chartDayRates from '../../components/charts/ChartDayRates.js';

export default {
  props: [
    'fiatsymbol',
    'fiat'
  ],
  components: {
    chartDayRates
  },
  mixins: [format,base],
  data: function() {
    return {
      balances: 0,
      sum: 0,
      error: 0,
      trace: '',
      balances: 0,
      dailyRates: [],
      baseCurrency: this.fiat,
      activeCurrencyClass: 'active',
      filter: {
        avaible: true
      }
    }
  },
  methods: {
    makeRequest: function() {
      this.setIsLoading(true);
      this.$http.get('/api/portfolio/', {
        headers: {
          'Accept': 'application/json'
        }
      }).then(response => {
        this.balances = response.body.balances;
        this.sum = response.body.sum;
        this.dailyRates = response.body.dailyRateAverage;
        this.setIsLoading(false);
      }, response => {
        var parsed = JSON.parse(response.body);
        this.error = parsed.message;
        this.trace = parsed.trace;
        this.setIsLoading(false);

      });

    },
    changeBaseCurrency: function(currency) {
      this.activeCurrencyClass = 'no';
      this.baseCurrency = currency;
    },
    getActiveClass(currency) {
      if (currency === this.baseCurrency) {
        return 'active'
      } else {
        return 'no';
      }
    },
    applyFilterAvaible(item) {
      if (this.filter.avaible && item.volume > 0) {
        return 'show-row';
      }
      if (!this.filter.avaible) {
        return 'show-row';
      }
      return 'hide-row';
    }
  },
  mounted() {
    this.makeRequest();
    this.getLastRefreshDateTime();
  }
}
</script>
