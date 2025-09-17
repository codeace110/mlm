# 🚀 MLM System

[![Laravel](https://img.shields.io/badge/Laravel-9.19-red.svg)](https://laravel.com)
[![PHP](https://img.shields.io/badge/PHP-8.0+-blue.svg)](https://php.net)
[![MySQL](https://img.shields.io/badge/MySQL-8.0-orange.svg)](https://mysql.com)
[![Tailwind CSS](https://img.shields.io/badge/Tailwind_CSS-3.1-38B2AC.svg)](https://tailwindcss.com)
[![License: MIT](https://img.shields.io/badge/License-MIT-yellow.svg)](https://opensource.org/licenses/MIT)

Advanced Multi-Level Marketing Platform Built with Laravel

A comprehensive MLM web application featuring user management, network visualization, earnings tracking, and administrative controls. The system supports hierarchical user structures, commission calculations, and real-time dashboard updates.

## 📊 Project Statistics

| Metric | Value |
|--------|-------|
| Sample Users | 43 |
| Product Packages | 10 |
| Bonus Rules | 8 |
| User Levels | 4 |

## ✨ Key Features

### 👥 User Management
- Complete user registration and authentication
- Role-based access control (Admin/User)
- Profile management with image uploads
- Hierarchical user structure with sponsor relationships

### 🌳 Network Visualization
- Interactive tree visualization of user networks
- Genealogy view showing downline structure
- Network statistics and performance metrics
- Multi-level hierarchy support (up to 4 levels)

### 💰 Earnings & Commissions
- Multiple bonus types and commission structures
- Real-time earnings tracking and history
- Automatic commission calculations
- Account balance management

### 📦 Product Management
- Product catalog with pricing
- Package management system
- Purchase functionality
- Inventory tracking

### 🏦 Withdrawal System
- Secure withdrawal requests
- Multiple payment method support
- Admin approval workflow
- Transaction history

### 📊 Admin Dashboard
- Comprehensive administrative interface
- User and network management
- Package and bonus rule configuration
- System monitoring and analytics

### 🎨 Modern UI/UX
- Responsive design with Bootstrap 5
- Real-time updates with AJAX
- Mobile-friendly interface
- Clean and intuitive user experience

## 🛠️ Installation Guide

### Prerequisites
- **PHP**: 8.0.2 or higher
- **Composer**: Latest version
- **Node.js**: 16.x or higher
- **NPM**: Latest version
- **MySQL**: 8.0 or higher

### Step-by-Step Installation

1. **Clone the Repository**
   ```bash
   git clone https://github.com/codeace110/mlm.git
   cd mlm
   ```

2. **Install PHP Dependencies**
   ```bash
   composer install
   ```

3. **Install JavaScript Dependencies**
   ```bash
   npm install
   ```

4. **Environment Configuration**
   ```bash
   cp .env.example .env
   php artisan key:generate
   ```
   Update your `.env` file with:
   - Database credentials
   - App URL and settings
   - Mail configuration (optional)

5. **Database Setup**
   ```bash
   php artisan migrate:fresh --seed
   ```
   This will create all tables and populate with sample data including:
   - 43 users with realistic profiles
   - 10 health & wellness product packages
   - 8 bonus rules for commissions
   - Sample earnings and withdrawal data

6. **Build Assets**
   ```bash
   npm run dev
   ```
   For production:
   ```bash
   npm run build
   ```

7. **Start the Application**
   ```bash
   php artisan serve
   ```
   Access at: `http://localhost:8000`

## 🗄️ Database Schema

### Core Tables
- **users**: User accounts with hierarchical structure
- **referrals**: User referral relationships
- **packages**: Product packages and pricing
- **bonus_rules**: Commission and bonus configurations
- **earnings**: User earnings and transactions
- **withdrawals**: Withdrawal requests and processing

### Sample Data
- 43 users with complete profiles and network relationships
- 10 premium health & wellness products
- 8 configurable bonus rules
- Hierarchical network structure (4 levels deep)

## 💻 Technology Stack

| Component | Technology | Version |
|-----------|------------|---------|
| **Backend** | Laravel | 9.19 |
| **Frontend** | Tailwind CSS | 3.1 |
| **JavaScript** | Alpine.js | 3.4 |
| **Database** | MySQL | 8.0 |
| **Build Tool** | Vite | 4.0 |
| **Authentication** | Laravel Sanctum | 3.0 |

## 🔌 API Endpoints

### User Dashboard APIs
- `GET /dashboard` - Main dashboard with statistics
- `GET /referrals` - User referral network
- `GET /earnings` - Earnings history with pagination
- `GET /withdrawals` - Withdrawal history
- `GET /packages` - Available product packages

### Admin APIs
- `GET /admin/dashboard` - Admin dashboard
- `GET /admin/users` - User management
- `GET /admin/packages` - Package management
- `GET /admin/bonus_rules` - Bonus rule management

## 📖 Usage Guide

### For Regular Users
1. Register using a referral link from your sponsor
2. Complete your profile and verify your account
3. Purchase product packages to activate your account
4. Share your unique referral link to build your network
5. Monitor earnings and request withdrawals
6. View your network tree and track downline performance

### For Administrators
1. Access admin panel at `/admin`
2. Manage users, packages, and bonus rules
3. Approve or deny withdrawal requests
4. Monitor system performance and statistics
5. Configure commission structures and settings

## 🤝 Contributing

We welcome contributions! Please follow these steps:

1. Fork the repository
2. Create a feature branch (`git checkout -b feature/amazing-feature`)
3. Commit your changes (`git commit -m 'Add amazing feature'`)
4. Push to the branch (`git push origin feature/amazing-feature`)
5. Open a Pull Request

## 📄 License

This project is licensed under the MIT License - see the [LICENSE](LICENSE) file for details.

## 🙏 Acknowledgments

- **Marjo** - Lead Developer & Project Manager
- **jayson** - bystander
- **aldren** - tester
- **adrian** - coffee maker

- **CodeAce110** - Repository Owner & Architecture

## 🎯 Getting Started

Ready to build your MLM network? Follow the installation guide above and start growing your business today!

**Repository**: [https://github.com/codeace110/mlm](https://github.com/codeace110/mlm)

---

*Built with ❤️ using Laravel & Tailwind CSS*