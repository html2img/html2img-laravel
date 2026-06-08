# Changelog

All notable changes to this package are documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [1.0.1] - 2026-06-08

### Changed

- Require `html2img/html2img-php` `^1.0.1`.

## [1.0.0] - 2026-06-07

### Added

- Initial release.
- `Html2imgServiceProvider` with package auto-discovery and a publishable
  `config/html2img.php`.
- `Html2img` facade covering `html()`, `screenshot()` and `template()`, plus
  `download()` and `store()` helpers for saving a render to any filesystem disk.
- Container bindings for the underlying `Html2img\Html2imgClient` SDK and the
  `Html2img\Laravel\Html2img` manager.
- `html2img:test` artisan command to verify configuration with a small render.
- Configuration via the `HTML2IMG_API_KEY`, `HTML2IMG_BASE_URI`,
  `HTML2IMG_TIMEOUT` and `HTML2IMG_DISK` environment variables.
- Pest and Orchestra Testbench suite, Larastan and Laravel Pint configuration.

[1.0.1]: https://github.com/html2img/html2img-laravel/releases/tag/v1.0.1
[1.0.0]: https://github.com/html2img/html2img-laravel/releases/tag/v1.0.0
