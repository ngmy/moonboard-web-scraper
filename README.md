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

### Scrape the logbook

Run the `scrape-logbook` command and wait for JSON files of the logbook to appear:

```bash
bin/moonboard-web-scraper scrape-logbook
```

### Scrape user profiles

Run the `scrape-user-profiles` command and wait for JSON files of user profiles to appear:

```bash
bin/moonboard-web-scraper scrape-user-profiles
```

You can also specify the user IDs to scrape by passing the `--user-ids-file` option:

```bash
bin/moonboard-web-scraper scrape-user-profiles --user-ids-file=user_ids.txt
```

## Changelog

Please see the [changelog](CHANGELOG.md).

## License

MoonBoard Web Scraper is open-sourced software licensed under the [MIT license](https://opensource.org/licenses/MIT).
