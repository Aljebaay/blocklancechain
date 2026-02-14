# Homepage Copy Rollout

SQL patch file: `scripts/sql/2026-02-14_homepage_copy_refresh.sql`

## 1) Local

Run the patch against your local database:

```powershell
mysql -h $env:DB_HOST -u $env:DB_USER -p$env:DB_PASS $env:DB_NAME < scripts/sql/2026-02-14_homepage_copy_refresh.sql
```

Then verify:
- Home hero heading/subheading text is updated.
- Cards and trust-box text is updated.
- Card links work with your local `APP_URL`.
- "View all featured services" appears when featured items are more than one.

## 2) Production

Take a DB backup, then run the same SQL file in production.

```powershell
mysql -h $env:DB_HOST -u $env:DB_USER -p$env:DB_PASS $env:DB_NAME < scripts/sql/2026-02-14_homepage_copy_refresh.sql
```

Post-deploy checks:
- Home page renders without PHP warnings.
- Copy updates are visible for the default language.
- Featured services link points to `featured_proposals`.
