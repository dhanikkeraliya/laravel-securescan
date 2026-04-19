# 🔐 Laravel SecureScan - Security Scanner for Laravel Applications

[![Latest Version](https://img.shields.io/packagist/v/dhanikkeraliya/laravel-securescan.svg?style=flat-square)]()
[![License](https://img.shields.io/badge/license-MIT-blue?style=flat-square)](LICENSE)
[![PHP Version](https://img.shields.io/badge/php-%3E%3D8.1-blue?style=flat-square)]()
[![Laravel](https://img.shields.io/badge/laravel-9%20to%2011-red?style=flat-square)]()

---

## 🚀 Overview

**Laravel SecureScan** is a powerful security analysis tool for Laravel applications.

It scans your codebase to detect:

* 🔴 Critical vulnerabilities (SQL Injection, XSS, Secrets)
* 🟡 Security misconfigurations
* 🟢 Best practice issues

It provides:

* ⚡ CLI-based scanning
* 📊 Real-time web dashboard
* 📁 Detailed findings with fixes

---

## 🔥 Features

### ✅ CLI Scanner

* Real-time progress bar
* Colored severity output
* Detailed issue + fix suggestions

### ✅ Web Dashboard

* Live scanning (no queue required)
* Progress tracking
* Severity charts (High / Medium / Low)
* Live logs (terminal-style)
* Findings table

### ✅ Security Checks

#### 🔴 High Severity

* SQL Injection detection
* XSS vulnerabilities
* Hardcoded secrets
* ENV exposure
* Dangerous PHP functions
* Sensitive data logging

#### 🟡 Medium Severity

* Missing authorization
* Mass assignment issues
* File upload risks
* Open redirects
* Rate limiting issues
* Unvalidated input

#### 🟢 Low Severity

* Weak random usage
* Hardcoded URLs

---

## 📦 Installation

```bash
composer require dhanikkeraliya/laravel-securescan
```

---

## ⚙️ Configuration

Publish config:

```bash
php artisan vendor:publish --tag=securescan-config
```

---

## 🔍 Usage

### CLI Scan

```bash
php artisan security:scan
```

---

### Web Dashboard

```bash
http://localhost:8000/_securescan
```

---

## 🖥️ Dashboard Preview
<img width="1258" height="624" alt="image" src="https://github.com/user-attachments/assets/f211c83c-08d2-4669-a15d-f2fd792ad549" />


### CLI Output
<img width="566" height="428" alt="image" src="https://github.com/user-attachments/assets/ce8e0f12-3fb0-41f7-8e4f-9f10a0df1a50" />


### Web Dashboard

---

## ⚠️ Security Notice

This package is intended for **development and internal use only**.

Do NOT expose the dashboard publicly without authentication.

---

## 📌 Roadmap

* [ ] Real-time WebSocket scanning
* [ ] CI/CD integration
* [ ] Scan history & reports
* [ ] Export (PDF/JSON)
* [ ] SaaS dashboard

---

## 🤝 Contributing

Contributions are welcome!

Steps:

1. Fork the repo
2. Create feature branch
3. Submit PR

---

## 🔐 Security

If you find any vulnerabilities, please check [SECURITY.md](SECURITY.md).

---

## 📄 License

MIT License
