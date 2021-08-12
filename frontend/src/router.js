import Vue from 'vue'
import Router from 'vue-router'
import AuthLayout from '@/layouts/Auth'
import MainLayout from '@/layouts/Main'
import store from '@/store'

Vue.use(Router)

const router = new Router({
  base: process.env.BASE_URL,
  // mode: 'history',
  scrollBehavior() {
    return { x: 0, y: 0 }
  },
  routes: [
    {
      path: '/',
      redirect: 'dashboard/about',
      component: MainLayout,
      meta: {
        authRequired: true,
        hidden: true,
      },
      children: [
        // Dashboards
        {
          path: '/dashboard/about',
          meta: {
            title: 'Dashboard About',
          },
          component: () => import('./views/dashboard/about'),
        },
        // Manager
        {
          path: '/manager/form',
          meta: {
            title: 'Form Manager',
          },
          component: () => import('./views/manager/form'),
        },
        {
          path: '/manager/form',
          meta: {
            title: 'Form Manager',
          },
          component: () => import('./views/manager/form'),
        },
        {
          path: '/manager/measures',
          meta: {
            title: 'Measures Manager',
          },
          component: () => import('./views/manager/measures'),
        },
        {
          path: '/manager/signatories',
          meta: {
            title: 'Signatories Manager',
          },
          component: () => import('./views/manager/signatory'),
        },
        // Main Form
        {
          path: '/form/:formId',
          name: 'main.form',
          props: true,

          component: () => import('./views/forms'),
        },
        {
          path: '/list/:formId',
          name: 'form.list',
          props: true,
          component: () => import('./views/forms/list.vue'),
        },
      ],
    },

    // System Pages
    {
      path: '/auth',
      component: AuthLayout,
      redirect: 'auth/login',
      children: [
        {
          path: '/auth/404',
          meta: {
            title: 'Error 404',
          },
          component: () => import('./views/auth/404'),
        },
        {
          path: '/auth/500',
          meta: {
            title: 'Error 500',
          },
          component: () => import('./views/auth/500'),
        },
        {
          path: '/auth/login',
          meta: {
            title: 'Sign In',
          },
          component: () => import('./views/auth/login'),
        },
      ],
    },

    // Redirect to 404
    {
      path: '*', redirect: 'auth/404', hidden: true,
    },
  ],
})

router.beforeEach((to, from, next) => {
  if (to.matched.some(record => record.meta.authRequired)) {
    if (!store.state.user.authorized) {
      next({
        path: '/auth/login',
        query: { redirect: to.fullPath },
      })
    } else {
      next()
    }
  } else {
    next()
  }
})

export default router
