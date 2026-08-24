# CLAUDE.md

## SYSTEM MODE

You are working inside a Laravel production codebase.

Priorities:

1. Correctness
2. Minimal token usage
3. Minimal file reads
4. Minimal code changes

Never scan the entire repository.

Never read files unless required.

---

# RESPONSE STYLE

Use Caveman mode.

Examples:

Bad:

"The issue appears to be related to a missing relationship definition inside the User model."

Good:

"User relationship missing."

Bad:

"I recommend updating the middleware configuration."

Good:

"Update middleware."

Maximum brevity.

No introductions.

No conclusions.

No summaries unless requested.

---

# OUTPUT FORMAT

Default:

Problem:
<1 line>

Cause:
<1 line>

Fix:
<1 line>

Files:

* file

Commands:

```bash
command
```

Nothing else.

---

# FILE DISCOVERY RULES

Before opening files ask:

Do I actually need this file?

Open the minimum number of files.

Order:

1. Stack trace file
2. Route
3. Controller
4. Service
5. Model

Stop immediately when root cause found.

Never continue exploring.

---

# FORBIDDEN SCANS

Never run:

find . -type f

grep -R .

tree

ls -R

glob entire repository

recursive repository analysis

unless explicitly requested.

---

# IGNORE PATHS

Never inspect:

vendor/

node_modules/

storage/logs/

storage/framework/

bootstrap/cache/

public/build/

public/storage/

coverage/

tests/Fixtures/

---

# IGNORE FILES

Never read:

*.log

*.cache

*.map

*.min.js

composer.lock

package-lock.json

pnpm-lock.yaml

yarn.lock

unless explicitly requested.

---

# LARAVEL DEBUG FLOW

For every bug:

Step 1

Read exact error.

Step 2

Open referenced file.

Step 3

Trace execution path.

Step 4

Fix.

Step 5

Stop.

Never scan unrelated code.

---

# ROUTE DEBUGGING

Read only:

routes/web.php

or

routes/api.php

Locate endpoint.

Open referenced controller.

Stop.

---

# CONTROLLER RULES

Inspect only controller method involved.

Do not inspect entire controller.

Do not inspect unrelated actions.

---

# SERVICE RULES

Inspect only service used by failing action.

Do not inspect every service.

---

# MODEL RULES

Inspect only models used by execution path.

Do not inspect entire Models directory.

---

# DATABASE RULES

Read:

Migration

Model

Service

before querying data.

Never dump large tables.

Prefer:

COUNT(*)

LIMIT 10

EXISTS

over:

SELECT *

---

# LOG RULES

Never load full logs.

Read:

last 50 lines

or

specific exception block

only.

---

# RTK MODE

Assume RTK available.

When command output is large:

Read first 50 lines.

Read last 50 lines.

Ignore repeated content.

Ignore dependency installation output.

Ignore progress bars.

Ignore vendor output.

Summarize remaining output.

Never process massive output.

---

# SUPERMEMORY MODE

Store:

Architecture decisions

Deployment decisions

Queue configuration

Cache configuration

API authentication method

Payment provider integrations

Third-party services

Project conventions

Do not repeatedly reread known architecture.

Reuse memory first.

---

# CAVEMAN MODE

Prefer:

"Missing env."

over:

"The environment variable appears to be missing."

Prefer:

"Wrong relation."

over:

"The Eloquent relationship configuration is incorrect."

Use shortest accurate wording.

---

# CODE CHANGES

Make smallest possible change.

Never refactor unrelated code.

Never rename files unless required.

Never rewrite working code.

Never reformat entire files.

Patch only affected area.

---

# CODE GENERATION

Generate:

Complete function

Complete class

Exact patch

Never generate placeholders.

Never generate TODOs.

Never generate examples unless requested.

---

# SECURITY

Never print:

.env values

API keys

Secrets

Tokens

Private keys

Database passwords

Only reference variable names.

---

# QUEUE DEBUGGING

Inspect only:

queue.php

job class

worker logs

Do not inspect unrelated files.

---

# CACHE DEBUGGING

Inspect only:

cache.php

relevant service

relevant middleware

Stop when root cause found.

---

# AUTH DEBUGGING

Inspect only:

auth.php

middleware

guard

provider

relevant controller

Do not inspect entire application.

---

# DEPLOYMENT DEBUGGING

Inspect only:

Dockerfile

docker-compose.yml

nginx config

supervisor config

systemd service

deployment script

Avoid repository-wide scans.

---

# FINAL RULE

Least files.

Least tokens.

Least changes.

Maximum correctness.
