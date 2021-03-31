<template>
  <div>
    <div class="card" :class="$style.container">
      <div class="text-dark font-size-32 mb-3">Sign In</div>
      <div class="mb-4">PMAPS ID and Password</div>
      <!-- <div class="mb-4">
        <a-radio-group
          :value="settings.authProvider"
          @change="e => changeAuthProvider(e.target.value)"
        >
          <a-radio value="firebase">Firebase</a-radio>
          <a-radio value="jwt">JWT</a-radio>
          <a-tooltip>
            <template slot="title">
              <span>Read Docs Guide</span>
            </template>
            <a-radio value="auth0" disabled>Auth0</a-radio>
          </a-tooltip>
          <a-tooltip>
            <template slot="title">
              <span>Read Docs Guide</span>
            </template>
            <a-radio value="strapi" disabled>Strapi</a-radio>
          </a-tooltip>
        </a-radio-group>
      </div> -->
      <a-form class="mb-4" :form="form" @submit="handleSubmit">
        <a-form-item>
          <a-input
            size="large"
            placeholder="PMAPS ID"
            v-decorator="['pmapsId', {rules: [{ required: true, message: 'Please input your PMAPS ID!' }]}]"
          />
        </a-form-item>
        <a-form-item>
          <a-input
            size="large"
            placeholder="Password"
            type="password"
            v-decorator="['password', {rules: [{ required: true, message: 'Please input your Password!' }]}]"
          />
        </a-form-item>
        <a-button
          type="primary"
          htmlType="submit"
          size="large"
          class="text-center w-100"
          :loading="loading"
        >
          <strong>Sign in</strong>
        </a-button>
      </a-form>
      <!-- <router-link to="/auth/forgot-password" class="kit__utils__link font-size-16">Forgot Password?</router-link> -->
    </div>
    <!-- <div class="text-center pt-2 mb-auto">
      <span class="mr-2">Don't have an account?</span>
      <router-link to="/auth/register" class="kit__utils__link font-size-16">Sign up</router-link>
    </div> -->
  </div>
</template>
<script>
import { mapState } from 'vuex'

export default {
  name: 'CuiLogin',
  computed: {
    ...mapState(['settings']),
    loading() {
      return this.$store.state.user.loading
    },
  },
  data: function () {
    return {
      form: this.$form.createForm(this),
    }
  },
  methods: {
    changeAuthProvider(value) {
      this.$store.commit('CHANGE_SETTING', { setting: 'authProvider', value })
    },
    handleSubmit(e) {
      e.preventDefault()
      this.form.validateFields((err, values) => {
        if (!err) {
          this.$store.dispatch('user/LOGIN', { payload: values })
        }
      })
    },
  },
}
</script>
<style lang="scss" module>
@import "@/@airui/system/Auth/style.module.scss";
</style>
