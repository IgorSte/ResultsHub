# ResultsHub

**See your automation health at one page.**

ResultsHub is a self-hosted hub for test automation results. It collects automation
results from external sources and can also execute automation itself, storing the
results in one place with dashboards, trends and drill-down reports.

Currently supported:

- **Collecting results** from [Allure TestOps](https://qameta.io/) (via the Scout service)
- **Executing automation** locally and storing the results as Allure reports (via the Executor service)

Additional parsers (GitLab, Jenkins) are in progress.

## Architecture

ResultsHub consists of three parts:

| Component | Tech | Description |
|---|---|---|
| Web UI | PHP, Bootstrap, Chart.js, jQuery | Boards with runs, charts, machines and test sets management, embedded Allure reports |
| Background services | Python 3.14 (Poetry) | `scout.py` pulls results from external sources, `executor.py` runs automation on registered machines and processes results |
| Database | PostgreSQL | Two databases: `boards` (configuration: projects, boards, runs, machines, sets, executors) and `results` (results, tests, associations) |

```
External sources ──▶ scout.py ──┐
                                ├──▶ PostgreSQL ──▶ Web UI (boards, charts, reports)
machines ──▶ executor.py ───────┘        + static Allure reports in results/
```

### Repository layout

```
├── index.php, board.php, result.php, ...   # Web UI pages
├── rest.php                                # REST API (details, charts, run_start, logs, UI actions)
├── modules/                                # PHP/JS/CSS UI modules
├── static/                                 # Frontend libraries (Bootstrap, Chart.js, jQuery, ...)
├── background/
│   ├── scout.py                            # Collects results from external sources
│   ├── executor.py                         # Executes automation and processes results
│   ├── parsers/                            # Source parsers (allure_testops, allure_local, ...)
│   ├── config.py                           # DB connection settings
│   ├── credentials.json                    # Credentials for external systems
│   └── pyproject.toml                      # Python dependencies (Poetry)
├── boards.sql                              # Schema + demo data for the "boards" database
├── results.sql                             # Schema for the "results" database
└── results/                                # Stored Allure reports (one folder per result)
```

## Deployment

The application is served by an nginx + PHP + PostgreSQL stack from the companion
repository [docker-compose_nginx-php-postgres](https://github.com/IgorSte/docker-compose_nginx-php-postgres).

### 1. Install Docker (Ubuntu)

```bash
apt-get -y update
apt-get -y install ca-certificates curl
install -m 0755 -d /etc/apt/keyrings
curl -fsSL https://download.docker.com/linux/ubuntu/gpg -o /etc/apt/keyrings/docker.asc
chmod a+r /etc/apt/keyrings/docker.asc
echo "deb [arch=$(dpkg --print-architecture) signed-by=/etc/apt/keyrings/docker.asc] https://download.docker.com/linux/ubuntu $(. /etc/os-release && echo "jammy") stable" | sudo tee /etc/apt/sources.list.d/docker.list > /dev/null
apt-get -y update
apt-get -y install docker-ce docker-ce-cli containerd.io docker-buildx-plugin docker-compose-plugin
systemctl start docker
systemctl enable docker

apt install docker-compose
```

### 2. Clone the repositories

```bash
cd /opt/
git clone git@github.com:IgorSte/docker-compose_nginx-php-postgres.git
git clone git@github.com:IgorSte/ResultsHub.git
```

### 3. Start the stack

```bash
cd /opt/docker-compose_nginx-php-postgres/ && APP_PUBLIC_PATH=/opt/ResultsHub docker-compose up -d
```

### 4. Initialize the databases

```bash
apt install postgresql-client
cd /opt/ResultsHub

PGPASSWORD='dbpwd' psql -h localhost -U dbuser -d postgres -c "DROP DATABASE boards;"
PGPASSWORD='dbpwd' psql -h localhost -U dbuser -d postgres -c "CREATE DATABASE boards;"
PGPASSWORD='dbpwd' psql -h localhost -U dbuser -d boards -f boards.sql

PGPASSWORD='dbpwd' psql -h localhost -U dbuser -d postgres -c "DROP DATABASE results;"
PGPASSWORD='dbpwd' psql -h localhost -U dbuser -d postgres -c "CREATE DATABASE results;"
PGPASSWORD='dbpwd' psql -h localhost -U dbuser -d results -f results.sql
```

> **⚠️ Security note:** `dbuser` / `dbpwd` are the default database credentials
> shipped with the docker-compose stack, and they are also referenced in
> `modules/config.php` and `background/config.py`. **Change them before any
> real deployment** — update the password in the docker-compose configuration,
> both config files, and use your new password in the commands above.

## Background services

The background services live in `background/` and use
[Poetry](https://python-poetry.org/) for dependency management.

### Setup

```bash
cd /opt/ResultsHub/background
poetry install
```

### Credentials

Edit `background/credentials.json` and add credentials for each external system
referenced by the `parsers` table:

```json
{
    "allure_testops": {
        "login": "user",
        "password": "pass"
    }
}
```

### Running

Both services perform one iteration per launch, so schedule them with cron
(e.g. every minute).

To run manually:

```bash
cd /opt/ResultsHub/background
poetry run python3 scout.py --credentials credentials.json
poetry run python3 executor.py --credentials credentials.json
```

### Cron configuration

Add the following entries with `crontab -e` to run both services every minute:

```cron
* * * * * cd /opt/ResultsHub/background && /usr/local/bin/poetry run python3 scout.py --credentials credentials.json >> /opt/ResultsHub/log/cron.log 2>&1
* * * * * cd /opt/ResultsHub/background && /usr/local/bin/poetry run python3 executor.py --credentials credentials.json >> /opt/ResultsHub/log/cron.log 2>&1
```

> **Note:** cron runs with a minimal environment, so use the full path to the
> poetry binary — find it with `command -v poetry` in a regular shell
> (commonly `/usr/local/bin/poetry` or `~/.local/bin/poetry`).

Logs are written to `log/scout.log` and `log/executor.log`; cron's own stdout/stderr
goes to `log/cron.log`.

## Usage

- **Boards** — group runs into dashboards with sections and comments; track
  pass/fail trends and statistics per run; start the new runs.
- **Machines** — register machines that can execute automation.
- **Sets** — define test sets (command line, environment, artifacts) to be executed.

New runs can be also started from the UI or via the REST API (`/rest/run_start/<id>`),
which makes it easy to trigger executions from CI/CD pipelines.

## License

[Apache License 2.0](LICENSE)
