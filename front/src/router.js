import { createRouter, createWebHistory } from 'vue-router'
import { can, session } from './session'

/** Menu and routes; each section is shown only to roles with one of its permissions. */
export const sections = [
  { path: '/capacity', label: 'Capacidad', permissions: ['CAPACITY_VIEW'], component: () => import('./views/CapacityView.vue') },
  { path: '/shelves', label: 'Estanterías', permissions: ['SHELF_MANAGE'], component: () => import('./views/ShelvesView.vue') },
  { path: '/catalog', label: 'Catálogo', permissions: ['BOOK_REGISTER', 'COPY_MANAGE'], component: () => import('./views/CatalogView.vue') },
  { path: '/loans', label: 'Préstamos', permissions: ['LOAN_MANAGE'], component: () => import('./views/LoansView.vue') },
  { path: '/readers', label: 'Lectores', permissions: ['READER_REGISTER', 'LOAN_VIEW'], component: () => import('./views/ReadersView.vue') },
  { path: '/users', label: 'Usuarios', permissions: ['USER_MANAGE'], component: () => import('./views/UsersView.vue') },
  { path: '/settings', label: 'Configuración', permissions: ['SETTINGS_UPDATE'], component: () => import('./views/SettingsView.vue') },
]

export function allowedSections() {
  return sections.filter((s) => can(...s.permissions))
}

export const router = createRouter({
  history: createWebHistory(),
  routes: [
    { path: '/login', component: () => import('./views/LoginView.vue'), meta: { public: true } },
    ...sections.map((s) => ({ path: s.path, component: s.component, meta: { permissions: s.permissions } })),
    { path: '/:pathMatch(.*)*', redirect: () => allowedSections()[0]?.path ?? '/login' },
  ],
})

router.beforeEach((to) => {
  if (!session.token) return to.meta.public ? true : '/login'
  if (to.meta.public) return allowedSections()[0]?.path ?? true
  if (to.meta.permissions && !can(...to.meta.permissions)) return allowedSections()[0]?.path ?? '/login'
  return true
})
