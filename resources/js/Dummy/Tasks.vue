<template>
  <div id="app">
    <p v-if="loading">Loading...</p>
    <ul v-else>
      <li v-for="item in data" :key="key">
        <a href="#">{{ item.title }}</a>
      </li>
    </ul>
    <p v-if="error">{{ error }}</p>
  </div>
</template>

<script>
import axios from "axios";

export default {
  data() {
    return {
      loading: false,
      data: null,
      error: ""
    };
  },

  created: function() {
    this.loading = true;
    axios
      .get("http://127.0.0.1:8000/api")
      .then(res => {
        this.loading = false;
        this.data = res.data;
      })
      .catch(err => {
        this.loading = false;
        this.error = err;
      });
  }
};
</script>
