<template>
  <div>
    <div class="card" :class="$style.container">
      <div class="text-dark font-size-32 mb-3">Sign In</div>
      <div class="mb-4">PMAPS ID and Password</div>
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
    </div>
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
@import "@/components/system/Auth/style.module.scss";
</style>
