# PTM public release checklist

Use this checklist before making any PTM source repository public or submitting a stable build to WordPress.org.

## Repository and history

- [ ] Decide whether the current private repository history is safe to expose.
- [ ] Search the full Git history for credentials, API keys, database dumps and personal/client data.
- [ ] Review internal roadmap/status documents and move internal-only material to a private development repository if needed.
- [ ] Verify the public repository description, homepage and project links.
- [ ] Confirm the official upstream repository name and default branch.

## Licensing and identity

- [ ] `LICENSE` states GPL v2 or later.
- [ ] `COPYRIGHT.md` is present and current.
- [ ] `TRADEMARKS.md` / project-identity policy is present and does not add restrictions to the GPL.
- [ ] Plugin PHP header contains `License: GPL v2 or later` and the GPL URI.
- [ ] `readme.txt` contains matching license metadata.
- [ ] All bundled third-party code/assets have compatible licenses and attribution where required.

## Release quality

- [ ] Replace the development version with a numeric release version accepted by WordPress.org.
- [ ] Keep the PHP plugin header, runtime version constant and `Stable tag` identical.
- [ ] PHP syntax checks pass on supported PHP versions.
- [ ] JavaScript syntax checks pass.
- [ ] WordPress Plugin Check passes or every remaining finding is understood and documented.
- [ ] WordPress target-version matrix passes on the exact release candidate.
- [ ] Gutenberg test passes.
- [ ] Elementor test passes.
- [ ] Divi test passes.
- [ ] Activation and deactivation on a clean WordPress installation pass.
- [ ] Exact generated distribution ZIP installs successfully on a clean WordPress installation.

## Privacy and security

- [ ] No telemetry, remote call or external lookup exists without documentation and deliberate user action where required.
- [ ] Nonces/capabilities protect state-changing admin actions.
- [ ] No client data, production URL, credentials or private test fixture is included in the release ZIP.
- [ ] `SECURITY.md` and private vulnerability reporting path are ready for a public repository.

## WordPress.org submission

- [ ] `readme.txt` metadata is final.
- [ ] Description and screenshots match the real current plugin.
- [ ] Five-tag WordPress.org limit is respected.
- [ ] Release ZIP SHA-256 is recorded.
- [ ] WordPress.org submission uses the exact validated build.
- [ ] After approval, add the canonical WordPress.org URL to the README and project-identity policy.

## After publication

- [ ] Tag the public source release.
- [ ] Keep the public release source reproducible from the repository.
- [ ] Publish security fixes promptly on the supported stable line.
- [ ] Keep internal/client-specific planning outside the public repository.
