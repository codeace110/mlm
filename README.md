<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>MLM System - Laravel Multi-Level Marketing Platform</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            line-height: 1.6;
            color: #333;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
        }

        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 20px;
        }

        .header {
            text-align: center;
            color: white;
            padding: 60px 0;
            background: rgba(0, 0, 0, 0.1);
            border-radius: 15px;
            margin-bottom: 30px;
        }

        .header h1 {
            font-size: 3.5rem;
            margin-bottom: 10px;
            text-shadow: 2px 2px 4px rgba(0, 0, 0, 0.3);
        }

        .header p {
            font-size: 1.3rem;
            opacity: 0.9;
        }

        .badge {
            display: inline-block;
            background: #28a745;
            color: white;
            padding: 8px 16px;
            border-radius: 20px;
            font-size: 0.9rem;
            margin: 5px;
        }

        .section {
            background: white;
            margin: 30px 0;
            padding: 40px;
            border-radius: 15px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
        }

        .section h2 {
            color: #2c3e50;
            border-bottom: 3px solid #3498db;
            padding-bottom: 10px;
            margin-bottom: 30px;
            font-size: 2.2rem;
        }

        .section h3 {
            color: #34495e;
            margin: 30px 0 15px 0;
            font-size: 1.5rem;
        }

        .feature-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 20px;
            margin: 30px 0;
        }

        .feature-card {
            background: #f8f9fa;
            padding: 25px;
            border-radius: 10px;
            border-left: 5px solid #3498db;
            transition: transform 0.3s ease;
        }

        .feature-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
        }

        .feature-card h4 {
            color: #2c3e50;
            margin-bottom: 10px;
        }

        .code-block {
            background: #2c3e50;
            color: #ecf0f1;
            padding: 20px;
            border-radius: 8px;
            margin: 20px 0;
            overflow-x: auto;
            font-family: 'Courier New', monospace;
        }

        .step-list {
            counter-reset: step-counter;
        }

        .step-list li {
            counter-increment: step-counter;
            margin: 15px 0;
            padding: 15px;
            background: #f8f9fa;
            border-radius: 8px;
            border-left: 4px solid #3498db;
        }

        .step-list li::before {
            content: counter(step-counter);
            display: inline-block;
            width: 30px;
            height: 30px;
            background: #3498db;
            color: white;
            border-radius: 50%;
            text-align: center;
            line-height: 30px;
            margin-right: 15px;
            font-weight: bold;
        }

        .tech-stack {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 20px;
            margin: 30px 0;
        }

        .tech-item {
            text-align: center;
            padding: 20px;
            background: #f8f9fa;
            border-radius: 10px;
            border: 2px solid #e9ecef;
        }

        .tech-item h4 {
            color: #2c3e50;
            margin-bottom: 10px;
        }

        .contributors {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 20px;
            margin: 30px 0;
        }

        .contributor-card {
            background: #f8f9fa;
            padding: 25px;
            border-radius: 10px;
            text-align: center;
            border: 2px solid #e9ecef;
        }

        .contributor-avatar {
            width: 80px;
            height: 80px;
            background: #3498db;
            border-radius: 50%;
            margin: 0 auto 15px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 2rem;
            font-weight: bold;
        }

        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 20px;
            margin: 30px 0;
        }

        .stat-card {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 25px;
            border-radius: 10px;
            text-align: center;
        }

        .stat-number {
            font-size: 2.5rem;
            font-weight: bold;
            margin-bottom: 5px;
        }

        .highlight {
            background: #fff3cd;
            border: 1px solid #ffeaa7;
            border-radius: 8px;
            padding: 20px;
            margin: 20px 0;
        }

        .footer {
            text-align: center;
            color: white;
            padding: 40px 0;
            background: rgba(0, 0, 0, 0.2);
            border-radius: 15px;
            margin-top: 30px;
        }

        @media (max-width: 768px) {
            .header h1 {
                font-size: 2.5rem;
            }

            .section {
                padding: 20px;
            }

            .feature-grid {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <!-- Header Section -->
        <div class="header">
            <h1>🚀 MLM System</h1>
            <p>Advanced Multi-Level Marketing Platform Built with Laravel</p>
            <div>
                <span class="badge">Laravel 11</span>
                <span class="badge">PHP 8.2+</span>
                <span class="badge">MySQL</span>
                <span class="badge">Bootstrap 5</span>
            </div>
        </div>

        <!-- Overview Section -->
        <div class="section">
            <h2>📋 Project Overview</h2>
            <p>A comprehensive Multi-Level Marketing (MLM) web application built with Laravel, featuring user management, network visualization, earnings tracking, and administrative controls. The system supports hierarchical user structures, commission calculations, and real-time dashboard updates.</p>

            <div class="stats-grid">
                <div class="stat-card">
                    <div class="stat-number">43</div>
                    <div>Sample Users</div>
                </div>
                <div class="stat-card">
                    <div class="stat-number">10</div>
                    <div>Product Packages</div>
                </div>
                <div class="stat-card">
                    <div class="stat-number">8</div>
                    <div>Bonus Rules</div>
                </div>
                <div class="stat-card">
                    <div class="stat-number">4</div>
                    <div>User Levels</div>
                </div>
            </div>
        </div>

        <!-- Features Section -->
        <div class="section">
            <h2>✨ Key Features</h2>

            <div class="feature-grid">
                <div class="feature-card">
                    <h4>👥 User Management</h4>
                    <p>Complete user registration, authentication, and profile management with role-based access control.</p>
                </div>

                <div class="feature-card">
                    <h4>🌳 Network Visualization</h4>
                    <p>Interactive tree visualization showing user hierarchy, levels, and network relationships.</p>
                </div>

                <div class="feature-card">
                    <h4>💰 Earnings Tracking</h4>
                    <p>Comprehensive earnings management with multiple bonus types, history tracking, and real-time updates.</p>
                </div>

                <div class="feature-card">
                    <h4>📦 Product Management</h4>
                    <p>Product catalog with package management, pricing, and purchase functionality.</p>
                </div>

                <div class="feature-card">
                    <h4>🏦 Withdrawal System</h4>
                    <p>Secure withdrawal requests with multiple payment methods and approval workflow.</p>
                </div>

                <div class="feature-card">
                    <h4>📊 Admin Dashboard</h4>
                    <p>Comprehensive administrative interface for managing users, packages, and system settings.</p>
                </div>

                <div class="feature-card">
                    <h4>📱 Responsive Design</h4>
                    <p>Mobile-friendly interface built with Bootstrap 5 for optimal user experience.</p>
                </div>

                <div class="feature-card">
                    <h4>🔄 Real-time Updates</h4>
                    <p>AJAX-powered live updates for balances, earnings, and network statistics.</p>
                </div>
            </div>
        </div>

        <!-- Installation Section -->
        <div class="section">
            <h2>🛠️ Installation Guide</h2>

            <div class="highlight">
                <strong>Prerequisites:</strong> PHP 8.2+, Composer, Node.js, NPM, MySQL
            </div>

            <ol class="step-list">
                <li>
                    <strong>Clone the Repository</strong>
                    <div class="code-block">
git clone https://github.com/codeace110/mlm.git<br>
cd mlm
                    </div>
                </li>

                <li>
                    <strong>Install PHP Dependencies</strong>
                    <div class="code-block">
composer install
                    </div>
                </li>

                <li>
                    <strong>Install JavaScript Dependencies</strong>
                    <div class="code-block">
npm install
                    </div>
                </li>

                <li>
                    <strong>Environment Configuration</strong>
                    <div class="code-block">
copy .env.example .env<br>
php artisan key:generate
                    </div>
                    <p>Update your <code>.env</code> file with database credentials and app settings.</p>
                </li>

                <li>
                    <strong>Database Setup</strong>
                    <div class="code-block">
php artisan migrate:fresh --seed
                    </div>
                    <p>This will create all tables and populate them with sample data.</p>
                </li>

                <li>
                    <strong>Build Assets</strong>
                    <div class="code-block">
npm run dev
                    </div>
                    <p>Start Vite development server for asset compilation.</p>
                </li>

                <li>
                    <strong>Start the Application</strong>
                    <div class="code-block">
php artisan serve
                    </div>
                    <p>Access the application at <code>http://localhost:8000</code></p>
                </li>
            </ol>
        </div>

        <!-- Database Schema Section -->
        <div class="section">
            <h2>🗄️ Database Schema</h2>

            <h3>Core Tables</h3>
            <ul>
                <li><strong>users</strong> - User accounts with hierarchical structure</li>
                <li><strong>referrals</strong> - User referral relationships</li>
                <li><strong>packages</strong> - Product packages and pricing</li>
                <li><strong>bonus_rules</strong> - Commission and bonus configurations</li>
                <li><strong>earnings</strong> - User earnings and transactions</li>
                <li><strong>withdrawals</strong> - Withdrawal requests and processing</li>
            </ul>

            <h3>Sample Data</h3>
            <ul>
                <li>43 users with realistic names (John Smith, Sarah Johnson, etc.)</li>
                <li>10 product packages (vitamins, supplements, coffee)</li>
                <li>8 bonus rules for different commission types</li>
                <li>Hierarchical network structure (4 levels deep)</li>
            </ul>
        </div>

        <!-- Tech Stack Section -->
        <div class="section">
            <h2>💻 Technology Stack</h2>

            <div class="tech-stack">
                <div class="tech-item">
                    <h4>Laravel 11</h4>
                    <p>PHP Framework for robust backend development</p>
                </div>

                <div class="tech-item">
                    <h4>Blade Templates</h4>
                    <p>Server-side templating engine</p>
                </div>

                <div class="tech-item">
                    <h4>Bootstrap 5</h4>
                    <p>Responsive CSS framework</p>
                </div>

                <div class="tech-item">
                    <h4>MySQL</h4>
                    <p>Relational database management</p>
                </div>

                <div class="tech-item">
                    <h4>Vite</h4>
                    <p>Fast build tool for modern web development</p>
                </div>

                <div class="tech-item">
                    <h4>AJAX</h4>
                    <p>Asynchronous JavaScript for dynamic updates</p>
                </div>
            </div>
        </div>

        <!-- API Documentation Section -->
        <div class="section">
            <h2>🔌 API Endpoints</h2>

            <h3>User Dashboard APIs</h3>
            <ul>
                <li><code>GET /dashboard</code> - Main dashboard with statistics</li>
                <li><code>GET /referrals</code> - User referral network</li>
                <li><code>GET /earnings</code> - Earnings history with pagination</li>
                <li><code>GET /withdrawals</code> - Withdrawal history</li>
                <li><code>GET /packages</code> - Available product packages</li>
            </ul>

            <h3>Admin APIs</h3>
            <ul>
                <li><code>GET /admin/dashboard</code> - Admin dashboard</li>
                <li><code>GET /admin/users</code> - User management</li>
                <li><code>GET /admin/packages</code> - Package management</li>
                <li><code>GET /admin/bonus_rules</code> - Bonus rule management</li>
            </ul>
        </div>

        <!-- Contributors Section -->
        <div class="section">
            <h2>👥 Contributors</h2>

            <div class="contributors">
                <div class="contributor-card">
                    <div class="contributor-avatar">M</div>
                    <h4>Marjo</h4>
                    <p>Lead Developer & Project Manager</p>
                    <p>Specialized in Laravel development and MLM systems</p>
                </div>

                <div class="contributor-card">
                    <div class="contributor-avatar">A</div>
                    <h4>AI Assistant</h4>
                    <p>Code Generation & Documentation</p>
                    <p>Automated testing and deployment scripts</p>
                </div>

                <div class="contributor-card">
                    <div class="contributor-avatar">C</div>
                    <h4>CodeAce110</h4>
                    <p>Repository Owner</p>
                    <p>Project architecture and GitHub management</p>
                </div>
            </div>
        </div>

        <!-- Usage Guide Section -->
        <div class="section">
            <h2>📖 Usage Guide</h2>

            <h3>For Regular Users</h3>
            <ol>
                <li>Register an account using a referral link</li>
                <li>Complete your profile and purchase packages</li>
                <li>Share your referral link to build your network</li>
                <li>Monitor earnings and request withdrawals</li>
                <li>View your network tree and downline performance</li>
            </ol>

            <h3>For Administrators</h3>
            <ol>
                <li>Access admin dashboard at <code>/admin</code></li>
                <li>Manage users, packages, and bonus rules</li>
                <li>Approve or deny withdrawal requests</li>
                <li>Monitor system performance and statistics</li>
                <li>Configure commission structures and settings</li>
            </ol>
        </div>

        <!-- Footer -->
        <div class="footer">
            <h3>🎉 Ready to Get Started?</h3>
            <p>Follow the installation guide above and start building your MLM network today!</p>
            <p>
                <strong>Repository:</strong> <a href="https://github.com/codeace110/mlm" style="color: white;">https://github.com/codeace110/mlm</a><br>
                <strong>License:</strong> MIT License<br>
                <strong>Version:</strong> 1.0.0
            </p>
        </div>
    </div>
</body>
</html>