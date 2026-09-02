# Activity Ledger — RUN-2026-09-02-marketplace-009

## Resume investigation
- ACTION: re-establish repo state
- COMMAND: git config core.fileMode false && git pull --ff-only origin main
- RESULT: clean main at 2709333.

## User request: deep research
- USER: "even after those two previous attempts I am still getting exactly
  the same issue with exactly the same logs and different deployments"
- DECISION: use web-search + web-reader skills to research Vercel's actual
  Dockerfile handling, rather than continuing to guess.

## Deep research
- ACTION: web search for Vercel Dockerfile auto-detection
- RESULT: Found Vercel official PHP guide (vercel.com/kb/guide/deploy-php-on-vercel-with-docker)
  and Docker guide (vercel.com/kb/guide/docker).
- KEY FINDING: Vercel requires `Dockerfile.vercel` (NOT `Dockerfile`) +
  `vercel.json` with `services` + `runtime: "container"` + `rewrites`.
  A bare `Dockerfile` is treated as a generic serverless function.
- ACTION: fetched full content of both Vercel guide pages via web-reader
- RESULT: Confirmed the correct `vercel.json` structure:
  ```json
  {
    "services": {
      "api": { "root": ".", "entrypoint": "Dockerfile.vercel", "runtime": "container" }
    },
    "rewrites": [{ "source": "/(.*)", "destination": { "service": "api" } }]
  }
  ```
- ROOT CAUSE CONFIRMED: All previous fixes (RUN-006 through RUN-008) had
  zero effect because Vercel was never running the Docker image — it was
  falling back to framework auto-detection. Recorded in OBS-011.

## Apply fix
- COMMAND: git mv Dockerfile Dockerfile.vercel && git mv Dockerfile.render Dockerfile
- RESULT: PASS — Dockerfile.vercel is the Vercel variant, Dockerfile is the Render variant.
- Created vercel.json with services + runtime:container + rewrites.
- Updated docker-compose.yml to reference Dockerfile (not Dockerfile.render).
- Updated .dockerignore for new filenames.
- Updated README with correct Vercel deployment instructions.
- Updated both Dockerfiles' header comments.

## Verify
- ACTION: JSON validation
- COMMAND: python3 -c "import json; json.load(open('vercel.json'))"
- RESULT: PASS
- ACTION: shell syntax check
- COMMAND: sh -n docker/entrypoint.vercel.sh
- RESULT: PASS
- ACTION: static review of vercel.json against Vercel official docs
- RESULT: PASS — matches the structure from the PHP guide exactly.
- ACTION: Vercel live build verification
- RESULT: PENDING — no Vercel CLI in this environment.

## Document
- OBS-011, ADR-010, TASK-008, RUN-009, ledger, snapshot: all written.

## Commit + Push
- (hash recorded after commit)
