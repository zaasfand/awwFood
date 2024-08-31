<template>
  <div class="auth-screen">
    <h1>Login / Signup Screen</h1>
    <v-btn @click="$router.push('/organization')">Continue as Organization</v-btn>
    <v-btn @click="$router.push('/individual')">Continue as Individual</v-btn>
  </div>
</template>

<script>
export default {
  name: 'LoginScreen', // Change name to SignupScreen in the other file
};
</script>

<style scoped>
.auth-screen {
  display: flex;
  flex-direction: column;
  justify-content: center;
  align-items: center;
  height: 100vh;
  gap: 20px;
}
</style>
