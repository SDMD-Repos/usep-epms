<template>
  <vue-pdf-app :pdf="name" theme="light" :file-name="fileName" :config="config" />
</template>

<script>

import { ref } from 'vue'
import VuePdfApp from 'vue-pdf-app'
import 'vue-pdf-app/dist/icons/main.css'

export default {
  name: 'PdfViewer',
  components: { VuePdfApp },
  props: {
    name: { type: String, default: '' },
    fileName: { type: String, default: '' },
  },
  setup() {
    const viewer = ref(null)
    const config = {
      toolbar: {
        toolbarViewerRight: {
          presentationMode: false,
          viewBookmark: false,
          openFile: false,
          secondaryToolbarToggle: false,
        },
      },
      secondaryToolbar: false,
    }

    return {
      viewer,
      config,
    }
  },
}
</script>

<style>
#webviewer {
  height: 800px;
}
</style>
