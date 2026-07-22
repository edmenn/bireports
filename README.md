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

🔗 **Live Demo**: [https://your-demo-url.com](https://your-demo-url.com)

### Screenshots
![Main Interface](screenshots/main-interface.png)
*Main application interface showing PowerBI dashboards*

![Dashboard View](screenshots/dashboard.png)  
*User dashboard displaying key PowerBI reports*

## 🚀 Quick Start

Clone and run in 3 steps:

```bash
git clone https://github.com/edmenn/bireports.git
cd bireports
composer install
# Configure your database and PowerBI settings in .env
php -S localhost:8000 -t public
```

Open [http://localhost:8000](http://localhost:8000) to view it in your browser.

## 📦 Installation

### Prerequisites
- PHP 8.2+ (PHP 8.3 is used by the production image)
- Composer
- A web server (e.g., Apache, Nginx)
- PowerBI account with necessary permissions

### From Source

```bash
# Clone repository
git clone https://github.com/edmenn/bireports.git
cd bireports

# Install dependencies
composer install

# Configure your environment (.env file)
cp .env.example .env
# Edit .env with your database and PowerBI settings

# Set up your web server to point to the 'public' directory
# Example for Apache:
# <VirtualHost *:80>
#     DocumentRoot /path/to/bireports/public
#     <Directory /path/to/bireports/public>
#         AllowOverride All
#         Require all granted
#     </Directory>
# </VirtualHost>
```

## 💻 Usage

### Basic Usage

1.  **Configure your `.env` file** with your database credentials and PowerBI API keys.
2.  **Access the web application** through your web browser.
3.  **Navigate** through the available dashboards.
4.  **Interact** with the dashboards as you would in PowerBI.

### Advanced Examples

// More complex usage scenarios (e.g., embedding in other applications)

## ⚙️ Configuration

### Environment Variables

Create a `.env` file in the root directory (copy `.env.example`):

```env
# Database
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=bireports
DB_USERNAME=your_db_username
DB_PASSWORD=your_db_password

# PowerBI
POWERBI_CLIENT_ID=your_powerbi_client_id
POWERBI_CLIENT_SECRET=your_powerbi_client_secret
POWERBI_TENANT_ID=your_powerbi_tenant_id
POWERBI_WORKSPACE_ID=your_powerbi_workspace_id
```

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
# Fork and clone the repo
git clone https://github.com/yourusername/bireports.git

# Install dependencies
composer install

# Create a new branch
git checkout -b feature/your-feature-name

# Make your changes and test
# (Add testing instructions here)

# Commit and push
git commit -m "Description of changes"
git push origin feature/your-feature-name
```

### Code Style

-   Follow PSR-2 coding standards.
-   Use meaningful variable and function names.
-   Write clear and concise comments.

## Testing

// Include testing instructions and commands (e.g., using PHPUnit)

## Deployment

// Provide deployment instructions for different platforms (e.g., Apache, Nginx, Docker)

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

-   📧 **Email**: your.email@example.com
-   🐛 **Issues**: [GitHub Issues](https://github.com/edmenn/bireports/issues)
-   📖 **Documentation**: [Full Documentation](https://docs.your-site.com) (Create a documentation site)

## 🙏 Acknowledgments

-   📚 **Libraries used**:
    -   [PHP](https://www.php.net/) - Server-side scripting language
    -   [Composer](https://getcomposer.org/) - Dependency Management
    -   [PowerBI REST API](https://docs.microsoft.com/en-us/power-bi/developer/embedded/rest-api) - For PowerBI integration
-   👥 **Contributors**: Thanks to all [contributors](https://github.com/edmenn/bireports/contributors)
```
