export const getMenuData = [
  // VB:REPLACE-START:MENU-CONFIG
  {
    title: 'Dashboard',
    key: 'dashboard',
    icon: 'fe fe-home',
    url: '/dashboard',
  },
  {
    category: true,
    title: 'Manager',
  },
  {
    title: 'Form',
    key: 'managerForm',
    icon: 'fe fe-settings',
    url: '/manager/form',
  },
  {
    title: 'Groups',
    key: 'managerGroups',
    icon: 'fe fe-users',
    url: '/manager/groups',
  },
  {
    title: 'Measures',
    key: 'managerMeasures',
    icon: 'fa fa-scale-balanced',
    url: '/manager/measures',
  },
  {
    title: 'Signatories',
    key: 'managerSignatories',
    icon: 'fa fa-signature',
    url: '/manager/signatories',
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
        key: 'vpOpcrForm',
        url: '/form/vpopcr',
      },
      {
        title: 'List',
        key: 'opcrVpList',
        url: '/list/vpopcr',
      },
    ],
  },
  // {
  //   title: 'OPCR',
  //   key: 'opcr',
  //   icon: 'fa fa-file-pen',
  //   children: [
  //     {
  //       title: 'Manager',
  //       key: 'opcrManager',
  //       children: [
  //         {
  //           title: 'Form',
  //           key: 'opcrFormManager',
  //           url: '/opcr/manager',
  //           level: 3,
  //         },
  //       ],
  //     },
  //     {
  //       title: 'Template',
  //       key: 'opcrTemplate',
  //       children: [
  //         {
  //           title: 'Form',
  //           key: 'opcrTemplateForm',
  //           url: '/form/opcrtemplate',
  //           level: 3,
  //         },
  //         {
  //           title: 'List',
  //           key: 'opcrTemplateList',
  //           url: '/list/opcrtemplate',
  //           level: 3,
  //         },
  //       ],
  //     },
  //     {
  //       title: 'Form',
  //       key: 'opcrForm',
  //       url: '/form/opcr',
  //     },
  //     {
  //       title: 'List',
  //       key: 'opcrList',
  //       url: '/list/opcr',
  //     },
  //   ],
  // },
  // {
  //   title: 'CPCR',
  //   key: 'cpcr',
  //   icon: 'fe fe-edit-3',
  //   children: [
  //     {
  //       title: 'Form',
  //       key: 'cpcrForm',
  //       url: '/form/cpcr',
  //     },
  //     {
  //       title: 'List',
  //       key: 'cpcrList',
  //       url: '/list/cpcr',
  //     },
  //   ],
  // },
  // {
  //   title: 'IPCR',
  //   key: 'ipcr',
  //   icon: 'fa fa-file-lines',
  //   children: [
  //     {
  //       title: 'Form',
  //       key: 'ipcrForm',
  //       url: '/form/ipcr',
  //     },
  //     {
  //       title: 'List',
  //       key: 'ipcrList',
  //       url: '/list/ipcr',
  //     },
  //   ],
  // },
  {
    category: true,
    title: 'System Admin',
  },
  {
    title: 'Access Rights',
    key: 'systemAdmin',
    icon: 'fe fe-lock',
    children: [
      {
        title: 'Manager',
        key: 'systemAdminManager',
        url: '/system/admin/permisson',
      },
      {
        title: 'Form',
        key: 'systemAdminForm',
        url: '/system/admin/formaccess',
      },
    ],
  },
  {
    title: 'Requests',
    key: 'requestManager',
    icon: 'fe fe-archive',
    url: '/system/admin/requests',
  },

  // VB:REPLACE-END:MENU-CONFIG
]
