export const getMenuData = [
  {
    category: true,
    title: 'Dashboards',
  },
  {
    title: 'Dashboard',
    key: 'dashboard',
    icon: 'fe fe-home',
    url: '/dashboard/about',
  },
  {
    title: 'Settings',
    key: 'settings',
    icon: 'fe fe-settings',
    children: [
      {
        title: 'Form',
        key: 'settingsForm',
        url: '/settings/form',
      },
      {
        title: 'Measures',
        key: 'settingsMeasures',
        url: '/settings/measures',
      },
      {
        title: 'Signatories',
        key: 'settingsSignatories',
        url: '/settings/signatories',
      },
      {
        title: 'Others',
        key: 'settingsOthers',
        url: '/settings/others',
      },
    ],
  },
]
