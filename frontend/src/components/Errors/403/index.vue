<template>
  <div class="p-5 font-size-30">
    <div class="font-weight-bold font-size-70 mb-1">403 —</div>
    <div class="font-weight-bold mb-3">We are sorry...</div>
    <div>
      The page you're trying to access has restricted access
      <br> Please refer to your system administrator
    </div>
    <router-link to="/" class="btn btn-outline-primary width-100">
      Go Back
    </router-link>
  </div>
</template>
<script>
export default {
  name: 'EpmsError403',
}
</script>
