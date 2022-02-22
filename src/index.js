import ColorPalette from './components/field/ColorPalette.vue'
import './assets/svg/icons.js'

window.panel.plugin('sylvainjule/color-palette', {
  fields: {
    'color-palette': ColorPalette
  }
})
