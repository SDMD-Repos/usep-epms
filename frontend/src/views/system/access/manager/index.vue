<template>
  <div>
    <access-rights-manager />
  </div>
</template>

<script>
import { defineComponent } from "vue";
import AccessRightsManager from '@/components/SystemAdmin/AccessRights/Manager'

export default defineComponent({
  name: 'AccessRightsComponent',
  components: { AccessRightsManager },
})
</script>
