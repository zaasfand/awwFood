import { createRouter, createWebHistory } from 'vue-router';
import SplashScreen from '../components/SplashScreen.vue';
import SelectionScreen from '../components/SelectionScreen.vue';
import LoginScreen from '../components/LoginScreen.vue'; // For Organization login
import OrganizationSignupScreen from '../components/OrganizationSignupScreen.vue'; // Organization signup
import IndividualLoginScreen from '../components/IndividualLoginScreen.vue'; // Individual login
import IndividualSignupScreen from '../components/IndividualSignupScreen.vue'; // Individual signup
import OrganizationPage from '../components/OrganizationPage.vue';

const routes = [
  { path: '/', name: 'SplashScreen', component: SplashScreen },
  { path: '/select', name: 'SelectionScreen', component: SelectionScreen },
  { path: '/login', name: 'LoginScreen', component: LoginScreen }, // Organization login
  { path: '/organization-signup', name: 'OrganizationSignupScreen', component: OrganizationSignupScreen }, // Organization signup
  { path: '/individual-login', name: 'IndividualLoginScreen', component: IndividualLoginScreen }, // Individual login
  { path: '/individual-signup', name: 'IndividualSignupScreen', component: IndividualSignupScreen }, // Individual signup
  { path: '/organization', name: 'OrganizationPage', component: OrganizationPage },
];

const router = createRouter({
  history: createWebHistory(),
  routes,
});

export default router;
