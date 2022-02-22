<template>
  <k-field v-bind="$props" class="k-color-palette-field">
    <template slot="options">
      <k-button
        v-if="extract === true"
        ref="extract"
        :id="_uid"
        icon="palette-pipette"
        @click="openSelector"
      >
        {{ $t('palette.new.palette') }}
      </k-button>
      <k-files-dialog ref="selector" @submit="processImage" />
    </template>

    <k-box v-if="emptyOptions" theme="info" class="color-palette_empty-options">
      {{ emptyOptionsPlaceholder }}
    </k-box>
    <k-empty
      v-else-if="emptyPalette"
      layout="custom"
      icon="image"
      :class="['color-palette_empty-palette', size]"
      @click="openSelector"
    >
      {{ $t('palette.empty.palette') }}
    </k-empty>
    <div v-else-if="loadingPalette" class="color-palette_empty-loading">
      <div class="loader-ctn" :class="size">
        <div class="loader"></div>
      </div>
    </div>
    <div v-else class="color-palette_input">
      <ul class="color-palette_input-list">
        <li
          v-for="(color, index) in colors"
          :key="index"
          :class="[size, { active: isValue(color) }, { unselect: unselect }]"
          @click="input(color, index)"
        >
          <div
            class="color-palette_input-color"
            :data-tooltip="toTooltip(color)"
            :style="inlineStyle(color)"
          ></div>
        </li>
      </ul>
    </div>
  </k-field>
</template>

<script>
export default {
  data() {
    return {
      loading: false,
      palette: []
    }
  },
  props: {
    options: [Object, Array],
    display: String,
    size: String,
    unselect: Boolean,
    default: [String, Boolean],
    extract: [String, Boolean],
    limit: Number,
    uri: String,
    endpoints: Object,
    autotemplate: String,
    template: String,

    // general options
    label: String,
    disabled: Boolean,
    help: String,
    parent: String,
    value: [String, Array],
    name: [String, Number],
    required: Boolean,
    type: String
  },
  computed: {
    selected() {
      return Array.isArray(this.value) ? this.value[0] : this.value
    },
    extracted() {
      let val = Array.isArray(this.value) ? this.value[1] : ''
      return this.palette.length ? this.palette : val
    },
    emptyOptions() {
      const options = this.isObject(this.options)
        ? Object.keys(this.options).length
        : this.options.length
      return !options
    },
    emptyOptionsPlaceholder() {
      return this.extract
        ? this.$t('palette.empty.template')
        : this.$t('palette.empty.options')
    },
    emptyPalette() {
      return this.extract === true && !this.extracted.length && !this.loading
    },
    loadingPalette() {
      return this.extract === true && this.loading
    },
    colors() {
      if (this.extract === true) return this.extracted.length ? this.extracted : false
      if (this.isQueryOptions(this.options)) {
        return this.options.map((el) => {
          return el['value']
        })
      } else return this.options
    }
  },
  async created() {
    if (!this.value && this.default) {
      if (
        Array.isArray(this.colors) &&
        this.colors.find((el) => el == this.default)
      ) {
        this.value = this.default
        this.input()
      } else if (
        this.isObject(this.colors) &&
        Object.keys(this.colors).find((el) => el == this.default)
      ) {
        this.value = this.colors[this.default]
        this.input()
      }
    }
  },
  methods: {
    isValue(color) {
      if (this.isObject(color)) {
        if (this.selected == color) return true
        else {
          if (this.isObject(this.selected)) {
            return this.isEquivalent(this.selected, color)
          } else {
            return false
          }
        }
      }
      return this.selected == color
    },
    inlineStyle(color) {
      // display: duo
      if (this.display == 'duo' && this.isObject(color)) {
        return (
          'background: linear-gradient(to right, ' +
          this.firstColor(color) +
          ' 50%, ' +
          this.secondColor(color) +
          ' 50%);'
        )
      }

      return 'background:' + this.firstColor(color)
    },
    toTooltip(color) {
      return this.isObject(color) && color['tooltip'] ? color['tooltip'] : false
    },
    firstColor(color) {
      if (this.isString(color)) return color
      else if (this.isObject(color)) return color[Object.keys(color)[0]]
    },
    secondColor(color) {
      if (this.isObject(color)) return color[Object.keys(color)[1]]
      return false
    },
    isString(color) {
      return typeof color === 'string'
    },
    isObject(color) {
      return color !== null && color !== undefined && typeof color === 'object'
    },
    isQueryOptions(options) {
      if (!options.length) return false
      let option = options[0]
      return (
        this.isObject(option) &&
        Object.keys(option).length == 2 &&
        Object.keys(option).includes('text') &&
        Object.keys(option).includes('value')
      )
    },
    isEquivalent(a, b) {
      let aKeys = Object.keys(a)
      let bKeys = Object.keys(b)

      // Make sure to compare without the keys
      let aKeyIndex = aKeys.indexOf('key')
      if (aKeyIndex !== -1) aKeys.splice(aKeyIndex, 1)
      let bKeyIndex = bKeys.indexOf('key')
      if (bKeyIndex !== -1) bKeys.splice(bKeyIndex, 1)

      // Different keys? not equivalent
      if (aKeys.length != bKeys.length) {
        return false
      }

      for (var i = 0; i < aKeys.length; i++) {
        let key = aKeys[i]
        // Different values? not equivalent
        if (a[key] !== b[key]) {
          return false
        }
      }
      return true
    },
    openSelector() {
      this.$refs.selector.open({
        max: false,
        multiple: false,
        endpoint: this.endpoints.field
      })
    },
    async processImage(file) {
      this.loading = true

      try {
        const res = await this.$api.get(`${this.endpoints.field}/extract`, {
          file: encodeURIComponent(Array.isArray(file) ? file[0].uuid : file)
        })

        this.palette = res.colors
        this.value = ['', this.palette]
        this.input()
        this.loading = false
      } catch (error) {
        this.palette = []
        this.loading = false
      }
    },
    input(color = false, index) {
      if (color) {
        if (this.unselect && this.isValue(color)) color = ''
        this.value = this.extract ? [color, this.extracted] : color
        if (this.isObject(this.value)) {
          this.value['key'] = index
        }
      }
      this.$emit('input', this.value)
    }
  }
}
</script>

<style lang="scss">
@import '../../assets/css/styles.scss';
</style>
