# CI/CD Documentation

## Overview

This project uses GitHub Actions for Continuous Integration and Continuous Deployment.

## Workflows

### 1. Tests (`tests.yml`)

**Trigger:** Push or PR to `main`/`develop` branches

**Purpose:** Run automated tests on multiple PHP versions

**Steps:**
1. Checkout code
2. Setup PHP (8.2, 8.3)
3. Install dependencies
4. Run PHPUnit tests with coverage
5. Upload coverage to Codecov

**Status Badge:**
```markdown
![Tests](https://github.com/eagapov-dev/scandere-api/workflows/Tests/badge.svg)
```

---

### 2. Code Style (`code-style.yml`)

**Trigger:** Push or PR to `main`/`develop` branches

**Purpose:** Enforce code style standards using Laravel Pint

**Steps:**
1. Checkout code
2. Setup PHP 8.3
3. Install dependencies
4. Run Pint style checker

**Fix locally:**
```bash
./vendor/bin/pint
```

---

### 3. Deploy to Production (`deploy.yml`)

**Trigger:**
- Push to `main` branch (excluding `.md` and `.github` files)
- Manual trigger via `workflow_dispatch`

**Purpose:** Automatic deployment to production server

**Requirements:**
Set these secrets in GitHub repository settings:
- `SSH_PRIVATE_KEY` - Private SSH key for server access
- `SSH_HOST` - Server hostname/IP
- `SSH_USER` - SSH username (usually `root`)

**Deployment Steps:**
1. Pull latest code from `main`
2. Install composer dependencies
3. Run database migrations
4. Cache configurations
5. Set proper permissions
6. Restart PHP-FPM and Nginx

**Manual Deploy:**
Go to Actions → Deploy to Production → Run workflow

---

### 4. Security Checks (`security.yml`)

**Trigger:**
- Push or PR to `main`/`develop` branches
- Weekly schedule (Mondays)

**Purpose:** Check for security vulnerabilities

**Steps:**
1. Run `composer audit` for known vulnerabilities
2. Check for outdated dependencies
3. Report security issues

---

### 5. Database Backup (`backup.yml`)

**Trigger:**
- Daily at 2 AM UTC
- Manual trigger via `workflow_dispatch`

**Purpose:** Automated database backups

**Steps:**
1. SSH to server
2. Run backup script
3. Verify backup was created

**Manual Backup:**
Go to Actions → Database Backup → Run workflow

---

## Dependabot

Automatically creates PRs for dependency updates:
- **Composer packages:** Weekly
- **GitHub Actions:** Weekly

Configuration: `.github/dependabot.yml`

---

## Setting Up CI/CD

### 1. GitHub Secrets

Add these secrets in **Settings → Secrets and variables → Actions**:

```bash
# SSH_PRIVATE_KEY
# Generate on server:
ssh-keygen -t ed25519 -C "github-actions" -f ~/.ssh/github_actions
cat ~/.ssh/github_actions  # Copy this as SSH_PRIVATE_KEY

# Add public key to authorized_keys
cat ~/.ssh/github_actions.pub >> ~/.ssh/authorized_keys

# SSH_HOST
your-server-ip-or-domain

# SSH_USER
root
```

### 2. Server Setup

Ensure Git is configured on server:

```bash
ssh root@your-server
cd /var/www/scandere-api

# Initialize git if not done
git init
git remote add origin https://github.com/eagapov-dev/scandere-api.git
git fetch
git checkout main

# Set proper permissions
chown -R www-data:www-data .
chmod +x backup.sh
```

### 3. Test SSH Connection

```bash
# From your local machine
ssh -i ~/.ssh/github_actions root@your-server
```

---

## Workflow Status

Check workflow status at:
```
https://github.com/eagapov-dev/scandere-api/actions
```

### Status Badges

Add to README.md:

```markdown
![Tests](https://github.com/eagapov-dev/scandere-api/workflows/Tests/badge.svg)
![Code Style](https://github.com/eagapov-dev/scandere-api/workflows/Code%20Style/badge.svg)
![Security](https://github.com/eagapov-dev/scandere-api/workflows/Security%20Checks/badge.svg)
```

