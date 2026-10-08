---
view: components.home.faq
badge: FAQ
title: "{package.name}"
highlight: Questions
subtitle: "Quick answers about installing and using {package.name}."
---

## What is {package.name}?

{card.description} It's a free, open-source {technology.name} package by Pharaonic.

## How do I install {package.name}?

Run `composer require {package.composer}` in your project's root directory.

## What does {package.name} require?

The latest release requires {package.requiresText}.

## Do I need the intl extension?

No. Without a locale, every formatter uses a built-in English formatter that gives the same output on every machine. You only need `ext-intl` when you pass a locale such as `de_DE` or `ar_EG`.

## Does it work with Laravel, Symfony or other frameworks?

Yes. The classes are plain static methods with no framework dependency, no configuration and no global state, so you can call them from any PHP code.

## Is 1 KB 1000 or 1024 bytes?

1000. `Bytes::format()` uses SI units (`KB`, `MB`) by default. Pass `binary: true` for IEC units (`KiB`, `MiB`), where 1 KiB is 1024 bytes. The two are never mixed.

## Is {package.name} free to use?

Yes. {package.name} is open source under the {package.license} license, so you can use it in personal and commercial projects.

## Where can I find the {package.name} documentation?

Read the [{package.name} documentation]({package.docsUrl}) for installation, usage and examples.

## How do I report a bug or contribute to {package.name}?

Open an issue or a pull request on [GitHub]({package.githubUrl}), or ask in the [Pharaonic Discord](https://discord.gg/XQG9RhvEvf).
