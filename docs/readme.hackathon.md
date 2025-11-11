# Welcome Hackers from CloudFest 2025!

_some hastily written notes.  hacked together, you could say._

## 📋 Quick Start

### Step 0: Get on Slack

If you're not already there, head to chat.fair.pm, which will take you to the FAIR Slack workspace.
Next, join `#cloudfest-hackathon` and `#wg-aspirecloud` and say hi.

### Step 1: Check out AspireCloud:

Prerequisites:
* Docker
* GNU make 
  * macOS: the builtin version works fine
* `zstd` 
* `psql` PostgreSQL client
  * macOS: `brew install libpq` and add `/opt/homebrew/opt/libpq/bin` to your PATH.

```
git clone https://github.com/aspirepress/AspireCloud
cd AspireCloud
cp docker-compose.override.yml.dist docker-compose.override.yml # (edit it as desired)
make traefik-up # (optional)
make init
```

### Step 2: Load a database snapshot

Grab the aspirepress database snapshot from the 'files' tab in `#wg-aspirecloud` on Slack (I recommend the "mini" snapshot), decompress it, and run:
```
PGPASSWORD=password psql -U postgres -h localhost < aspirecloud_mini_20251029.sql
```

### Step 3: Start using AspireCloud

Some sample queries to get you started.  

```
base=https://api.aspiredev.local
# if not using Traefik:
# base=http://localhost:8099

curl "$base/plugins/info/1.2/?action=plugin_information&slug=hello-dolly&_fair=1"
curl "$base/packages/did:web:api.aspiredev.org:packages:wp-plugin:hello-dolly"

curl "$base/plugins/info/1.2/?action=query_plugins&browse=updated?_fair=1"
curl "$base/packages/did:plc:afjf7gsjzsqmgc7dlhb553mv"

```

