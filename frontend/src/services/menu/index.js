export const getMenuData = [
  // VB:REPLACE-START:MENU-CONFIG
  {
    category: true,
    title: 'Dashboards',
  },
  {
    title: 'Dashboard',
    key: 'dashboard',
    icon: 'fe fe-home',
    url: '/dashboard',
  },
  {
    title: 'Manager',
    key: 'manager',
    icon: 'fe fe-settings',
    children: [
      {
        title: 'Form',
        key: 'managerForm',
        url: '/manager/form',
      },
      {
        title: 'Groups',
        key: 'managerGroups',
        url: '/manager/groups',
      },
      {
        title: 'Measures',
        key: 'managerMeasures',
        url: '/manager/measures',
      },
      {
        title: 'Signatories',
        key: 'managerSignatories',
        url: '/manager/signatories',
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
        title: 'Manager',
        key: 'opcrManager',
        children: [
          {
            title: 'Programs',
            key: 'opcrProgram',
            url: '/manager/opcr',
            level: 3,
          },
        ],
      },
      {
        title: 'Template',
        key: 'opcrTemplate',
        children: [
          {
            title: 'Form 1',
            key: 'opcrTemplateForm_1',
            url: '/form/opcrtemplate',
            level: 3,
          },
          {
            title: 'List 1',
            key: 'opcrTemplateList_1',
            url: '/list/opcrtemplate',
            level: 3,
          },
        ],
      },
      {
        title: 'Form',
        key: 'opcrForm',
        url: '/form/opcrsss',
      },
      {
        title: 'List',
        key: 'opcrList',
        url: '/list/opcrsss',
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

  // VB:REPLACE-END:MENU-CONFIG
]
