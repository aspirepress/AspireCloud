[//]: # (@formatter:off)
# AspireCloud

This project is designed to function as a CDN/API endpoint system for distributing WordPress assets (themes, plugins, core) to users of the [AspirePress Updater](https://github.com/aspirepress/updater-plugin).

# 🪧 CloudFest Hackathon 2025: see [/docs/readme.hackathon.md](./docs/readme.hackathon.md)

## Setup

### Quick Start

```
make init
```

Next configure WordPress to use your local version of AspireCloud, and you're good to go!

## Using https://api.aspiredev.org instead of localhost

The local dev instance can be reached this way by enabling a [Traefik](https://hub.docker.com/_/traefik) proxy server:

    make traefik-up

You will then be able to reach the instance at https://api.aspiredev.org

## CVE Labeller Integration

AspireCloud includes automated vulnerability scanning for FAIR packages using the CVE Labeller API.

### Features

- **Automated Scanning**: Checks all latest package releases for vulnerabilities
- **Dynamic Frequency**: Adjusts check frequency based on vulnerability severity
    - HIGH severity detected → Scans every 10 minutes
    - MEDIUM severity detected → Scans every 30 minutes
    - LOW severity detected → Scans every hour
    - No vulnerabilities → Scans every 2 hours
- **Efficient**: Only checks the latest release of each package
- **Production Ready**: Includes retry logic, error handling, and detailed logging

### Setup

1. **Run the migration**:
   ```bash
   php artisan migrate
   ```

2. **Configure the API URL** in `.env`:
   ```env
   CVE_LABELLER_API_URL=http://api.cve-labeller.local/api/query
   ```

3. **Test the command**:
   ```bash
   php artisan cve:query -v
   ```

4. **Configure cron** for production:
   ```bash
   * * * * * cd /path-to-project && php artisan schedule:run >> /dev/null 2>&1
   ```

### Documentation

Complete documentation is available in the `/docs/cve-labeller/` directory:

- **START_HERE.md** - Quick installation guide
- **QUICK_REFERENCE.md** - One-page cheat sheet
- **FINAL_GUIDE.md** - Complete documentation
- **FILE_INDEX.md** - Index of all implementation files

### Configuration

All CVE settings are configurable via environment variables. See `.env.example` for the complete list of available options.

Key settings:
```env
CVE_LABELLER_API_URL=http://api.cve-labeller.local/api/query
CVE_LABELLER_ENABLED=true
CVE_LABELLER_BATCH_SIZE=50
CVE_LOG_API_REQUESTS=false
```

## Notes

AspireCloud operates as an API and a pseudo pull-through cache against WordPress.org. This means that if AspireCloud provides the requested endpoint, it attempts to deliver the resource; otherwise, it passes the request through to WordPress.org and returns their response to the end user.

The long-term goal is to gradually implement WordPress.org APIs to reduce reliance on their website and endpoints.

**Important**: Please do not use this project to flood or harass the WordPress.org website. We don't want to get banned from using their resources!

## License

This project is licensed under the [MIT License](https://opensource.org/license/mit). You may exercise all rights granted by the MIT license, including using this project for commercial purposes.
