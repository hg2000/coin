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
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
  <!-- Right Sidebar End -->

  <div class="content-full">
    <div class="content">
      <div class="row top-summary" v-if="balances != 0">
        <div class="col-lg-4 col-md-6" >
          <div class="widget green-1 animated fadeInDown" >
            <div class="widget-content padding">
              <div class="widget-icon">
                <i class="icon-globe-inv"></i>
              </div>
              <div class="text-box">
                <p class="maindata">TOTAL <b>REVENUE</b></p>
                <h2><span class="animate-number" data-value="25153" data-duration="3000">{{ formatFiat(sum.totalRevenueFiat) }}</span></h2>
                <div class="clearfix"></div>
              </div>
            </div>
            <div class="widget-footer">
              <div class="row">
                <div class="col-sm-12">

                </div>
              </div>
              <div class="clearfix"></div>
            </div>
          </div>
        </div>

        <div class="col-lg-4 col-md-6">
          <div class="widget darkblue-2 animated fadeInDown">
            <div class="widget-content padding">
              <div class="widget-icon">
                <i class="icon-bag"></i>
              </div>
              <div class="text-box">
                <p class="maindata">TOTAL <b>TRADE REVENUE</b></p>
                <h2><span class="animate-number" data-value="6399" data-duration="3000">{{ formatFiat(sum.tradingRevenueFiat) }}</span></h2>

                <div class="clearfix"></div>
              </div>
            </div>
            <div class="widget-footer">
              <div class="row">
                <div class="col-sm-12">

                </div>
              </div>
              <div class="clearfix"></div>
            </div>
          </div>
        </div>

        <div class="col-lg-4 col-md-6">
          <div class="widget blue-1 animated fadeInDown">
            <div class="widget-content padding">

              <div class="text-box">
                <p class="maindata">OVERALL <b>CURRENT COIN VALUE</b></p>
                <h2><span class="animate-number" data-value="70389" data-duration="3000">{{ formatFiat(sum.currentValueFiat) }}</span></h2>
                <div class="clearfix"></div>
              </div>
            </div>
            <div class="widget-footer">
              <div class="row">
                <div class="col-sm-12">

                </div>
              </div>
              <div class="clearfix"></div>
            </div>
          </div>
        </div>

      </div>

      <div class="row" v-if="balances != 0">
        <div class="col-md-6">
          <div class="widget">
            <div class="widget-header">
              <h2>Buy / Sell Volume</h2>
            </div>
            <div class="widget-content padding">
              <chartBuySell elementId="buys-sales" :buyVolume="sum.buyVolumeFiat" :sellVolume="sum.sellVolumeFiat" :currencyLabel="fiat" />
            </div>
          </div>
        </div>
        <div class="col-md-6">
          <div class="widget">
            <div class="widget-header">
              <h2>Current Coin Value + Trading Revenue</h2>
            </div>
            <div class="widget-content padding">
              <chartRevenue elementId="chart-revenue" :tradingRevenue="sum.tradingRevenueFiat" :currentValue="sum.currentValueFiat" />
            </div>
          </div>
        </div>
      </div>
      <div class="row">

      </div>
    </div>
  </div>
  <div v-if="error == 0">
    <div v-if="balances == 0">
      <div class="spinner"></div>
    </div>
  </div>
  <div v-if="error != 0" class="alert alert-danger">
    <p>
      <strong>Error: {{error}}</strong><br> {{trace}}

      <br>
    </p>
  </div>
</div>
</template>


<script>
import format from '../../mixins/format.js';
import chartBuySell from '../../components/charts/ChartBuySell.js';
import chartRevenue from '../../components/charts/ChartRevenue.js';

export default {
  props: [
    'fiatsymbol',
    'fiat'
  ],
  components: {
    chartBuySell,
    chartRevenue

  },
  mixins: [format],
  data: function() {
    return {
      balances: 0,
      sum: 0,
      error: 0,
      trace: '',
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
        var parsed = JSON.parse(response.body);
        this.error = parsed.message;
        this.trace = parsed.trace;
      });
    },
  },
  mounted() {
    this.makeRequest();
  }
}
</script>
