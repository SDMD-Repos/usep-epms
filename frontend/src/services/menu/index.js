export const getMenuData = [
  // VB:REPLACE-START:MENU-CONFIG
  {
    title: 'Dashboard',
    key: 'dashboard',
    icon: 'fe fe-home',
    url: '/dashboard',
    role: ["all"],
  },
  {
    category: true,
    title: 'Manager',
    role: ["manager"],
  },
  {
    title: 'Form',
    key: 'managerForm',
    icon: 'fe fe-settings',
    url: '/manager/form',
    role: ["manager"],
  },
  {
    title: 'Groups',
    key: 'managerGroups',
    icon: 'fe fe-users',
    url: '/manager/groups',
    role: ["manager"],
  },
  {
    title: 'Measures',
    key: 'managerMeasures',
    icon: 'fa fa-scale-balanced',
    url: '/manager/measures',
    role: ["manager"],
  },
  {
    title: 'Signatories',
    key: 'managerSignatories',
    icon: 'fa fa-signature',
    url: '/manager/signatories',
    role: ["manager"],

  },
  {
    category: true,
    title: 'Forms',
    role: ["form", "adminPermission"],
  },
  {
    title: 'AAPCR',
    key: 'aapcr',
    icon: 'fe fe-globe',
    role: ["f-aapcr", "adminPermission"],
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
    role: ["f-opcrvp", "adminPermission"],
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
    role: ["adminPermission"],
  },
  {
    title: 'Access Rights',
    key: 'systemAdmin',
    icon: 'fe fe-lock',
    role: ["adminPermission"],
    children: [
      {
        title: 'Manager',
        key: 'systemAdminManager',
        url: '/system/admin/permisson',
        // role: ["adminPermission"],
      },
      {
        title: 'Form',
        key: 'systemAdminForm',
        url: '/system/admin/formaccess',
        // role: ['adminPermission'],
      },
    ],
  },
  {
    title: 'Requests',
    key: 'requestManager',
    icon: 'fe fe-archive',
    url: '/system/admin/requests',
    role: ["adminRequests"],
  },

  // VB:REPLACE-END:MENU-CONFIG
]

// Function to filter menu items based on users role
export function filterMenuByAccessRights(access, MenuData) {
  const filteredMenu = [];

  if(access.value.accessRights.length<=0) return filteredMenu

  MenuData.forEach(item => {
    let isAccess = item.role.some(role => access.value.accessRights.some(accessRight => accessRight.permission_id == role));
    const newItem = { ...item };
    if (isAccess) {
      if (newItem.children) {
        // Filter child items based on users access rights
        newItem.children = newItem.children.filter(childItem => {
          // Check if any role in childItem.roles is accessible to the user
          return !childItem.role || childItem.role.some(role => access.value.accessRights.some(accessRight => accessRight.permission_id == role));
        });
        if (newItem.children.length > 0) {
          filteredMenu.push(newItem);
        }
      } else {
        filteredMenu.push(newItem);
      }
    }else if(item.role=="all") {
      filteredMenu.push(newItem);
    }
  })
  return filteredMenu;
}