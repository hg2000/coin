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
      <div class="row">
        <div class="col-md-4">
          <div class="panel panel-default" v-if="balances != 0">
            <div class="panel-heading">
              <h3>Revenue</h3>
            </div>
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
        </div>
        <div class="col-md-4">
          <div class="panel panel-default" v-if="balances != 0">
            <div class="panel-heading">
              <h3>Buy / Sell Volume</h3>
            </div>
            <div class="panel-body">
              <div class="panel-body">
                <chartBuySell elementId="buys-sales" :buyVolume="sum.buyVolumeFiat" :sellVolume="sum.sellVolumeFiat" :currencyLabel="fiat"  />
              </div>
            </div>
          </div>
        </div>
        <div class="col-md-4">
          <div class="panel panel-default" v-if="balances != 0">
            <div class="panel-heading">
              <h3>Revenue: {{ formatFiat(sum.totalRevenueFiat) }}</h3>
            </div>
            <div class="panel-body">
              <div class="panel-body">
                <chartRevenue elementId="chart-revenue" :tradingRevenue="sum.tradingRevenueFiat" :currentValue="sum.currentValueFiat" />

              </div>
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
