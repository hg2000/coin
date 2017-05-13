<template>
<div class="container-fluid">

  <div class="row">
    <div class="col-md-12">
        Coin Detail Page {{ $route.params.id }}
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

      this.$http.get('/api/coin/' + this.$route.params.id).then(response => {
        this.balances = response.body.balances;
        this.sum = response.body.sum;
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
