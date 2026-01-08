# 📍 SIPRESMAGMTH34 — Internship Attendance System

[![PHP](https://img.shields.io/badge/PHP-%23777BB4?style=for-the-badge&logo=php&logoColor=white)](https://www.php.net/)
[![JavaScript](https://img.shields.io/badge/JavaScript-%23323330?style=for-the-badge&logo=javascript&logoColor=%23F7DF1E)](https://developer.mozilla.org/en-US/docs/Web/JavaScript)
[![MySQL](https://img.shields.io/badge/MySQL-4479A1?style=for-the-badge&logo=mysql&logoColor=white)](https://www.mysql.com/)
[![Face API](https://img.shields.io/badge/Face_API_js-%23ff69b4?style=for-the-badge)](https://github.com/justadudewhohacks/face-api.js)
[![Leaflet](https://img.shields.io/badge/Leaflet-199900?style=for-the-badge&logo=leaflet&logoColor=white)](https://leafletjs.com/)

**SIPRESMAGMTH34** is a web-based **attendance system** for interns at **Badan Pemeriksa Keuangan (BPK) Perwakilan DKI Jakarta** featuring **face recognition**, **location-based attendance**, and **multi-role access control**.

🌍 **Live Demo:** https://sipresmagmth34.com/  
🔗 **Repo:** https://github.com/yuanthio/sipresmagmth34

---

## ✨ Key Features

- **Face Recognition Attendance**  
  Intern attendance verification using real-time face detection with **Face API.js**.

- **Geo-Location Attendance Validation**  
  Attendance submissions validated using **Leaflet.js**-based GPS coordinates and radius restrictions.

- **Multiple User Roles**  
  - 👨‍💼 **Administrator** — Full control over users, schedules, and records.  
  - 👨‍🎓 **Intern** — Attendance check-in/out using face & location.  
  - 👨‍🏫 **Mentor** — Monitor intern attendance and activities.

- **Responsive UI**  
  Built with HTML, CSS, Bootstrap, and JavaScript for accessible use across devices.

- **Database Driven**  
  MySQL used to store user, attendance, and configuration data.

---

## 🛠️ Tech Stack

- **Frontend:**  
  HTML, CSS, Bootstrap, JavaScript, jQuery, Face API.js, Leaflet.js  
- **Backend:**  
  Native PHP  
- **Database:**  
  MySQL  
- **Deployment:**  
  Hosted on custom domain (via service)

---

## 📂 Project Structure

    ```text
    sipresmagmth34/
    ├── apps/                   # Custom application modules (e.g., pengaturan assets)
    │   └── pengaturan/
    │       └── logo/           # Logo & image assets
    ├── config/                 # Configuration files
    │   └── database.php        # Database connection config
    ├── database/               # Database migrations / seeds (if any)
    ├── models/                 # PHP models for business logic
    ├── source/                 # Supporting libraries and plugins
    │   └── plugin/
    │       └── fpdf/           # Library to generate PDF reports
    ├── template/               # HTML/PHP templates & UI layout
    ├── index.php               # Main landing page
    ├── login.php               # Login page
    ├── logout.php              # Logout script
    ├── cek_rating.php          # Rating/validation utilities
    ├── simpan_rating.php       # Rating persistence scripts
    └── readme.txt              # Legacy readme & notes

## Getting Started
1. Clone the Repository
   ```bash
   git clone https://github.com/yuanthio/sipresmagmth34.git
   cd sipresmagmth34
2. Install / Setup Environment
   Copy .env.example or update database config in /config/database.php.
   Example config/database.php setup:
   ```bash
   <?php
   // Database Connection
   $host = "localhost";
   $user = "root";
   $pass = "your_password";
   $db   = "sipresmagmth34_db";
    
   $kon = mysqli_connect($host, $user, $pass, $db) or die("Connection Failed");
   ?>
3. Setup Database
   - Create a database in MySQL (e.g., sipresmagmth34_db).
   - Import provided SQL file (if available) or run schema scripts via phpMyAdmin / CLI.
4. Start Using (Frontend)
   - Place project in your local web server root (e.g., htdocs, www).
   - Open in browser: http://localhost/sipresmagmth34/

## Project Goals
This project demonstrates:
- Integration of AI-based face recognition in a web app.
- Geo-fencing attendance validation with Leaflet.js.
- Classic PHP + MySQL stack with modular folder organization.
- Multi-role access control with user-friendly UI & UX.
