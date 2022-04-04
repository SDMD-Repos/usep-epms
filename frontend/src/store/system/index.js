import { notification } from 'ant-design-vue'

import * as system from '@/services/api/system/permission'

const mapApiProviders = {
    getAllPermissionList: system.getAllPermissionList,
    savePermissionList: system.savePermissionList,
    updatePermissionList: system.updatePermissionList,
    saveOfficeHead: system.saveOfficeHead,
    fetchOfficeHead: system.fetchOfficeHead,
    saveOfficeStaff: system.saveOfficeStaff,
    checkAccessPermission: system.checkAccessPermission,
    checkFormHeadPermission: system.checkFormHeadPermission,
}

export default {
    namespaced: true,
    state: {
      loading: false,
      opcrFormPermission: false,
      list: [],
      officeHeadDetails: [],
      createFormPermission: false,
      deleteFormPermission: false,
      createProgramPermission: false,
      deleteProgramPermission: false,
      createSubCatPermission: false,
      deleteSubCatPermission: false,
      createFieldPermission: false,
      createGroupPermission: false,
      editGroupPermission: false,
      deleteGroupPermission: false,
      createMeasuresPermission: false,
      editMeasuresPermission: false,
      deleteMeasuresPermission: false,
      createAapcrPermission: false,
      aapcrHeadPermission: false,
      aapcrFormPermission: false,
      opcrHeadPermission: false,
    },
    mutations: {
      SET_STATE(state, payload) {
        Object.assign(state, {
          ...payload,
        })
      },
      SET_PERMISSION_STATE(state, payload) {
        const { name,hasPermission }=payload
        state[name]=hasPermission
      },
    },
    actions: {
        FETCH_PERMISSION({ commit }) {
            commit('SET_STATE', {
              loading: true,
            })
            const getAccessPermission = mapApiProviders.getAllPermissionList
            getAccessPermission().then(response => {
              if (response) {
                const { accessPermission } = response
                commit('SET_STATE', {
                  list: accessPermission,
                })
              }
              commit('SET_STATE', {
                loading: false,
              })
            })
          },
          SAVE_PERMISSION({ commit }, { payload }) {
            commit('SET_STATE', {
              loading: true,
            })
            const savePermissionList = mapApiProviders.savePermissionList
            savePermissionList(payload).then(response => {
              if (response) {
                  notification.success({
                  message: 'Success',
                  description: 'Access Rights was saved successfully',
                })
              }
              commit('SET_STATE', {
                loading: false,
              })
            })
          },
          UPDATE_PERMISSION({ commit }, { payload }) {
            commit('SET_STATE', {
              loading: true,
            })
            const updatePermissionList = mapApiProviders.updatePermissionList
            updatePermissionList(payload).then(response => {
              if (response) {
                  notification.success({
                  message: 'Success',
                  description: 'Access Rights was saved successfully',
                })
              }
              commit('SET_STATE', {
                loading: false,
              })
            })
          },
          CHECK_OPCR_FORM_PERMISSION({ commit }, { payload }) {
            commit('SET_STATE', {
              loading: true,
            })
            const checkAccessPermission = mapApiProviders.checkAccessPermission
            checkAccessPermission(payload).then(response => {
              if (response) {
                const { hasPermission } = response
                commit('SET_STATE', {
                  opcrFormPermission: hasPermission,
                })
              }
              commit('SET_STATE', {
                loading: false,
              })
            })
          },

        FETCH_AAPCR_HEAD({ commit }, { payload }) {
          commit('SET_STATE', {
            loading: true,
          })
          const fetchOfficeHead = mapApiProviders.fetchOfficeHead
          fetchOfficeHead(payload.form_id).then(response => {
            if (response) {

              commit('SET_STATE', {
                officeHeadDetails : {
                            pmaps_id: response.officeHeadDetails.pmaps_id,
                            pmaps_name: response.officeHeadDetails.pmaps_name,
                            office_id: response.officeHeadDetails.office_id,
                            office_name: response.officeHeadDetails.office_name,
                            staff_id: response.officeHeadDetails.staff_id,
                            staff_name: response.officeHeadDetails.staff_name,
                },
              })
            }
            commit('SET_STATE', {
              loading: false,
            })
          })
      },
      SAVE_FORM_HEAD({ commit,dispatch  }, { payload }) {
        commit('SET_STATE', {
          loading: true,
        })
        const { form_id } = payload
        const saveOfficeHead = mapApiProviders.saveOfficeHead
        saveOfficeHead(payload).then(response => {
          if (response) {
            dispatch('FETCH_AAPCR_HEAD',{payload:{form_id:'aapcr'}})
              notification.success({
              message: 'Success',
              description: form_id.toUpperCase() + ' Head has been assigned.',
            })
          }
          commit('SET_STATE', {
            loading: false,
          })
        })
    },
      SAVE_FORM_STAFF({ commit, dispatch }, { payload }) {
        commit('SET_STATE', {
          loading: true,
        })
        const { form_id } = payload
        const saveOfficeStaff = mapApiProviders.saveOfficeStaff
        saveOfficeStaff(payload).then(response => {
          if (response) {
            dispatch('FETCH_AAPCR_HEAD',{payload:{form_id:'aapcr'}})
              notification.success({
              message: 'Success',
              description: form_id.toUpperCase() + ' Staff has been assigned.',
            })
          }
          commit('SET_STATE', {
            loading: false,
          })
        })
    },
    // CHECK_CREATE_FORM_PERMISSION({ commit }, { payload }) {
    //   commit('SET_STATE', {
    //     loading: true,
    //   })
    //   const checkAccessPermission = mapApiProviders.checkAccessPermission
    //   checkAccessPermission(payload).then(response => {
    //     if (response) {
    //       const { permissions } = response
    //       commit('SET_STATE', {
    //         createFormPermission: permissions,
    //       })
    //     }
    //     commit('SET_STATE', {
    //       loading: false,
    //     })
    //   })
    // },
    // CHECK_DELETE_FORM_PERMISSION({ commit }, { payload }) {
    //   commit('SET_STATE', {
    //     loading: true,
    //   })
    //   const checkAccessPermission = mapApiProviders.checkAccessPermission
    //   checkAccessPermission(payload).then(response => {
    //     if (response) {
    //       const { permissions } = response
    //       commit('SET_STATE', {
    //         deleteFormPermission: permissions,
    //       })
    //     }
    //     commit('SET_STATE', {
    //       loading: false,
    //     })
    //   })
    // },
    // CHECK_CREATE_PROGRAM_PERMISSION({ commit }, { payload }) {
    //   commit('SET_STATE', {
    //     loading: true,
    //   })
    //   const checkAccessPermission = mapApiProviders.checkAccessPermission
    //   checkAccessPermission(payload).then(response => {
    //     if (response) {
    //       const { permissions } = response
    //       commit('SET_STATE', {
    //         createProgramPermission: permissions,
    //       })
    //     }
    //     commit('SET_STATE', {
    //       loading: false,
    //     })
    //   })
    // },
    // CHECK_DELETE_PROGRAM_PERMISSION({ commit }, { payload }) {
    //   commit('SET_STATE', {
    //     loading: true,
    //   })
    //   const checkAccessPermission = mapApiProviders.checkAccessPermission
    //   checkAccessPermission(payload).then(response => {
    //     if (response) {
    //       const { permissions } = response
    //       commit('SET_STATE', {
    //         deleteProgramPermission: permissions,
    //       })
    //     }
    //     commit('SET_STATE', {
    //       loading: false,
    //     })
    //   })
    // },
    // CHECK_CREATE_SUBCAT_PERMISSION({ commit }, { payload }) {
    //   commit('SET_STATE', {
    //     loading: true,
    //   })
    //   const checkAccessPermission = mapApiProviders.checkAccessPermission
    //   checkAccessPermission(payload).then(response => {
    //     if (response) {
    //       const { permissions } = response
    //       commit('SET_STATE', {
    //         createSubCatPermission: permissions,
    //       })
    //     }
    //     commit('SET_STATE', {
    //       loading: false,
    //     })
    //   })
    // },
    // CHECK_DELETE_SUBCAT_PERMISSION({ commit }, { payload }) {
    //   commit('SET_STATE', {
    //     loading: true,
    //   })
    //   const checkAccessPermission = mapApiProviders.checkAccessPermission
    //   checkAccessPermission(payload).then(response => {
    //     if (response) {
    //       const { permissions } = response
    //       commit('SET_STATE', {
    //         deleteSubCatPermission: permissions,
    //       })
    //     }
    //     commit('SET_STATE', {
    //       loading: false,
    //     })
    //   })
    // },
    // CHECK_CREATE_FIELD_PERMISSION({ commit }, { payload }) {
    //   commit('SET_STATE', {
    //     loading: true,
    //   })
    //   const checkAccessPermission = mapApiProviders.checkAccessPermission
    //   checkAccessPermission(payload).then(response => {
    //     if (response) {
    //       const { permissions } = response
    //       commit('SET_STATE', {
    //         createFieldPermission: permissions,
    //       })
    //     }
    //     commit('SET_STATE', {
    //       loading: false,
    //     })
    //   })
    // },
    // CHECK_CREATE_GROUP_PERMISSION({ commit }, { payload }) {
    //   commit('SET_STATE', {
    //     loading: true,
    //   })
    //   const checkAccessPermission = mapApiProviders.checkAccessPermission
    //   checkAccessPermission(payload).then(response => {
    //     if (response) {
    //       const { permissions } = response
    //       commit('SET_STATE', {
    //         createGroupPermission: permissions,
    //       })
    //     }
    //     commit('SET_STATE', {
    //       loading: false,
    //     })
    //   })
    // },
    // CHECK_EDIT_GROUP_PERMISSION({ commit }, { payload }) {
    //   commit('SET_STATE', {
    //     loading: true,
    //   })
    //   const checkAccessPermission = mapApiProviders.checkAccessPermission
    //   checkAccessPermission(payload).then(response => {
    //     if (response) {
    //       const { permissions } = response
    //       commit('SET_STATE', {
    //         editGroupPermission: permissions,
    //       })
    //     }
    //     commit('SET_STATE', {
    //       loading: false,
    //     })
    //   })
    // },
    // CHECK_DELETE_GROUP_PERMISSION({ commit }, { payload }) {
    //   commit('SET_STATE', {
    //     loading: true,
    //   })
    //   const checkAccessPermission = mapApiProviders.checkAccessPermission
    //   checkAccessPermission(payload).then(response => {
    //     if (response) {
    //       const { permissions } = response
    //       commit('SET_STATE', {
    //         deleteGroupPermission: permissions,
    //       })
    //     }
    //     commit('SET_STATE', {
    //       loading: false,
    //     })
    //   })
    // },
    // CHECK_CREATE_MEASURES_PERMISSION({ commit }, { payload }) {
    //   commit('SET_STATE', {
    //     loading: true,
    //   })
    //   const checkAccessPermission = mapApiProviders.checkAccessPermission
    //   checkAccessPermission(payload).then(response => {
    //     if (response) {
    //       const { permissions } = response
    //       commit('SET_STATE', {
    //         createMeasuresPermission: permissions,
    //       })
    //     }
    //     commit('SET_STATE', {
    //       loading: false,
    //     })
    //   })
    // },
    // CHECK_EDIT_MEASURES_PERMISSION({ commit }, { payload }) {
    //   commit('SET_STATE', {
    //     loading: true,
    //   })
    //   const checkAccessPermission = mapApiProviders.checkAccessPermission
    //   checkAccessPermission(payload).then(response => {
    //     if (response) {
    //       const { permissions } = response
    //       commit('SET_STATE', {
    //         editMeasuresPermission: permissions,
    //       })
    //     }
    //     commit('SET_STATE', {
    //       loading: false,
    //     })
    //   })
    // },
    // CHECK_DELETE_MEASURES_PERMISSION({ commit }, { payload }) {
    //   commit('SET_STATE', {
    //     loading: true,
    //   })
    //   const checkAccessPermission = mapApiProviders.checkAccessPermission
    //   checkAccessPermission(payload).then(response => {
    //     if (response) {
    //       const { permissions } = response
    //       commit('SET_STATE', {
    //         deleteMeasuresPermission: permissions,
    //       })
    //     }
    //     commit('SET_STATE', {
    //       loading: false,
    //     })
    //   })
    // },
    // CHECK_CREATE_AAPCR_PERMISSION({ commit }, { payload }) {
    //   commit('SET_STATE', {
    //     loading: true,
    //   })
    //   const checkAccessPermission = mapApiProviders.checkAccessPermission
    //   checkAccessPermission(payload).then(response => {
    //     if (response) {
    //       const { permissions } = response
    //       commit('SET_STATE', {
    //         createAapcrPermission: permissions,
    //       })
    //     }
    //     commit('SET_STATE', {
    //       loading: false,
    //     })
    //   })
    // },
    CHECK_OPCR_HEAD_PERMISSION({ commit }, { payload }) {
      const { pmaps_id,form_id } = payload
      commit('SET_STATE', {
        loading: true,
      })
      const checkFormHeadPermission = mapApiProviders.checkFormHeadPermission
      checkFormHeadPermission(pmaps_id,form_id).then(response => {
        if (response) {
          const { permission } = response
          commit('SET_STATE', {
            opcrHeadPermission: permission,
          })
        }
        commit('SET_STATE', {
          loading: false,
        })
      })
    },
    CHECK_APCR_HEAD_PERMISSION({ commit }, { payload }) {
      const { pmaps_id,form_id } = payload
      commit('SET_STATE', {
        loading: true,
      })
      const checkFormHeadPermission = mapApiProviders.checkFormHeadPermission
      checkFormHeadPermission(pmaps_id,form_id).then(response => {
        if (response) {
          const { permission } = response
          console.log(permission)
          commit('SET_STATE', {
            aapcrHeadPermission: permission,
          })
        }
        commit('SET_STATE', {
          loading: false,
        })
      })
    },
    CHECK_AAPCR_FORM_PERMISSION({ commit }, { payload }) {

      commit('SET_STATE', {
        loading: true,
      })
      const checkAccessPermission = mapApiProviders.checkAccessPermission
      checkAccessPermission(payload).then(response => {
        if (response) {
          const { hasPermission } = response
          commit('SET_STATE', {
            aapcrFormPermission: hasPermission,
          })
        }
        commit('SET_STATE', {
          loading: false,
        })
      })
    },
    CHECK_PERMISSION({ commit }, { payload }) {
      commit('SET_STATE', {
        loading: true,
      })
      const { permission,name } = payload
      const checkAccessPermission = mapApiProviders.checkAccessPermission
      checkAccessPermission(permission).then(response => {
        if (response) {
          const { hasPermission } = response
          commit('SET_PERMISSION_STATE', {
            name: name,
            hasPermission: hasPermission,
          })
        }
        commit('SET_STATE', {
          loading: false,
        })
      })
    },
    },

    getters: {
      permission: state => state,
    },

}
