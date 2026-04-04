# Ansible provisioning

This directory provisions the base server dependencies for EduGarden.

What it installs:

- `env_sync` role on `app_servers`, `mysql_servers`, `lb_servers`
  - copies local `env/.env.production` to each host's `public_env_file`
  - copies local `env/secret.env.production` to each host's `private_env_file`
- `docker` role on `app_servers`
  - Docker Engine
  - Docker Compose plugin
- `mysql` role on `mysql_servers`
  - MySQL Server
  - reads `DB_ROOT_PASSWORD`, `DB_DATABASE`, `DB_USERNAME`, `DB_PASSWORD` from `private_env_file`
- `redis` role on `redis_servers`
  - Redis Server
- `nginx` role on `lb_servers`
  - Nginx
  - Certbot
  - `/etc/nginx/conf.d/<domain>.conf`
  - `/etc/nginx/conf.d/<domain>.d/ssl.conf`

## Structure

- `inventory/hosts.ini.example`: inventory template
- `env/.env.production.example`: public production env template
- `env/secret.env.production.example`: private production env template
- `site.yml`: main playbook
- `roles/env_sync`: copy local env files to remote servers
- `roles/docker`: install Docker and Compose plugin
- `roles/mysql`: install MySQL Server
- `roles/redis`: install Redis Server
- `roles/nginx`: install Nginx

## Usage

1. Copy the inventory template:

```bash
cd infrastructure/ansible
cp inventory/hosts.ini.example inventory/hosts.ini
```

2. Edit `inventory/hosts.ini` with real hosts and SSH users.
   Copy `env/.env.production.example` to `env/.env.production` and fill the public values locally.
   Copy `env/secret.env.production.example` to `env/secret.env.production` and fill the private values locally.
   Set `private_env_file` on each `mysql_servers` host if the remote secret path differs.
   Set `nginx_domain` on each `lb_servers` host.
   Set `public_env_file` on each `app_servers` host if the app env path differs.
   Set `public_env_file` and `private_env_file` on each `app_servers` or `lb_servers` host if your deploy paths differ.
   The nginx role uses `APP_PORT_FORWARD` resolved from each app server env file.
   SSL certs are expected to be created separately with `certbot`.

3. Run the playbook:

```bash
ansible-playbook site.yml
```

If you need a different SSH key:

```bash
ansible-playbook site.yml --private-key ~/.ssh/your-key.pem
```

These roles currently target Debian/Ubuntu servers.
