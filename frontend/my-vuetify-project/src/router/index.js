import { createRouter, createWebHistory } from 'vue-router';
import SplashScreen from '../components/SplashScreen.vue';
import SelectionScreen from '../components/SelectionScreen.vue';
import LoginScreen from '../components/LoginScreen.vue';
import OrganizationPage from '../components/OrganizationPage.vue';

const routes = [
  { path: '/', name: 'SplashScreen', component: SplashScreen },
  { path: '/select', name: 'SelectionScreen', component: SelectionScreen },
  { path: '/login', name: 'LoginScreen', component: LoginScreen },
  { path: '/organization', name: 'OrganizationPage', component: OrganizationPage },
];

const router = createRouter({
  history: createWebHistory(),
  routes,
});

export default router;
