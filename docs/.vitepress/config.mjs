import { defineConfig } from 'vitepress'

// https://vitepress.dev/reference/site-config
export default defineConfig({
  lang: 'en-US',
  title: "Orchestration",
  description: "Service abstraction layer for Laravel.",
  cleanUrls: true,
  head: [
      ['link', { rel: 'icon', type: 'image/svg+xml', href: 'icon-color-no_background.svg' }]
  ],
  themeConfig: {
    // https://vitepress.dev/reference/default-theme-config
    logo: { light: 'logo-icon_color-text_black-no_background.svg', dark: 'logo-icon_color-text_white-no_background.svg', alt: 'Payavel' },
    // siteTitle: false,
    nav: [
      {
        text: 'Guide',
        link: '/guide',
        activeMatch: '/guide'
      }
    ],

    sidebar: {
      '/guide': [
        {
          text: 'Getting Started',
          collapsed: false,
          items: [
            {
              text: 'Introduction',
              link: '/guide'
            },
            {
              text: 'Quick Start',
              link: '/guide/quick-start'
            }
          ]
        }
      ]
    },

    socialLinks: [
      { icon: 'github', link: 'https://github.com/payavel/serviceable' }
    ]
  }
})
