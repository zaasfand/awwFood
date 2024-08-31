// import { createApp } from 'vue'
// import App from './App.vue'
// import vuetify from './plugins/vuetify'
// import router from '../src/router/index';
// import { loadFonts } from './plugins/webfontloader'

// loadFonts()

// createApp(App)
//   .use(vuetify)
//   .mount('#app')
//   .use(router) 


import { createApp } from 'vue';
import App from './App.vue';
import router from './router';
import vuetify from './plugins/vuetify'; // Ensure this path and plugin are correct

createApp(App)
  .use(router)
  .use(vuetify)
  .mount('#app');


