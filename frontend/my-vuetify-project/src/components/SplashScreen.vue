<template>
  <div class="splash-screen">
    <p>Splash Screen - Checking if it loads...</p>
    <img src="@/assets/splash-image.png" alt="Splash Image" />
  </div>
</template>

<script>
export default {
  name: 'SplashScreen',
  mounted() {
    setTimeout(() => {
      this.$router.push('/select');
    }, 3000);
  },
};
</script>

<style scoped>
.splash-screen {
  display: flex;
  justify-content: center;
  align-items: center;
  height: 100vh;
  background-color: #ffffff;
}
</style>
