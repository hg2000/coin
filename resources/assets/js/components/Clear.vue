<template>
<div class="container-fluid">
  <div class="row">
    <div class="col-md-12">
      <div v-if="error == 0">
        <div v-if="clearing" class="alert alert-warning">
          <p>
            Refreshing data. Please Wait.
          </p>
        </div>
        <div v-if="success != 0" class="alert alert-success">
          <p>
            {{success}}
          </p>
        </div>
      </div>
      <div v-if="error == 0">
        <div v-if="balances == 0" class="alert alert-warning">
          <p>
            Please Wait
          </p>
        </div>
      </div>
      <div v-if="error != 0" class="alert alert-danger">
        <p>
          <strong>Error: {{error}}</strong><br>
          {{trace}}<br>
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
      clearing: true,
      success: 0,
      error: 0,
      trace: ''
    }
  },
  methods: {
    makeRequest: function() {
      this.$http.get('/api/clear', {
        headers: {
          'Accept': 'application/json'
        }
      }).then(response => {

        this.success = response.body;
        this.clearing = false;


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
