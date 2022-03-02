import { createRouter, createWebHashHistory } from 'vue-router'
import NProgress from 'nprogress'
import AuthLayout from '@/layouts/Auth'
import MainLayout from '@/layouts/Main'
import ViewerLayout from '@/layouts/Forms/PdfViewer'
import storeState from '@/store'

const router = createRouter({
  base: process.env.BASE_URL,
  scrollBehavior() {
    return { x: 0, y: 0 }
  },
  history: createWebHashHistory(),
  routes: [
    {
      path: '/',
      name: 'home',
      // VB:REPLACE-NEXT-LINE:ROUTER-REDIRECT
      redirect: '/dashboard',
      component: MainLayout,
      meta: {
        authRequired: true,
        hidden: true,
      },
      children: [
        // VB:REPLACE-START:ROUTER-CONFIG
        {
          path: '/dashboard',
          meta: { title: 'Dashboard' },
          component: () => import('./views/dashboard'),
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
          path: '/manager/groups',
          meta: {
            title: 'Groups Manager',
          },
          component: () => import('./views/manager/groups'),
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

        // Main Forms
        {
          path: '/form/:formId',
          name: 'main.form',
          props: true,
          component: () => import('./views/forms/main'),
        },
        {
          path: '/list/:formId',
          name: 'form.list',
          props: true,
          component: () => import('./views/forms/list'),
        },
        {
          path: '/manager/:formId',
          name: 'manager.manager',
          props: true,
          component: () => import('./views/forms/manager'),
        },
        // VB:REPLACE-END:ROUTER-CONFIG
      ],
    },

    // PDF Viewer
    {
      path: '/viewer',
      name: 'viewerComponent',
      // VB:REPLACE-NEXT-LINE:ROUTER-REDIRECT
      redirect: 'viewer/pdf',
      component: ViewerLayout,
      meta: {
        authRequired: true,
        hidden: true,
      },
      children: [
        {
          path: '/viewer/pdf',
          name: 'viewerPdf',
          meta: {
            title: 'File Viewer',
          },
          component: () => import('./views/viewer'),
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
          name: 'route404',
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
          name: 'login',
          meta: {
            title: 'Sign In',
          },
          component: () => import('./views/auth/login'),
        },
      ],
    },

    // Redirect to 404
    {
      path: '/:pathMatch(.*)*',
      redirect: { name: 'route404' },
    },
  ],
})

router.beforeEach((to, from, next) => {
  if (to.name !== 'viewerPdf') {
    NProgress.start()
    setTimeout(() => {
      NProgress.done()
    }, 300)
  }

  if (to.matched.some(record => record.meta.authRequired)) {
    const accessToken = localStorage.getItem('accessToken')
    if(typeof accessToken === 'undefined' || accessToken == null) {
      next({
        path: '/auth/login',
        query: { redirect: to.fullPath },
      })
    } else {
      if(to.name === 'viewerPdf') {
        storeState.state.user.accountFetchIsTouched.then(response => {
          if(response) {
            const { accessToken } = response.data
            if (!accessToken) {
              next({
                path: '/auth/login',
                query: { redirect: to.fullPath },
              })
            } else {
              next()
            }
          }
        })
      } else {
        if(!storeState.state.user.authorized) {
          next({
            path: '/auth/login',
            query: { redirect: to.fullPath },
          })
        }else {
          next()
        }
      }
    }
  } else {
    next()
  }
})

export default router
