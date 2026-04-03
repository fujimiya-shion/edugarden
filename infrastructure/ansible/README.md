# Ansible provisioning

This directory provisions the base server dependencies for EduGarden.

What it installs:

- `docker` role on `app_servers`
  - Docker Engine
  - Docker Compose plugin
- `mysql` role on `mysql_servers`
  - MySQL Server
  - reads `DB_ROOT_PASSWORD`, `DB_DATABASE`, `DB_USERNAME`, `DB_PASSWORD` from `secret_env_file`
- `redis` role on `redis_servers`
  - Redis Server
- `nginx` role on `lb_servers`
  - Nginx
  - Certbot
  - `/etc/nginx/conf.d/<domain>.conf`
  - `/etc/nginx/conf.d/<domain>.d/ssl.conf`

## Structure

- `inventory/hosts.ini.example`: inventory template
- `site.yml`: main playbook
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
   Set `secret_env_file` on each `mysql_servers` host if the secret path differs.
   Set `nginx_domain` on each `lb_servers` host.
   Set `public_env_file` on each `app_servers` host if the app env path differs.
   Set `public_env_file` and `secret_env_file` on each `lb_servers` host if your deploy paths differ.
   The nginx role uses `APP_PORT` resolved from each app server env file.
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