---

## Development Workflow

### Feature Development

1. Create feature branch:
```bash
git checkout -b feature/new-feature
```

2. Make changes and commit:
```bash
git add .
git commit -m "Add new feature"
```

3. Push and create PR:
```bash
git push origin feature/new-feature
```

4. CI automatically runs:
   - ✅ Tests
   - ✅ Code Style
   - ✅ Security Checks

5. After PR approval, merge to `main`
6. Auto-deployment to production

---

## Deployment Process

### Automatic Deployment

When code is pushed to `main`:

```
Push to main
    ↓
GitHub Actions triggered
    ↓
Tests run (must pass)
    ↓
Deploy workflow starts
    ↓
SSH to server
    ↓
Pull latest code
    ↓
Install dependencies
    ↓
Run migrations
    ↓
Cache configs
    ↓
Restart services
    ↓
✅ Deployment complete
```

### Rollback

If deployment fails or has issues:

```bash
# SSH to server
ssh root@your-server
cd /var/www/scandere-api

# View recent commits
git log --oneline -5

# Rollback to previous commit
git reset --hard <commit-hash>

# Re-run deployment steps
composer install --no-dev --optimize-autoloader
php artisan migrate
php artisan config:cache
systemctl restart php8.3-fpm nginx
```

---

## Environment Variables

Production environment variables are stored in `.env` on the server.

**Never commit `.env` to Git!**

To update production environment:

```bash
ssh root@your-server
cd /var/www/scandere-api
nano .env
# Make changes
php artisan config:cache
systemctl restart php8.3-fpm
```

---

## Monitoring

### Check Deployment Status

```bash
# View latest deployments
gh run list --workflow=deploy.yml

# View specific deployment
gh run view <run-id>
```

### Check Server Status

```bash
ssh root@your-server

# Check services
systemctl status php8.3-fpm
systemctl status nginx
systemctl status mariadb

# Check logs
tail -f /var/www/scandere-api/storage/logs/laravel.log
tail -f /var/log/nginx/error.log
```

### Check Backups

```bash
ssh root@your-server
ls -lh /root/backups/scandere/
```

---

## Troubleshooting

### Deployment Fails

1. Check GitHub Actions logs
2. SSH to server and check manually:
   ```bash
   cd /var/www/scandere-api
   git pull origin main
   composer install
   php artisan migrate --pretend
   ```

### Tests Fail in CI

1. Run tests locally:
   ```bash
   php artisan test
   ```
2. Check for environment differences
3. Review test logs in GitHub Actions

### SSH Connection Issues

1. Verify SSH key is correct in GitHub Secrets
2. Ensure public key is in `~/.ssh/authorized_keys` on server
3. Check firewall allows SSH (port 22)

### Code Style Fails

Run Pint locally to fix:
```bash
./vendor/bin/pint
git add .
git commit -m "Fix code style"
git push
```

---

## Performance

### Caching

GitHub Actions caches:
- Composer dependencies
- Speeds up workflow by ~50%

### Parallel Jobs

Tests run in parallel for PHP 8.2 and 8.3

---

## Security

### Secrets Management

- Never log secrets in workflows
- Use GitHub Encrypted Secrets
- Rotate SSH keys regularly

### Branch Protection

Enable in GitHub settings:
- Require PR reviews
- Require status checks (tests, code style)
- Require branches to be up to date

---

## Cost Optimization

### Free Tier Limits

- **Public repos:** Unlimited minutes
- **Private repos:** 2,000 minutes/month

### Optimization Tips

1. Cache dependencies
2. Run workflows only when needed (`paths-ignore`)
3. Use `workflow_dispatch` for manual triggers
4. Use scheduled jobs efficiently

---

## Future Improvements

- [ ] Add integration tests with staging environment
- [ ] Implement blue-green deployment
- [ ] Add Slack/Discord notifications
- [ ] Setup monitoring with Sentry
- [ ] Add performance benchmarks
- [ ] Implement canary deployments

---

For more information, see:
- [GitHub Actions Documentation](https://docs.github.com/actions)
- [Laravel Deployment Best Practices](https://laravel.com/docs/deployment)
