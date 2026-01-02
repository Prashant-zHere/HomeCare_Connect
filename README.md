# HomeCare Connect

**Connecting Care. Empowering Homes.**

HomeCare Connect is a comprehensive web-based platform that bridges the gap between homeowners and professional service providers. The system facilitates seamless booking and management of home care services including electrical work, plumbing, cleaning, and other household maintenance services.

## 🏠 Project Overview

HomeCare Connect is designed to simplify the process of finding, booking, and managing home care services. The platform serves three main user types:
- **Home Owners**: Can browse services, book appointments, and manage their service history
- **Service Providers**: Can register their services, manage bookings, and build their client base
- **Administrators**: Can oversee the platform, approve service providers, and manage the system

## ✨ Key Features

### For Home Owners
- **Service Discovery**: Browse available services by category (Electrician, Plumber, etc.)
- **Easy Booking**: Schedule services with preferred date and time
- **Booking Management**: Track booking status (pending, confirmed, cancelled, completed)
- **Service History**: View past bookings and service records
- **Profile Management**: Update personal information and preferences

### For Service Providers
- **Service Registration**: Register and showcase services with detailed descriptions
- **Booking Management**: Accept, reject, or reschedule service requests
- **Profile Customization**: Upload documents, photos, and service portfolios
- **Rate Management**: Set hourly or per-service pricing
- **Service Area Definition**: Specify coverage areas for services

### For Administrators
- **Provider Approval**: Review and approve/reject service provider applications
- **Platform Oversight**: Monitor bookings, users, and system activity
- **Quality Control**: Ensure service provider credentials and documentation

## 🛠️ Technology Stack

- **Backend**: PHP with MySQL database
- **Frontend**: HTML5, CSS3, JavaScript
- **Database**: MySQL (MariaDB) managed via phpMyAdmin
- **Database Management**: phpMyAdmin for database administration
- **Server**: Apache/Nginx compatible (XAMPP/WAMP recommended)
- **Styling**: Custom CSS with responsive design
- **Icons**: Font Awesome 6.4.0

## 📁 Project Structure

```
homecare_connect/
├── db/
│   └── homecare_connect.sql          # Database schema and sample data
├── include/
│   ├── conn/
│   │   ├── conn.php                 # Database connection
│   │   └── session.php              # Session management
│   ├── css/
│   │   ├── login.css                # Login page styles
│   │   ├── register.css             # Registration styles
│   │   └── user_dashboard.css       # Dashboard styles
│   └── js/
│       └── reload.js                # JavaScript utilities
├── user/
│   ├── admin/
│   │   └── index.php                # Admin dashboard
│   ├── home_owner/
│   │   ├── index.php                # Home owner dashboard
│   │   ├── bookings.php             # Booking management
│   │   ├── services.php             # Service browsing
│   │   ├── profile.php              # Profile management
│   │   └── register.php             # Home owner registration
│   ├── service_provider/
│   │   ├── index.php                # Service provider dashboard
│   │   ├── bookings.php             # Provider booking management
│   │   ├── profile.php              # Provider profile
│   │   ├── edit_profile.php         # Profile editing
│   │   └── register.php             # Provider registration
│   └── uploads/                     # File upload directory
└── index.php                       # Main login page
```

## 🗄️ Database Schema

The system uses four main database tables:

### `admin`
- Stores administrator credentials
- Fields: id, email, password

### `user` (Home Owners)
- Stores home owner information
- Fields: id, user_name, email, password, phone, city, address

### `service_provider`
- Stores service provider details and services
- Fields: id, user_name, email, password, description, file, photo, status, category, per, title, service_description, area, rate

### `bookings`
- Manages service bookings and appointments
- Fields: id, user_id, service_provider_id, date, time, status, notes

## 🚀 Installation & Setup

### Prerequisites
- PHP 7.4 or higher
- MySQL 5.7 or higher / MariaDB 10.4 or higher
- Apache web server (XAMPP/WAMP/LAMP recommended)
- phpMyAdmin for database management
- Web browser with JavaScript enabled

### Installation Steps

1. **Clone or Download the Project**
   ```bash
   git clone <repository-url>
   # or download and extract the ZIP file to your web server directory
   # (e.g., C:\xampp\htdocs\ for XAMPP or C:\wamp64\www\ for WAMP)
   ```

2. **Database Setup**
   - Open phpMyAdmin in your web browser (usually `http://localhost/phpmyadmin`)
   - Create a new database named `homecare_connect`
   - Import the database schema:
     - Click on the `homecare_connect` database
     - Go to the "Import" tab
     - Choose the file `db/homecare_connect.sql`
     - Click "Go" to import the schema and sample data

3. **Configure Database Connection**
   - Edit `include/conn/conn.php`
   - Update database credentials:
   ```php
   $conn = mysqli_connect("localhost", "your_username", "your_password", "homecare_connect");
   ```

4. **Web Server Setup**
   - Place the project files in your web server's document root
   - Ensure the `user/uploads/` directory has write permissions
   ```bash
   chmod 755 user/uploads/
   ```

5. **Access the Application**
   - Start your local server (XAMPP/WAMP)
   - Open your web browser
   - Navigate to `http://localhost/homecare_connect/` (adjust path as needed based on your setup)

## 👥 Default Login Credentials

### Administrator
- **Email/ID**: `admin`
- **Password**: `admin`

### Test Accounts
The database includes sample service provider and home owner accounts for testing purposes.

## 🔧 Configuration

### File Upload Settings
- Maximum file size and allowed file types can be configured in the upload handling scripts
- Default upload directory: `user/uploads/`
- Supported file types: PDF documents and image files (JPG, PNG)

### Service Categories
Current supported service categories:
- Electrician
- Plumber
- (Additional categories can be added through database modifications)

## 📱 Responsive Design

The application features a fully responsive design that works seamlessly across:
- Desktop computers
- Tablets
- Mobile devices

## 🔒 Security Features

- Session-based authentication
- SQL injection prevention (prepared statements recommended for production)
- File upload validation
- User role-based access control
- Password protection for all user accounts

## 🚧 Development Notes

### Recommended Improvements for Production
1. **Security Enhancements**
   - Implement prepared statements for all database queries
   - Add password hashing (bcrypt/Argon2)
   - Implement CSRF protection
   - Add input validation and sanitization

2. **Performance Optimizations**
   - Add database indexing
   - Implement caching mechanisms
   - Optimize image uploads and storage

3. **Feature Enhancements**
   - Email notifications for bookings
   - Payment integration
   - Rating and review system
   - Advanced search and filtering

## 📄 Documentation

Additional documentation is available in the `documentation_diagram/` folder:
- **ERD.png**: Entity Relationship Diagram showing database structure
- **DFD diagrams**: Data Flow Diagrams illustrating system processes
- **Data Dictionary**: Comprehensive field definitions

## 🤝 Contributing

1. Fork the repository
2. Create a feature branch
3. Make your changes
4. Test thoroughly
5. Submit a pull request


**HomeCare Connect** - Making home care services accessible, reliable, and convenient for everyone.