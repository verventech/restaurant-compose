# 🍽️ Gourmet Bistro — LAPP Stack Restaurant Web App

[![Docker Compose](https://img.shields.io/badge/Docker%20Compose-v2%2B-blue?logo=docker&logoColor=white)](https://docs.docker.com/compose/)
[![PHP](https://img.shields.io/badge/PHP-8.2--Apache-777BB4?logo=php&logoColor=white)](https://www.php.net/)
[![PostgreSQL](https://img.shields.io/badge/PostgreSQL-15--Alpine-4169E1?logo=postgresql&logoColor=white)](https://www.postgresql.org/)
[![License](https://img.shields.io/badge/License-MIT-green.svg)](LICENSE)

A fully containerized **LAPP (Linux, Apache, PostgreSQL, PHP)** stack web application that dynamically renders a modern restaurant menu. Designed with production-ready Docker Compose practices, robust database health checks, automated schema initialization, and persistent storage volumes.

---

## 🏛️ System Architecture

The application runs in a isolated, two-tier architecture connected via a custom bridge network (`restaurant-network`).

                          ┌─────────────────────────────────────────┐
                          │           HOST OS / BROWSER             │
                          └────────────────────┬────────────────────┘
                                               │
                                        Port 8080:80
                                               │
┌──────────────────────────────────────────────────▼──────────────────────────────────────────────────┐│ DOCKER ENGINE (restaurant-network)                                                                  ││                                                                                                     ││   ┌────────────────────────────────────────┐            ┌────────────────────────────────────────┐  ││   │ SERVICE: restaurant-web                │            │ SERVICE: restaurant-db                 │  ││   │                                        │            │                                        │  ││   │  • Apache 2.4 + PHP 8.2                │            │  • PostgreSQL 15 (Alpine)              │  ││   │  • PDO PostgreSQL Extension            │   TCP 5432 │  • Auto-executes init.sql              │  ││   │  • Serves index.php UI                 ├───────────►│  • Persistent Data via Named Volume    │  ││   │  • Environment Variables:              │            │  • Internal Only (No host port bound)  │  ││   │    PGHOST, PGDATABASE, PGUSER, etc.    │            │                                        │  ││   └────────────────────────────────────────┘            └────────────────────────────────────────┘  ││                                                                      ▲                              ││                                                                      │ Mounts                       ││                                                         ┌────────────┴───────────┐                  ││                                                         │ Named Volume: db-data  │                  ││                                                         └────────────────────────┘                  │└─────────────────────────────────────────────────────────────────────────────────────────────────────┘
1. **Client Request:** Browser navigates to `http://localhost:8080`.
2. **Web Container:** Apache executes `index.php`, which reads database credentials from environment variables.
3. **Database Container:** PostgreSQL processes PDO queries over the internal bridge network (`restaurant-network`) on TCP port `5432`.
4. **Data Persistence:** Persistent database state is safely retained in the `db-data` Docker volume.

---

## 📂 Repository Directory Structure

```text
restaurant-docker/
├── .env                  # Active runtime environment secrets (git-ignored)
├── .env.example          # Environment variable template for repository cloning
├── .gitignore            # Git exclusion rules for sensitive & temporary files
├── Dockerfile            # Container build instructions for PHP 8.2 + Apache
├── docker-compose.yml    # Multi-container service specification
├── init.sql              # Auto-seeds database schema and menu items
├── README.md             # Project documentation
└── src/
    └── index.php         # Main web menu frontend interface
🗄️ Database Schema DesignThe relational model stores menu items with categories, descriptions, prices, and availability flags.categories (1) ───< (N) menu_items
1. categories TableColumnTypeConstraintsDescriptionidSERIALPRIMARY KEYCategory auto-increment IDnameVARCHAR(100)NOT NULL, UNIQUECategory title (e.g., Starters, Mains)display_orderINTDEFAULT 0Order index for UI rendering2. menu_items TableColumnTypeConstraintsDescriptionidSERIALPRIMARY KEYMenu item auto-increment IDcategory_idINTFOREIGN KEY (categories.id)FK linking item to its categorynameVARCHAR(150)NOT NULLMenu item namedescriptionTEXTNULLDish ingredients & detailspriceNUMERIC(10, 2)NOT NULLPrice formatted to 2 decimalsis_availableBOOLEANDEFAULT TRUEVisibility toggle flagcreated_atTIMESTAMPDEFAULT CURRENT_TIMESTAMPRecord creation timestamp🚀 Quick Start GuidePrerequisitesDocker Desktop (macOS / Windows) or Docker Engine + Docker Compose V2 (Linux).Git installed on your host system.Step 1: Clone the RepositoryBashgit clone [https://github.com/your-username/restaurant-docker.git](https://github.com/your-username/restaurant-docker.git)
cd restaurant-docker
Step 2: Configure Environment VariablesCopy .env.example to create your active .env configuration file:Bashcp .env.example .env
(Optional) Inspect or modify runtime credentials in .env:Code snippetHOST_PORT=8080
PGHOST=restaurant-db
PGDATABASE=restaurant_db
PGUSER=chef_admin
PGPASSWORD=kitchen_secure_pass_99
PGPORT=5432
Step 3: Build & Launch the ApplicationSpin up the entire stack in detached background mode:Bashdocker compose up -d --build
Step 4: Verify DeploymentCheck that both containers are active and the database health check passes:Bashdocker compose ps
Expected Output:PlaintextNAME             IMAGE                  COMMAND                  SERVICE          CREATED          STATUS                    PORTS
restaurant-db    postgres:15-alpine     "docker-entrypoint.s…"   restaurant-db    10 seconds ago   Up 9 seconds (healthy)    5432/tcp
restaurant-web   restaurant-docker-web  "docker-php-entrypoi…"   restaurant-web   10 seconds ago   Up 9 seconds              0.0.0.0:8080->80/tcp
Open your browser and navigate to:Plaintexthttp://localhost:8080
🛠️ Operational Commands ReferenceActionCommandView Live Container Logsdocker compose logs -fView Service Web Logs Onlydocker compose logs -f restaurant-webStop All Servicesdocker compose stopStart All Servicesdocker compose startRebuild Stack after Code Editsdocker compose up -d --buildTear Down Stack (Keep Data)docker compose downTear Down Stack + Delete Volumesdocker compose down -v🧪 Interactive Database Management (CRUD)To manually query or update menu items inside the running PostgreSQL container, launch an interactive psql session:Bashdocker compose exec -it restaurant-db psql -U chef_admin -d restaurant_db
Useful SQL Queries:View All Active Menu Items:SQLSELECT c.name AS category, m.name AS dish, m.price 
FROM menu_items m 
JOIN categories c ON m.category_id = c.id;
Insert a New Menu Item:SQLINSERT INTO menu_items (category_id, name, description, price) 
VALUES (2, 'Chef Special Pasta', 'Handmade fettuccine with garlic spinach.', 18.50);
Exit psql:SQL\q
Refresh your browser at http://localhost:8080 to see database updates reflected in real time.⚙️ Resilience & Health VerificationThis stack enforces auto-recovery policies:Dependency Synchronization: restaurant-web waits for restaurant-db to pass its SQL healthcheck routine before initializing Apache.Auto-Restart: Both containers are configured with restart: unless-stopped. If a container process crashes or encounters an out-of-memory event, Docker automatically restarts it.Testing Auto-Restart Behavior:Simulate an application crash by killing the primary Apache process inside restaurant-web:Bashdocker exec -it restaurant-web kill -9 1
Run docker compose ps to observe Docker instantly restarting the service while maintaining complete stack availability.🔒 Security Best Practices ImplementedNon-Root Database Exposure: The PostgreSQL container exposes port 5432 only inside the internal Docker bridge network (restaurant-network). Port 5432 is not bound to the host network interface.Secrets Isolation: .env is listed in .gitignore to prevent sensitive credentials from reaching source repositories.Prepared PDO Statements: SQL queries utilize PHP PDO parameter bindings to eliminate SQL injection risks.📜 LicenseThis project is open-source and available under the MIT License.