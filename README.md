# MoonBoard Web Scraper

A web scraper for the MoonBoard website.

## Requirements

- PHP >= 8.2.0
- Zip PHP Extension

## Installation

Clone this repository and install Composer packages:

```bash
git clone https://github.com/ngmy/moonboard-web-scraper.git

cd moonboard-web-scraper

composer install --no-dev
```

Also, set your MoonBoard username and password to the `MOONBOARD_USERNAME` and `MOONBOARD_PASSWORD` environment
variables.

## Usage

MoonBoard Web Scraper commands are invoked using the `bin/moonboard-web-scraper` script.

### Scrape benchmarks

Run the `scrape-benchmarks` command and wait for JSON files of benchmarks to appear:

```bash
bin/moonboard-web-scraper scrape-benchmarks
```

## Changelog

Please see the [changelog](CHANGELOG.md).

## License

MoonBoard Web Scraper is open-sourced software licensed under the [MIT license](https://opensource.org/licenses/MIT).
