export default {
  data: function() {
    return {
      spinner: true,
      displayContent: false,
      lastRefreshDateTime: ''
    }
  },
  methods: {
    refresh: function() {
      this.setIsLoading(true);
      this.$http.get('/api/refresh/', {
          headers: {
            'Accept': 'application/json'
          }
        })
        .then(response => {
          this.trades = response.body;
          this.setIsLoading(false);
          this.getLastRefreshDateTime();

        }, response => {
          var parsed = JSON.parse(response.body);
          this.error = parsed.message;
          this.trace = parsed.trace;
          this.setIsLoading(false);
        });

    },
    getLastRefreshDateTime: function() {
      this.$http.get('/api/lastRefreshDateTime/', {
          headers: {
            'Accept': 'application/json'
          }
        })
        .then(response => {
          this.lastRefreshDateTime = response.body[0];
          this.setIsLoading(false);

        }, response => {
          var parsed = JSON.parse(response.body);
          this.error = parsed.message;
          this.trace = parsed.trace;
          this.setIsLoading(false);
        });
    },

    isLoading: function() {
      return this.spinner && !this.displayContent;
    },
    setIsLoading: function(isLoading) {
      if (isLoading) {
        this.spinner = true;
        this.displayContent = false;
      } else {
        this.spinner = false;
        this.displayContent = true;
      }
    }
  }
}
