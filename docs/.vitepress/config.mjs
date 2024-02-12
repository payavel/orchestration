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

    sidebar: [
      {
        text: 'Getting Started',
        collapsed: false,
        items: [
          {
            text: 'What is Orchestration?',
            link: '/guide'
          },
          {
            text: 'Installation',
            link: '/guide/installation'
          },
          {
            text: 'Configuration',
            link: '/guide/configuration'
          },
          {
              text: 'File Structure',
              link: '/guide/file-structure'
          }
        ]
      },
      {
        text: 'Services',
        collapsed: false,
        items: [
          {
            text: 'Overview',
            link: '/services'
          },
          {
            text: 'Status Management',
            link: '/services/status-management'
          },
          {
            text: 'Drivers',
            link: '/services/drivers'
          },
        ]
      },
      {
        text: 'Architecture',
        collapsed: false,
        items: [
          {
            text: 'Design Patterns',
            link: '/architecture'
          },
          {
            text: 'Facades',
            link: '/architecture/facades'
          }
        ]
      },
      {
        text: 'Testing',
        collapsed: false,
        items: [
          {
            text: 'Test Mode',
            link: '/testing'
          },
          {
            text: 'Recommendations',
            link: '/testing/recommendations'
          },
        ]
      }
    ],

    socialLinks: [
      { icon: 'github', link: 'https://github.com/payavel/orchestration' }
    ]
  }
})
