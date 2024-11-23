// import { defineConfig } from 'vite'
// import vue from '@vitejs/plugin-vue'
// import { quasar, transformAssetUrls } from '@quasar/vite-plugin'
// import { fileURLToPath, URL } from 'node:url'

// export default defineConfig({
//   plugins: [
//     vue({
//       template: { transformAssetUrls }
//     }),
//     quasar()
//   ],
//   resolve: {
//     alias: {
//       '@': fileURLToPath(new URL('./src', import.meta.url))
//     }
//   },
//   devServer: {
//     proxy: {
//       '/api': {
//         target: 'http://localhost/raj-express/backend/controller',
//         changeOrigin: true,
//         pathRewrite: {
//           '^/api': ''
//         }
//       }
//     }
//   }
// })

import { defineConfig } from 'vite'
import vue from '@vitejs/plugin-vue'

export default defineConfig({
  plugins: [vue()],
  server: {
    proxy: {
      '/api': {
        target: 'http://localhost/raj-express/backend/controller',
        changeOrigin: true,
        pathRewrite: {
          '^/api': ''
        }
      }
    }
  }
})
