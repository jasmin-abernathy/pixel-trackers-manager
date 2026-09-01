# PTM public-release strategy

## Recommended model

Keep the current development repository private during pre-release work.

Before publication, do **not** simply switch the existing repository from private to public without auditing its complete Git history.

The development repository may contain internal roadmap documents, temporary test material or historical files that are not meant to become part of the public project. Deleting a file in the latest commit does not remove it from older Git history.

## Safer publication path

1. Reach a release-candidate build suitable for WordPress.org review.
2. Audit the complete private repository history for credentials, client data, internal-only documents and files that should not become public.
3. Decide whether to:
   - publish the existing repository only after a clean history audit/rewrite; or
   - preferably create a clean public upstream repository from the approved source tree while keeping the private development repository for internal planning.
4. Ensure the public tree contains the complete human-readable source required to build the distributed plugin.
5. Include `LICENSE`, `COPYRIGHT.md`, `TRADEMARKS.md`, contribution/security policies and the public documentation.
6. Run the exact release candidate through the project CI and WordPress Plugin Check.
7. Install and test the exact generated ZIP on a clean WordPress installation.
8. Use a numeric stable version accepted by WordPress.org.
9. Submit the release to WordPress.org.
10. Once a WordPress.org page exists, add its canonical URL to the public README and `TRADEMARKS.md`.

## Material to review before any public switch

At minimum inspect:

- every `.env`, config, archive, SQL dump or key ever committed;
- screenshots and test fixtures for personal/client information;
- `docs/CAHIER-DES-CHARGES.md` and other internal roadmap/status documents;
- test URLs, domains, e-mail addresses and API endpoints;
- old names and abandoned implementation notes;
- bundled third-party code and its license compatibility.

## Why keep a private development repository

A public source repository is useful for transparency, review, contribution and WordPress.org users. A separate private development space can still hold business planning, unreleased roadmap details, client-specific tests and operational notes that are not part of the open-source plugin itself.

This separation does not make the released PTM code proprietary: the public release remains GPL-2.0-or-later.
