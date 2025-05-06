import { createRouter, createWebHistory } from 'vue-router'
import Home from '@pages/Home.vue'
import Login from '@pages/auth/Login.vue'
import Register from '@pages/auth/Register.vue'
import Menu from '@pages/Menu.vue'
import Partner from '@pages/Partner.vue'
import Courier from '@pages/Courier.vue'
import Admin from '@pages/Admin.vue'
import Support from '@pages/Support.vue'
import Checkout from '@pages/Checkout.vue'

const routes = [
  {
    path: '/',
    name: 'home',
    component: Home
  },
  {
    path: '/login',
    name: 'login',
    component: Login
  },
  {
    path: '/register',
    name: 'register',
    component: Register
  },
  {
    path: '/partner',
    name: 'partner',
    component: Partner
  },
  {
    path: '/courier',
    name: 'courier',
    component: Courier
  },
  {
    path: '/admin',
    name: 'admin',
    component: Admin
  },
  {
    path: '/support',
    name: 'support',
    component: Support
  },
  {
    path: '/terms',
    name: 'terms',
    component: () => import('@pages/Terms.vue')
  },
  {
    path: '/privacy',
    name: 'privacy',
    component: () => import('@pages/Privacy.vue')
  },
  {
    path: '/menu/:slug',
    name: 'restaurant',
    component: Menu,
    props: true
  },
  {
    path: '/:slug',
    name: 'directRestaurant',
    component: Menu,
    props: true
  },
  {
    path: '/checkout',
    name: 'checkout',
    component: Checkout
  }
]

const router = createRouter({
  history: createWebHistory(),
  routes
})

export default router
