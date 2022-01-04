<template>
  <div>
    <div class="viewer__loading" v-if="loading">
      <img class="loading_img" :src="`${imagesPath}USeP Logo.png`" alt="USeP Logo" height="300">
    </div>
    <div ref="pdfContainer" style="height: 100vh;">
      <div id="viewer"></div>
    </div>
  </div>
</template>
<script>
import { fetchPdfData } from '@/services/mainForms/aapcr'
import { renderPdf } from '@/services/mainForms/opcrvp'
import { mapState } from 'vuex'
import NProgress from 'nprogress'

export default {
  props: {
    formId: {
      type: String,
      default: "",
    },
    id: {
      type: String,
      default: "",
    },
    documentName: {
      type: String,
      default: "",
    },
  },
  data() {
    return {
      adobeApiReady: false,
      previewFilePromise: null,
      loading: false,
    }
  },
  computed: {
    ...mapState({
      imagesPath: state => state.imagesPath,
    }),
  },
  mounted() {
    if(window.AdobeDC) {
      this.adobeApiReady = true
    } else {
      document.addEventListener('adobe_dc_view_sdk.ready', () => {
        this.adobeApiReady = true
      })
    }
    this.initializePdf()
  },
  methods: {
    nextPage() {
      this.previewFilePromise.then(adobeViewer => {
        adobeViewer.getAPIs().then(apis => {
          apis.getCurrentPage()
            .then(currentPage => apis.gotoLocation(currentPage + 1))
            .catch(error => console.error(error))
        })
      })
    },
    previousPage() {
      this.previewFilePromise.then(adobeViewer => {
        adobeViewer.getAPIs().then(apis => {
          apis.getCurrentPage()
            .then(currentPage => {
              if (currentPage > 1) {
                return apis.gotoLocation(currentPage - 1)
              }
            })
            .catch(error => console.error(error))
        })
      })
    },
    zoomIn() {
      this.previewFilePromise.then(adobeViewer => {
        adobeViewer.getAPIs().then(apis => {
          apis.getZoomAPIs().zoomIn()
            .catch(error => console.error(error))
        })
      })
    },
    zoomOut() {
      this.previewFilePromise.then(adobeViewer => {
        adobeViewer.getAPIs().then(apis => {
          apis.getZoomAPIs().zoomOut()
            .catch(error => console.error(error))
        })
      })
    },
    initializePdf() {
      const { id, documentName, formId } = this
      if (!this.adobeApiReady) {
        return
      }
      this.loading = true
      NProgress.start()
      /*this.$refs.pdfContainer.innerHTML = ""
      let viewer = document.createElement("div")
      viewer.id = "viewer"
      this.$refs.pdfContainer.appendChild(viewer)*/
      let renderer;
      if(formId === 'aapcr') {
        renderer = fetchPdfData(id, documentName)
      } else if (formId === 'opcrvp') {
        renderer = renderPdf(id)
      }
      console.log(renderer)
      renderer.then(response => {
        if (response) {
          const blob = new Blob([response], { type: 'application/pdf' })
          const fileUrl = window.URL.createObjectURL(blob)
          this.renderPdf(fileUrl, documentName)
        }
        this.loading = false
        NProgress.done()
      })
    },
    renderPdf(url, fileName) {
      const previewConfig = {
        defaultViewMode: 'FIT_WIDTH',
        showAnnotationTools: false,
        dockPageControls: false,
      }
      let adobeDCView = new AdobeDC.View({
        clientId: process.env.VUE_APP_ADOBE_KEY,
        divId: "viewer",
      })
      this.previewFilePromise = adobeDCView.previewFile({
        content: {
          location: {
            url: url,
          },
        },
        metaData: {
          fileName: fileName,
          id: fileName,
        },
      }, previewConfig)
    },
  },
}
</script>
<style scoped>
html, body {
  height: 100%;
  margin: 0;
}

.viewer__loading {
  position: fixed;
  width: 50px;
  height: 50px;
  top: 30%;
  left: 42%;
  margin: -25px 0 0 -25px
}
</style>
