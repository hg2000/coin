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
        <div class="col-md-12">
          <div class="panel panel-default" v-if="balances != 0">
            <div class="panel-heading">
              <h3>Trade History</h3>
            </div>
            <div class="panel-body">
              <div class="table-responsive">
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
                      <td>{{ trade.source_currency}}-{{ trade.target_currency}}</td>
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
          </div>
        </div>
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
export default {
  props: [
    'fiatsymbol',
    'fiat'
  ],
  mixins: [format],
  data: function() {
    return {
      trades: 0,
      trace: '',
      error: 0,
      balances: 0
    }
  },
  methods: {
    makeRequest: function() {

      this.$http.get('/api/trade_history/', {
        headers: {
          'Accept': 'application/json'
        }
      }).then(response => {
        this.trades = response.body;
        this.balances = 1;
      }, response => {
        var parsed = JSON.parse(response.body);
        this.error = parsed.message;
        this.trace = parsed.trace;
        this.balances = 1;
      });
    },
  },
  mounted() {
    this.makeRequest();
  },
}
</script>
