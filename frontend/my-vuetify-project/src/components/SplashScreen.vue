<template>
  <div class="splash-screen">
    <!-- Use require to load the image dynamically -->
    <img :src="require('../assets/foeoodelefroveres.png')" alt="Splash Image" class="splash-image" />
    <p class="title">Food Leftovers</p>
    <p class="version">Version 0.0.1</p>
  </div>
</template>

<script>
export default {
  name: 'SplashScreen',
  mounted() {
    // Navigate to the next screen after 3 seconds
    setTimeout(() => {
      this.$router.push('/select'); // Update path as necessary
    }, 3000);
  },
};
</script>

<style scoped>
.splash-screen {
  display: flex;
  flex-direction: column;
  justify-content: space-between; /* Ensures space between elements */
  align-items: center;
  height: 100vh;
  background-color: #f68b1e; /* Adjust color as needed */
  color: white;
  text-align: center;
  padding: 20px 0; /* Adds padding to the top and bottom */
}

.splash-image {
  width: 80px; /* Adjust size as needed */
  margin-bottom: 10px;
  margin-top: auto; /* Pushes the image down to center vertically */
}

.title {
  font-size: 20px;
  font-weight: bold;
  margin: 0;
}

.version {
  font-size: 12px;
  margin-top: auto;
}
</style>
