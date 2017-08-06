<template>
<div class="container-fluid">
  <div class="row">
    <div class="col-md-12">
      <div v-if="error == 0">
        <div v-if="clearing" class="alert alert-warning">
          <p>
            Clearing Cache. Please Wait.
          </p>
        </div>
        <div v-if="success != 0" class="alert alert-success">
          <p>
            {{success}}
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
      clearing: true,
      success: 0,
      error: 0
    }
  },
  methods: {
    makeRequest: function() {
      this.$http.get('/api/clear').then(response => {
        this.success = response.body;
        this.clearing = false;


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
