---
name: Readable

action:
  label: View on Packagist
  href: "{package.packagistUrl}"

views: components.packages

breadcrumbs:
  - label: Home
    href: route:home
  - label: Packages
    href: route:packages.index
  - label: "{technology.name} Packages"
    href: "url:/packages/{technology.slug}"
  - label: "{package.name}"

card:
  topic: formatting
  icon: chart
  tags: readable human-readable number format compact percentage ordinal spell bytes file-size money currency duration initials intl
  description: Human-friendly formatting for plain PHP. Turn numbers, byte sizes, money and durations into readable text, with predictable output and optional Intl locales.

seo:
  title: "{package.fullName} - Human-Readable Numbers, Sizes, Money & Durations"
  description: "{package.name} is a framework-agnostic PHP package that formats numbers, file sizes, money and durations for humans: 1.2M, 1.5 KB, USD 1,234.50, 1 hour 1 minute. {package.downloadsShort}+ downloads, {package.license} licensed."
  keywords: php readable, human readable numbers php, php number format, compact number php, file size format php, bytes to kb php, php money format, php duration format, php ordinal, number to words php
  author: Pharaonic
  images:
    - "{package.cover}"
  openGraph:
    type: website
    siteName: Pharaonic
  twitter:
    card: summary_large_image

schema:
  "@type": SoftwareSourceCode
  name: "{package.name}"
  description: "{package.name} is a framework-agnostic PHP package that formats numbers, file sizes, money and durations into human-friendly text."
  image: "{package.cover}"
  codeRepository: "{package.githubUrl}"
  programmingLanguage: PHP
  runtimePlatform: "{technology.name}"
  version: "{package.version}"
  datePublished: "{package.publishedAt}"
  dateModified: "{package.updatedAt}"
  license: "https://opensource.org/licenses/{package.license}"
  isAccessibleForFree: true
  sameAs:
    - "{package.githubUrl}"
    - "{package.packagistUrl}"
  author:
    "@id": url:/#organization
  publisher:
    "@id": url:/#organization
  interactionStatistic:
    "@type": InteractionCounter
    interactionType: https://schema.org/DownloadAction
    userInteractionCount: "{package.downloads}"
---
