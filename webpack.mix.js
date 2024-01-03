const mix = require('laravel-mix')
const path = require('path')

const directory = path.basename(path.resolve(__dirname))
const source = `platform/plugins/${directory}`
const dist = `public/vendor/core/plugins/${directory}`

mix.sass(`${source}/resources/sass/social-login-zalo.scss`, `${dist}/css`)

if (mix.inProduction()) {
    mix.copy(`${dist}/css`, `${source}/public/css`)
}
