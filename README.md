```markdown
# 📊 BI Reports Web System 
A web system to view PowerBI dashboards from Fabric or Power Embedded. Empowering data-driven decisions with seamless PowerBI integration.

![License](https://img.shields.io/github/license/edmenn/bireports)
![GitHub stars](https://img.shields.io/github/stars/edmenn/bireports?style=social)
![GitHub forks](https://img.shields.io/github/forks/edmenn/bireports?style=social)
![GitHub issues](https://img.shields.io/github/issues/edmenn/bireports)
![GitHub pull requests](https://img.shields.io/github/issues-pr/edmenn/bireports)
![GitHub last commit](https://img.shields.io/github/last-commit/edmenn/bireports)

![PHP](https://img.shields.io/badge/PHP-777BB4?style=for-the-badge&logo=php&logoColor=white)
![HTML5](https://img.shields.io/badge/HTML5-E34F26?style=for-the-badge&logo=html5&logoColor=white)
![CSS3](https://img.shields.io/badge/CSS3-1572B6?style=for-the-badge&logo=css3&logoColor=white)
![JavaScript](https://img.shields.io/badge/JavaScript-F7DF1E?style=for-the-badge&logo=javascript&logoColor=black)

## 📋 Table of Contents

- [About](#about)
- [Features](#features)
- [Demo](#demo)
- [Quick Start](#quick-start)
- [Installation](#installation)
- [Usage](#usage)
- [Configuration](#configuration)
- [Fabric tenant setup guides](#fabric-tenant-setup-guides)
- [Project Structure](#project-structure)
- [Contributing](#contributing)
- [Testing](#testing)
- [Deployment](#deployment)
- [FAQ](#faq)
- [License](#license)
- [Support](#support)
- [Acknowledgments](#acknowledgments)

## About

The BI Reports Web System is a PHP-based application designed to facilitate the viewing of PowerBI dashboards, whether sourced from Microsoft Fabric or PowerBI Embedded. This system addresses the need for a centralized and easily accessible platform to consume PowerBI reports, eliminating the complexities of direct PowerBI access for end-users. It provides a streamlined interface for interacting with critical business intelligence data.

This project aims to bridge the gap between PowerBI's powerful reporting capabilities and the need for a simplified, web-based viewing experience. It's ideal for organizations that want to distribute PowerBI dashboards to a wider audience without requiring extensive PowerBI training or licensing for every user. The system is built using PHP, HTML, CSS, and JavaScript, providing a flexible and customizable foundation.

Key technologies include PHP for server-side logic, HTML/CSS for the user interface, and JavaScript for enhancing interactivity. The architecture focuses on retrieving and displaying PowerBI dashboards through the PowerBI REST API, ensuring data is always up-to-date and accurate. A unique selling point is its ease of integration with existing web infrastructure, allowing for a seamless addition to any organization's data ecosystem.

## ✨ Features

- 🎯 **Dashboard Integration**: Seamlessly integrates PowerBI dashboards from Fabric and Power Embedded.
- ⚡ **Performance**: Optimized for fast loading and efficient data retrieval.
- 🔒 **Security**: Secure authentication and authorization mechanisms to protect sensitive data.
- 🎨 **UI/UX**: User-friendly interface for easy navigation and interaction with dashboards.
- 📱 **Responsive**: Accessible on various devices, ensuring a consistent experience across platforms.
- 🛠️ **Extensible**: Designed for customization and integration with other systems.

## 🎬 Demo

There is no public demo configured in this repository. Run the local Docker
stack below to access the portal.

## 🚀 Quick Start

### Recommended: automated Docker installer

Use the repository installer. It creates `.env` when needed, generates the
application key, starts Laravel/PostgreSQL/queue/scheduler, installs PHP and
Node dependencies, runs migrations, seeds the local administrator, and builds
the frontend assets.

```bash
git clone https://github.com/edmenn/bireports.git
cd bireports
cp .env.example .env
./install.sh
```

For a non-interactive refresh after code or dependency changes:

```bash
./install.sh --refresh
```

Use `./install.sh --help` for the data-destructive `--fresh` option and the
faster synchronization mode. The installer builds assets by default; it does
not start a long-running Vite development process.

Open [http://localhost](http://localhost) to view it in your browser. The local
Compose mapping is `${APP_PORT:-80}:80`, so the default URL uses port 80, not
8000 or 8080. Set `APP_PORT` in `.env` if you intentionally change the host
port.

To stop the local environment:

```bash
docker compose down
```

### Local login

After running the seeder, use this development administrator:

```text
User: admin@birentable.com
Password: XH6cdURvPXk2uOhTm3K8
```

These credentials are for local development only. Change them before using the
application in production.

## 📦 Installation

### Prerequisites
- Docker Desktop with Docker Compose
- PowerBI/Fabric account with the required permissions for integration

The supported local installation path is Docker plus `install.sh`. A native
PHP/PostgreSQL installation is not maintained by the project scripts.

## 💻 Usage

### Basic Usage

1.  **Configure your `.env` file** with your Power BI/Fabric settings. For Docker,
    keep `DB_HOST=pgsql`; `127.0.0.1` refers to the host, not the database
    container.
2.  **Access the web application** through your web browser.
3.  **Navigate** through the available dashboards.
4.  **Interact** with the dashboards as you would in PowerBI.

### Advanced Examples

// More complex usage scenarios (e.g., embedding in other applications)

## ⚙️ Configuration

### Environment Variables

Create a `.env` file in the root directory (copy `.env.example`):

```env
# Database values for docker-compose.yml
DB_CONNECTION=pgsql
DB_HOST=pgsql
DB_PORT=5432
DB_DATABASE=birentabledb
DB_USERNAME=birentable
DB_PASSWORD=change-me

