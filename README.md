# FinTrack — Personal Finance Tracker

FinTrack is a modern personal finance management application built with **Laravel**.  
It helps users track expenses, manage budgets, and gain clear insights into their financial habits through a clean, intuitive, and responsive dashboard.

---

## Table of Contents

- Overview  
- Features  
- Technology Stack  
- Installation  
- Project Structure  
- Database Design  
- Usage Guide  
- Data Export  
- API Endpoints  
- Configuration  
- Security  
- License  

---

## Overview

**finTrack** enables individuals to take control of their finances by offering structured expense tracking, budget planning, and insightful financial reporting.  
The application follows Laravel best practices and ensures secure data isolation for each user.

---

## Features

### Core Features

- User Authentication (Register, Login, Logout)
- Expense Management (Create, Read, Update, Delete)
- Monthly Budget Planning
- Category Management with color customization
- Financial Dashboard with analytics
- Data Export (PDF, Excel, CSV)
- Fully Responsive Design

### Advanced Features

- Filter expenses by date range and category
- Budget vs Actual spending comparison
- Recent expenses tracking
- Monthly financial summaries
- Professional report generation
- Secure per-user data isolation

---

## Technology Stack

### Backend

- PHP 8.1+
- Laravel 10.x
- MySQL 8.0+
- Laravel DomPDF
- Laravel Excel

### Frontend

- HTML5
- CSS3 (Bootstrap 5)
- JavaScript (Vanilla)
- Blade Templating Engine
- Chart.js (future enhancements)

### Development Tools

- Composer
- Laravel Artisan CLI
- Git & GitHub

---

## Installation

### Prerequisites

- PHP 8.1+
- Composer
- MySQL
- Node.js & NPM
- Apache or Nginx

---

### Step-by-Step Setup

#### 1. Clone the Repository

```bash
git clone https://github.com/yourusername/finTrack.git
cd finTrack
APP_NAME=finTrack
APP_ENV=local
APP_DEBUG=false
APP_URL=http://localhost:8000

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=finTrack
DB_USERNAME=root
DB_PASSWORD=
Security

CSRF protection enabled

Secure Laravel authentication

Password hashing with Bcrypt

SQL Injection protection via Eloquent ORM

XSS protection using Blade escaping

Ready for API authentication using Laravel Sanctum
