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
                                    <th>volumes</th>
                                    <th>Buy Value</th>
                                    <th>Sell Value</th>
                                    <th>Revenue</th>
                                    <th>Buy Pool</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr v-for="item in sellPoolItems">
                                    <td>{{ item.date }}

                                        <div class="collapse" :id="'buy-pool-' + item.id">
                                            <ul class="list-group">
                                                <li class="list-group-item" v-for="buyItem in item.buy_pool">
                                                    Date: {{ buyItem.date }}<br> Volume: {{ buyItem.volume_taken }}<br> Value BTC: {{ buyItem.value_btc }}<br> Value Fiat: {{ buyItem.value_fiat }}<br>
                                                </li>
                                            </ul>
                                        </div>
                                    </td>
                                    <td>{{ item.platform_id }}</td>
                                    <td>{{ item.type }}</td>
                                    <td>{{ item.source_currency }}/{{ item.target_currency }}</td>
                                    <td>{{ item.volume_taken}}</td>
                                    <td>{{ item.buy_value}}</td>
                                    <td>{{ item.value_btc}}</td>
                                    <td>{{ item.revenue}}</td>

                                    <td>
                                        <a class="btn btn-primary" data-toggle="collapse" :href="'#buy-pool-' + item.id" aria-expanded="false" aria-controls="collapseExample">
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
    },
    mounted() {
        this.makeRequest();
    }
}
</script>
