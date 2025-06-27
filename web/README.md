# Manufacturing Database System

A comprehensive manufacturing database system for managing production, inventory, quality control, and more.

## Features

- User Authentication and Authorization
- Product and Raw Materials Management
- Bill of Materials (BOM) Management
- Production Order Management
- Quality Control System
- Inventory Management
- Supplier Management
- Machine and Equipment Tracking
- Production Scheduling
- Maintenance Management
- Reporting and Analytics

## System Requirements

- PHP 7.4 or higher
- MySQL 5.7 or higher
- Web server (Apache/Nginx)
- Modern web browser

## Installation

1. Clone the repository:
```bash
git clone https://github.com/yourusername/manufacturing-db.git
cd manufacturing-db
```

2. Create a MySQL database and import the schema:
```bash
mysql -u root -p < database/schema.sql
```

3. Configure the database connection:
   - Open `config/database.php`
   - Update the database credentials:
     ```php
     private $host = "localhost";
     private $db_name = "manufacturing_db";
     private $username = "your_username";
     private $password = "your_password";
     ```

4. Configure the application settings:
   - Open `config/config.php`
   - Update the BASE_URL constant to match your server configuration:
     ```php
     define('BASE_URL', 'http://your-domain.com/manufacturing-db/');
     ```

5. Set up the web server:
   - For Apache, ensure mod_rewrite is enabled
   - Point the document root to the project directory
   - Ensure the web server has write permissions for the uploads directory

6. Create the uploads directory:
```bash
mkdir uploads
chmod 777 uploads
```

## Default Login

After installation, you can log in with the default admin account:
- Username: admin
- Password: admin123

**Important**: Change the default password immediately after first login.

## Directory Structure

```
manufacturing-db/
├── config/
│   ├── config.php
│   └── database.php
├── css/
│   └── style.css
├── js/
│   └── main.js
├── database/
│   └── schema.sql
├── uploads/
├── index.php
├── login.php
├── logout.php
└── README.md
```

## Security Features

- Password hashing using PHP's password_hash()
- Session security with httponly and secure flags
- Input sanitization
- SQL injection prevention using prepared statements
- XSS protection
- CSRF protection

## User Roles

1. Admin
   - Full system access
   - User management
   - System configuration

2. Manager
   - Production management
   - Inventory management
   - Report generation

3. Supervisor
   - Production oversight
   - Quality control
   - Team management

4. Operator
   - Production operations
   - Basic reporting
   - Machine operation

## API Documentation

The system provides RESTful APIs for integration with other systems:

### Authentication
- POST /api/auth/login
- POST /api/auth/logout

### Products
- GET /api/products
- POST /api/products
- GET /api/products/{id}
- PUT /api/products/{id}
- DELETE /api/products/{id}

### Production Orders
- GET /api/orders
- POST /api/orders
- GET /api/orders/{id}
- PUT /api/orders/{id}
- DELETE /api/orders/{id}

## Contributing

1. Fork the repository
2. Create your feature branch
3. Commit your changes
4. Push to the branch
5. Create a new Pull Request

## License

This project is licensed under the MIT License - see the LICENSE file for details.

## Support

For support, please email support@yourdomain.com or create an issue in the GitHub repository.

## Acknowledgments

- Bootstrap for the frontend framework
- jQuery for JavaScript functionality
- Font Awesome for icons
- Chart.js for data visualization 