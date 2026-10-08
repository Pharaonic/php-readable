---
view: components.packages.package-hero
badges:
  - label: PHP Package
    color: blue
  - label: "{package.latestVersionLabel}"
    color: green
  - label: "{package.license} License"
    color: purple
  - label: "{package.downloadsShort}+ downloads"
    color: blue
eyebrow: "{package.name}"
title: Raw values,
highlight: readable output
buttons:
  - label: View Full Documentation
    href: "{card.docsUrl}"
    style: primary
    icon: arrow-right
  - label: View on GitHub
    href: "{package.githubUrl}"
    style: ghost
    external: true
install: "{card.install}"
labels:
  copy: Copy
  copied: Copied!
---

Turn `1250000` into `1.3M`, `1536` bytes into `1.5 KiB`, `3661` seconds into `1 hour 1 minute 1 second` and `1234.5` into `USD 1,234.50`, with one static call and the same output on every machine.
