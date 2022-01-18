<template>
  <div>
    <div ref="pdfContainer" style="height: 100vh;">
      <div id="viewer"></div>
    </div>
  </div>
</template>
<script>
import { mapState } from 'vuex'

export default {
  data() {
    return {
      adobeApiReady: false,
      previewFilePromise: null,
    }
  },
  computed: {
    ...mapState({
      imagesPath: state => state.imagesPath,
      fileUrl: state => state.aapcr.fileUrl,
      documentName: state => state.aapcr.documentName,
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
    const { fileUrl, documentName } = this
    window.onbeforeunload = function() {
      localStorage.setItem('pdf.document.url', fileUrl)
      localStorage.setItem('pdf.document.name', documentName)
    }
  },
  methods: {
    // nextPage() {
    //   this.previewFilePromise.then(adobeViewer => {
    //     adobeViewer.getAPIs().then(apis => {
    //       apis.getCurrentPage()
    //         .then(currentPage => apis.gotoLocation(currentPage + 1))
    //         .catch(error => console.error(error))
    //     })
    //   })
    // },
    // previousPage() {
    //   this.previewFilePromise.then(adobeViewer => {
    //     adobeViewer.getAPIs().then(apis => {
    //       apis.getCurrentPage()
    //         .then(currentPage => {
    //           if (currentPage > 1) {
    //             return apis.gotoLocation(currentPage - 1)
    //           }
    //         })
    //         .catch(error => console.error(error))
    //     })
    //   })
    // },
    // zoomIn() {
    //   this.previewFilePromise.then(adobeViewer => {
    //     adobeViewer.getAPIs().then(apis => {
    //       apis.getZoomAPIs().zoomIn()
    //         .catch(error => console.error(error))
    //     })
    //   })
    // },
    // zoomOut() {
    //   this.previewFilePromise.then(adobeViewer => {
    //     adobeViewer.getAPIs().then(apis => {
    //       apis.getZoomAPIs().zoomOut()
    //         .catch(error => console.error(error))
    //     })
    //   })
    // },
    initializePdf() {
      if (!this.fileUrl) {
        this.$store.commit('aapcr/SET_STATE', {
          fileUrl: localStorage.getItem('pdf.document.url'),
          documentName: localStorage.getItem('pdf.document.name'),
        })
      }
      this.renderPdf()
    },
    renderPdf() {
      const { fileUrl, documentName } = this
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
            url: fileUrl,
          },
        },
        metaData: {
          fileName: documentName,
          id: documentName,
        },
      }, previewConfig)
      localStorage.removeItem('pdf.document.url')
      localStorage.removeItem('pdf.document.name')
    },
  },
}
</script>
<style scoped>
html, body {
  height: 100%;
  margin: 0;
}
</style>
