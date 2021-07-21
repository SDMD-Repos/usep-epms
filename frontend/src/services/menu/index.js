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
    children: [
      {
        title: 'Form',
        key: 'aapcrForm',
        url: '/form/aapcr',
      },
      {
        title: 'List',
        key: 'aapcrList',
        url: '/list/aapcr',
      },
    ],
  },
  {
    title: 'OPCR (VP)',
    key: 'vpopcr',
    icon: 'fe fe-file-text',
    children: [
      {
        title: 'Form',
        key: 'opcrVpForm',
        url: '/form/opcrvp',
      },
      {
        title: 'List',
        key: 'opcrVpList',
        url: '/list/opcrvp',
      },
    ],
  },
  {
    title: 'OPCR',
    key: 'opcr',
    icon: 'fe fe-file',
    children: [
      {
        title: 'Form',
        key: 'opcrForm',
        url: '/form/opcr',
      },
      {
        title: 'List',
        key: 'opcrList',
        url: '/list/opcr',
      },
    ],
  },
  {
    title: 'CPCR',
    key: 'cpcr',
    icon: 'fe fe-edit-3',
    children: [
      {
        title: 'Form',
        key: 'cpcrForm',
        url: '/form/cpcr',
      },
      {
        title: 'List',
        key: 'cpcrList',
        url: '/list/cpcr',
      },
    ],
  },
  {
    title: 'IPCR',
    key: 'ipcr',
    icon: 'fe fe-edit-2',
    children: [
      {
        title: 'Form',
        key: 'ipcrForm',
        url: '/form/ipcr',
      },
      {
        title: 'List',
        key: 'ipcrList',
        url: '/list/ipcr',
      },
    ],
  },
]
