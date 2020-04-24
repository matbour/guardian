module.exports = {
  base: '/guardian/',
  title: 'Guardian',
  description: 'Highly configurable JSON Web Token implementation for Laravel and Lumen.',
  plugins: [
    'mermaidjs',
  ],
  themeConfig: {
    nav: [
      {
        text: 'Installation',
        link: '/#installation'
      },
      {
        text: 'Reference',
        items: [
          {
            text: 'What are JWTs?',
            link: '/reference/jwt',
          },
          {
            text: 'Configuration',
            link: '/reference/configuration',
          },
          {
            text: 'Performances',
            link: '/reference/performances',
          },
        ],
      },
      {
        text: 'Case studies',
        items: [
          {
            text: 'Authentication',
            link: '/case-studies/authentication',
          },
        ],
      },
      {
        text: 'Contributing',
        link: '/contributing',
      }
    ],
    sidebar: 'auto',
    repo: 'mathieu-bour/guardian',
    repoLabel: 'GitHub',
    docsRepo: 'mathieu-bour/guardian',
    docsDir: 'docs',
    docsBranch: 'master',
    editLinks: true,
    editLinkText: 'Edit this page',
    lastUpdated: 'Last updated',
    smoothScroll: true
  },
}