# PowerBI
POWERBI_CLIENT_ID=your_powerbi_client_id
POWERBI_CLIENT_SECRET=your_powerbi_client_secret
POWERBI_TENANT_ID=your_powerbi_tenant_id
POWERBI_WORKSPACE_ID=your_powerbi_workspace_id
```

## Fabric tenant setup guides

Before configuring the Power BI/Fabric values in `.env` or using the portal's
Power BI features, review the tenant and permissions guide that matches your
language:

- [Microsoft Fabric permissions and setup guide (English)](BI_Portal_Permisos_Fabric_EN.pdf)
- [Guía de permisos y configuración de Microsoft Fabric (Español)](BI_Portal_Permisos_Fabric_ES.pdf)

These guides explain the Entra ID application, Microsoft Graph consent, Fabric
tenant settings, workspace access, Azure RBAC for capacity operations, and the
minimum permissions required by each portal feature. They are configuration
guides, not substitutes for validating permissions in the target tenant.

## 📁 Project Structure

```
bireports/
├── 📁 app/                   # Application logic
│   ├── 📁 Controllers/      # Controllers for handling requests
│   ├── 📁 Models/           # Data models
│   ├── 📁 Providers/        # Service providers
├── 📁 config/                # Configuration files
├── 📁 database/              # Database migrations and seeds
├── 📁 public/                # Publicly accessible files (CSS, JS, images)
├── 📁 resources/             # Views (HTML templates)
│   ├── 📁 views/           # Blade templates
│   ├── 📁 sass/            # Sass files for styling
├── 📁 routes/                # Route definitions
├── 📁 storage/               # Storage for uploaded files and cache
├── 📁 tests/                 # PHPUnit tests
├── 📄 .env                   # Environment configuration
├── 📄 .env.example           # Example environment configuration
├── 📄 composer.json          # Composer dependencies
├── 📄 README.md              # Project documentation
└── 📄 LICENSE                # License file
```

## 🤝 Contributing

We welcome contributions! Please see our [Contributing Guide](CONTRIBUTING.md) for details. (Create a CONTRIBUTING.md file)

### Quick Contribution Steps

1.  🍴 Fork the repository
2.  🌟 Create your feature branch (`git checkout -b feature/AmazingFeature`)
3.  ✅ Commit your changes (`git commit -m 'Add some AmazingFeature'`)
4.  📤 Push to the branch (`git push origin feature/AmazingFeature`)
5.  🔃 Open a Pull Request

### Development Setup

```bash
# Clone the repository
git clone https://github.com/edmenn/bireports.git

# Create a new branch
git checkout -b feature/your-feature-name

# Synchronize the Docker development environment
./install.sh --sync

# Run tests inside the application container
docker compose exec birentable composer test

# Commit and push
git commit -m "Description of changes"
git push origin feature/your-feature-name
```

### Code Style

-   Follow PSR-2 coding standards.
-   Use meaningful variable and function names.
-   Write clear and concise comments.

## Testing

Run the Laravel test suite inside Docker with:

```bash
docker compose exec birentable composer test
```

## Deployment

For production, configure the real `.env` first, review `docker-compose.secure.yml`,
and use `./deploy.sh`. It builds the production image, populates the protected
code volume, migrates, and starts Caddy, Nginx, the app, scheduler, queue, and
database. The secure stack exposes HTTP/HTTPS through Caddy, not the local port
mapping. Do not use `--fresh` against a database containing real data.

## FAQ

// Address common questions and issues

## 📄 License

This project is licensed under the MIT License - see the [LICENSE](LICENSE) file for details.

### License Summary

-   ✅ Commercial use
-   ✅ Modification
-   ✅ Distribution
-   ✅ Private use
-   ❌ Liability
-   ❌ Warranty

## 💬 Support

-   🐛 **Issues**: [GitHub Issues](https://github.com/edmenn/bireports/issues)

## 🙏 Acknowledgments

-   📚 **Libraries used**:
    -   [PHP](https://www.php.net/) - Server-side scripting language
    -   [Composer](https://getcomposer.org/) - Dependency Management
    -   [PowerBI REST API](https://docs.microsoft.com/en-us/power-bi/developer/embedded/rest-api) - For PowerBI integration
-   👥 **Contributors**: Thanks to all [contributors](https://github.com/edmenn/bireports/contributors)
```
