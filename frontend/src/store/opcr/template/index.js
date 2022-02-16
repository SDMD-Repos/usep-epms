import { notification } from 'ant-design-vue'

// import * as aapcrForm from '@/services/api/mainForms/aapcr'

const mapApiProviders = {
  // save: aapcrForm.save,
  // getAapcrList: aapcrForm.fetchAapcrs,
  // publish: aapcrForm.publish,
  // unpublish: aapcrForm.unpublish,
  // deactivate: aapcrForm.deactivate,
  // update: aapcrForm.update,
}

export default {
  namespaced: true,
  state: {
    loading: false,
    list: [],
    fileUrl: null,
    documentName: null,
    dataSource: [],
  },
  mutations: {
    SET_STATE(state, payload) {
      Object.assign(state, {
        ...payload,
      })
    },

  },
  actions: {

  },
  getters: {
    form: state => state,
  },
}
