<template>
  <div class="selection-screen">
    <v-btn @click="$router.push('/login')">Organization</v-btn>
    <v-btn @click="$router.push('/signup')">Individual</v-btn>
  </div>
</template>

<script>
export default {
  name: 'SelectionScreen',
};
</script>

<style scoped>
.selection-screen {
  display: flex;
  flex-direction: column;
  justify-content: center;
  align-items: center;
  height: 100vh;
  gap: 20px;
}
</style>
