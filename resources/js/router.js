import { createRouter, createWebHistory } from 'vue-router'
import Home from '@/Pages/Customer/Home.vue'
import Login from '@/Pages/auth/Login.vue'
import Register from '@/Pages/auth/Register.vue'
import Menu from '@/Pages/Customer/Menu.vue'
import Partner from '@/Pages/Partner/Partner.vue'
import Courier from '@/Pages/Courier/Courier.vue'
import Admin from '@/Pages/Partner/Admin.vue'
import Support from '@/Pages/Customer/Support.vue'
import Checkout from '@/Pages/Customer/Checkout.vue'

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
    component: () => import('@/Pages/Customer/Terms.vue')
  },
  {
    path: '/privacy',
    name: 'privacy',
    component: () => import('@/Pages/Customer/Privacy.vue')
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
