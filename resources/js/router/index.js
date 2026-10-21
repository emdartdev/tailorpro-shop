import { createRouter, createWebHistory } from 'vue-router';
import { authState } from '@/lib/auth';
import { updateSeo } from '@/lib/seo';

const routes = [
  {
    path: '/login',
    name: 'Login',
    component: () => import('@/pages/LoginPage.vue'),
    meta: { public: true },
  },
  {
    path: '/reset-password',
    name: 'ResetPassword',
    component: () => import('@/pages/ResetPasswordPage.vue'),
    meta: { public: true },
  },
  {
    path: '/',
    redirect: () => (authState.isViewer ? '/staff' : '/orders'),
  },
  {
    path: '/orders',
    name: 'Orders',
    component: () => import('@/pages/OrdersPage.vue'),
    meta: { requiresAuth: true, restrictViewer: true },
  },
  {
    path: '/orders/new',
    name: 'CreateOrder',
    component: () => import('@/pages/CreateOrderPage.vue'),
    meta: { requiresAuth: true, restrictViewer: true },
  },
  {
    path: '/orders/:id/edit',
    name: 'EditOrder',
    component: () => import('@/pages/CreateOrderPage.vue'),
    meta: { requiresAuth: true, restrictViewer: true },
  },
  {
    path: '/orders/:id',
    name: 'OrderDetails',
    component: () => import('@/pages/OrderDetailsPage.vue'),
    meta: { requiresAuth: true, restrictViewer: true },
  },
  {
    path: '/airman',
    name: 'AirmanOrders',
    component: () => import('@/pages/AirmanOrdersPage.vue'),
    meta: { requiresAuth: true, restrictViewer: true },
  },
  {
    path: '/airman/new',
    name: 'CreateAirmanOrder',
    component: () => import('@/pages/CreateAirmanOrderPage.vue'),
    meta: { requiresAuth: true, restrictViewer: true },
  },
  {
    path: '/airman/:id/edit',
    name: 'EditAirmanOrder',
    component: () => import('@/pages/CreateAirmanOrderPage.vue'),
    meta: { requiresAuth: true, restrictViewer: true },
  },
  {
    path: '/airman/:id',
    name: 'AirmanOrderDetails',
    component: () => import('@/pages/OrderDetailsPage.vue'),
    meta: { requiresAuth: true, restrictViewer: true },
  },
  {
    path: '/customers',
    name: 'Customers',
    component: () => import('@/pages/CustomersPage.vue'),
    meta: { requiresAuth: true, restrictViewer: true },
  },
  {
    path: '/history',
    name: 'CustomerHistory',
    component: () => import('@/pages/CustomerHistoryPage.vue'),
    meta: { requiresAuth: true, restrictViewer: true },
  },
  {
    path: '/staff',
    name: 'Workers',
    component: () => import('@/pages/WorkersPage.vue'),
    meta: { requiresAuth: true },
  },
  {
    path: '/staff/summary',
    name: 'StaffSummary',
    component: () => import('@/pages/StaffSummaryPage.vue'),
    meta: { requiresAuth: true },
  },
  {
    path: '/staff/:id',
    alias: ['/staff/:id/entries', '/workers/:id/entries'],
    name: 'WorkerEntries',
    component: () => import('@/pages/WorkerEntriesPage.vue'),
    meta: { requiresAuth: true },
  },
  {
    path: '/delivery',
    name: 'Delivery',
    component: () => import('@/pages/DeliveryPage.vue'),
    meta: { requiresAuth: true, restrictViewer: true },
  },
  {
    path: '/delivery/:id',
    name: 'DeliveryOrderDetails',
    component: () => import('@/pages/DeliveryPage.vue'),
    meta: { requiresAuth: true, restrictViewer: true },
  },
  {
    path: '/delivery/:id/confirm',
    name: 'DeliveryConfirm',
    component: () => import('@/pages/DeliveryPage.vue'),
    meta: { requiresAuth: true, restrictViewer: true },
  },
  {
    path: '/dashboard',
    name: 'Dashboard',
    component: () => import('@/pages/DashboardPage.vue'),
    meta: { requiresAuth: true, restrictViewer: true },
  },
  {
    path: '/report',
    name: 'DashboardResults',
    component: () => import('@/pages/DashboardResultsPage.vue'),
    meta: { requiresAuth: true, restrictViewer: true },
  },
  {
    path: '/settings',
    name: 'Settings',
    component: () => import('@/pages/SettingsPage.vue'),
    meta: { requiresAuth: true },
  },
  {
    path: '/:pathMatch(.*)*',
    name: 'NotFound',
    component: () => import('@/pages/NotFound.vue'),
  },
];

const router = createRouter({
  history: createWebHistory(),
  routes,
  scrollBehavior() {
    return { top: 0 };
  },
});

router.beforeEach((to, from, next) => {
  const token = authState.token || localStorage.getItem('tailorpro_token');

  // If public route, allow
  if (to.meta.public) {
    if (token && to.path === '/login') {
      return next(authState.isViewer ? '/staff' : '/orders');
    }
    return next();
  }

  // If requires auth and no token, redirect to login
  if (!token) {
    return next('/login');
  }

  // If viewer tries to access restricted routes
  if (authState.isViewer && to.meta.restrictViewer) {
    return next('/staff');
  }

  next();
});

router.afterEach((to) => {
  updateSeo(to);
});

export default router;
