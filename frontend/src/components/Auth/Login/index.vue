<template>
  <div class="mt-5 pt-2">
    <div class="card" :class="$style.container">
      <div class="text-dark font-size-32 mb-3">Sign In</div>
      <div class="mb-4">
        PMAPS ID and Password
        <br />
      </div>
      <a-form
        :model="loginForm"
        :rules="rules"
        layout="vertical"
        class="mb-4"
        @finish="handleFinish"
        @finishFailed="handleFinishFailed"
      >
        <a-form-item name="pmapsId">
          <a-input v-model:value="loginForm.pmapsId" placeholder="PMAPS ID" />
        </a-form-item>
        <a-form-item name="password">
          <a-input-password v-model:value="loginForm.password" placeholder="Password" />
        </a-form-item>
        <a-button type="primary" html-type="submit" class="text-center w-100" :loading="loading">
          <strong>Sign in</strong>
        </a-button>
      </a-form>
    </div>
    <div class="text-center pt-2 mb-auto">
      <span class="mr-2">Don't have an account?</span>
      <a href="https://hris.usep.edu.ph/hris/" class="vb__utils__link">
        Sign up
      </a>
    </div>
  </div>
</template>
<script>
import { computed, reactive } from 'vue'
import { useStore } from 'vuex'

export default {
  name: 'VbLogin',
  setup() {
    const store = useStore()
    const settings = computed(() => store.getters.settings)
    const loading = computed(() => store.getters['user/user'].loading)
    const rules = {
      pmapsId: [
        {
          required: true,
          message: 'Please input your PMAPS ID!',
          trigger: 'change',
        },
      ],
      password: [{ required: true, message: 'Please input password!', trigger: 'change' }],
    }
    const loginForm = reactive({
      pmapsId: '',
      password: '',
    })

    const changeAuthProvider = value => {
      store.commit('CHANGE_SETTING', { setting: 'authProvider', value })
    }
    const handleFinish = values => {
      store.dispatch('user/LOGIN', { payload: values })
    }
    const handleFinishFailed = errors => {
      console.log(errors)
    }

    return {
      settings,
      loading,
      rules,
      loginForm,
      changeAuthProvider,
      handleFinish,
      handleFinishFailed,
    }
  },
  // data: function () {
  //   return {
  //     rules: {
  //       email: [{ required: true, message: 'Please input your email!', trigger: 'change' }],
  //       password: [{ required: true, message: 'Please input password!', trigger: 'change' }],
  //     },
  //     loginForm: {
  //       email: 'demo@visualbuilder.cloud',
  //       password: 'VisualBuilder',
  //     },
  //   }
  // },
  // computed: {
  //   ...mapState(['settings']),
  //   loading() {
  //     return this.$store.state.user.loading
  //   },
  // },
  // methods: {
  //   changeAuthProvider(value) {
  //     this.$store.commit('CHANGE_SETTING', { setting: 'authProvider', value })
  //   },
  //   handleFinish(values) {
  //     this.$store.dispatch('user/LOGIN', { payload: values })
  //   },
  //   handleFinishFailed(errors) {
  //     console.log(errors)
  //   },
  // },
}
</script>
<style lang="scss" module>
@import '@/components/Auth/style.module.scss';
</style>
