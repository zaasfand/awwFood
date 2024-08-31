<template>
  <div class="selection-screen">
    <p class="question">
      Are you an <span class="highlight">Organization</span> or <span class="highlight">Individual</span>?
    </p>
    <div class="buttons-container">
      <div class="button-wrapper" @click="navigateTo('organization')">
        <!-- Use require to load the organization icon -->
        <img :src="require('@/assets/octicon_organization-16.png')" alt="Organization Icon" class="button-icon" />
        <p class="button-text">Organization</p>
      </div>
      <div class="button-wrapper" @click="navigateTo('individual')">
        <!-- Use require to load the individual icon -->
        <img :src="require('@/assets/ri_user-line.png')" alt="Individual Icon" class="button-icon" />
        <p class="button-text">Individual</p>
      </div>
    </div>
  </div>
</template>

<script>
export default {
  name: 'SelectionScreen',
  methods: {
    navigateTo(role) {
      // Navigate to login or signup page based on the selection
      this.$router.push(`/${role === 'organization' ? 'login' : 'signup'}`);
    },
  },
};
</script>

<style scoped>
.selection-screen {
  display: flex;
  flex-direction: column;
  justify-content: center;
  align-items: center;
  height: 100vh;
  background: url('@/assets/istockphoto-1829241109-612x612 1.png') no-repeat center center; 
  background-size: cover; /* Ensures the image covers the whole screen */
  padding: 20px;
  text-align: center;
  color: white;
}

.question {
  font-size: 18px;
  margin-bottom: 20px;
  font-weight: bold;
  color: #ffffff;
}

.highlight {
  font-weight: bold;
}

.buttons-container {
  display: flex;
  gap: 20px; /* Space between the buttons */
}

.button-wrapper {
  display: flex;
  flex-direction: column;
  justify-content: center;
  align-items: center;
  background: white;
  padding: 20px;
  border-radius: 12px;
  cursor: pointer;
  transition: transform 0.3s;
  box-shadow: 0px 4px 8px rgba(0, 0, 0, 0.1);
}

.button-wrapper:hover {
  transform: scale(1.05);
}

.button-icon {
  width: 40px; /* Adjust icon size */
  margin-bottom: 10px;
}

.button-text {
  color: #f68b1e; /* Adjust text color */
  font-size: 14px;
  font-weight: bold;
}
</style>
