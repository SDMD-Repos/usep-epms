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
    ],
  },
  {
    category: true,
    title: 'Forms',
  },
  {
    title: 'AAPCR',
    key: 'aapcr',
    icon: 'fe fe-globe',
    url: '/manager/aapcr',
    children: [
      {
        title: 'Create New',
        key: 'aapcrNew',
        url: '/aapcr/new',
      },
      {
        title: 'List',
        key: 'aapcrList',
        url: '/aapcr/list',
      },
    ],
  },
  {
    title: 'OPCR (VP)',
    key: 'vpopcr',
    icon: 'fe fe-file-text',
    url: '/manager/opcrvp',
    children: [
      {
        title: 'Create New',
        key: 'opcrVpNew',
        url: '/opcrvp/new',
      },
      {
        title: 'List',
        key: 'opcrVpList',
        url: '/opcrvp/list',
      },
    ],
  },
  {
    title: 'OPCR',
    key: 'opcr',
    icon: 'fe fe-file',
    url: '/manager/opcr',
    children: [
      {
        title: 'Create New',
        key: 'opcrNew',
        url: '/opcr/new',
      },
      {
        title: 'List',
        key: 'opcrList',
        url: '/opcr/list',
      },
    ],
  },
  {
    title: 'CPCR',
    key: 'cpcr',
    icon: 'fe fe-edit-3',
    url: '/manager/cpcr',
    children: [
      {
        title: 'Create New',
        key: 'cpcrNew',
        url: '/cpcr/new',
      },
      {
        title: 'List',
        key: 'cpcrList',
        url: '/cpcr/list',
      },
    ],
  },
  {
    title: 'IPCR',
    key: 'ipcr',
    icon: 'fe fe-edit-2',
    url: '/manager/ipcr',
    children: [
      {
        title: 'Create New',
        key: 'ipcrNew',
        url: '/ipcr/new',
      },
      {
        title: 'List',
        key: 'ipcrList',
        url: '/ipcr/list',
      },
    ],
  },
]
