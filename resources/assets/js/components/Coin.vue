<template>
<div class="container-fluid">
    <div class="row">
        <div class="col-md-12">
            <div class="panel panel-default" v-if="sellPoolItems != 0">
                <div class="panel-heading">Trade History {{ $route.params.id }}</div>

                <p>
                    <table class="table table-striped table-responsive table-hover">
                        <thead>
                            <tr>
                                <th>Date</th>
                                <th>platformId</th>
                                <th>Type</th>
                                <th>Currency Pair</th>
                                <th>volume</th>
                                <th>Revenue BTC</th>
                                <th>Revenue Fiat</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-for="item in sellPoolItems">
                                <td>{{ item.sell_trade.date }}

                                    <div class="collapse" :id="'buy-pool-' + item.sell_trade.id">
                                        <ul class="list-group">
                                            <li class="list-group-item" v-for="buyItem in item.buy_trades">
                                                <strong>{{ buyItem.volume_taken }} taken from buy pool trade from {{ buyItem.date }}.</strong><br> Original Pool Volume: {{ buyItem.volume_before }}<br> Remaining in Pool: {{ buyItem.volume }}<br>
                                                <br> Value (BTC): {{ buyItem.value_taken_btc }}<br> Purchase Value BTC: {{ buyItem.purchase_value_taken_btc }}<br> Revenue BTC: {{ buyItem.revenue_taken_btc }}<br>
                                            </li>
                                        </ul>
                                    </div>
                                </td>
                                <td>{{ item.sell_trade.platform_id }}</td>
                                <td>{{ item.sell_trade.type }}</td>
                                <td>{{ item.sell_trade.source_currency }}/{{ item.sell_trade.target_currency }}</td>
                                <td>{{ formatCoin(item.sell_trade.volume) }}</td>
                                <td>{{ formatCoin(item.sell_trade.revenue_btc) }}</td>
                                <td>{{ formatFiat(item.sell_trade.revenue_fiat) }}</td>

                                <td>
                                    <a class="btn btn-primary" data-toggle="collapse" :href="'#buy-pool-' + item.sell_trade.id" aria-expanded="false" aria-controls="collapseExample">
                                            Show Buy Pool
                                        </a>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </p>
            </div>
        </div>
        <div v-if="error == 0">
            <div v-if="sellPoolItems == 0" class="alert alert-warning">
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
            sellPoolItems: 0,
            error: 0
        }
    },
    methods: {
        makeRequest: function() {
            this.$http.get('/api/coin/' + this.$route.params.id).then(response => {
                this.sellPoolItems = response.body.sellPool;
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
        this.makeRequest();
    }
}
</script>
