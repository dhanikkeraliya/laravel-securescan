# 🔐 Laravel SecureScan - Security Scanner for Laravel Applications

[![Latest Version](https://img.shields.io/packagist/v/dhanikkeraliya/laravel-securescan.svg?style=flat-square)]()
[![License](https://img.shields.io/badge/license-MIT-blue?style=flat-square)](LICENSE)
[![PHP Version](https://img.shields.io/badge/php-%3E%3D8.1-blue?style=flat-square)]()
[![Laravel](https://img.shields.io/badge/laravel-9%20to%2011-red?style=flat-square)]()

---

## 🚀 Overview

**Laravel SecureScan** is a powerful security analysis tool for Laravel applications.

##🔥 Real-time Laravel security scanner with live dashboard (no queue required)

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
## 🚫 Ignore Rules (`.securescan-ignore`)

Laravel SecureScan allows you to ignore specific files, patterns, or rules using a `.securescan-ignore` file placed in the **project root**.

This helps reduce noise and avoid false positives in your scans.

---

### 📄 Example

Create a file:

```bash
.securescan-ignore
```

Add rules like:

```bash
# Ignore specific files
app/Models/Test.php

# Ignore by pattern
*/Seeder.php

# Ignore by rule
SQL Injection
XSS
```

---

### 🔍 Supported Ignore Types

#### 1. Ignore Specific File

```bash
app/Models/Test.php
```

#### 2. Ignore by Pattern

```bash
*/Seeder.php
```

#### 3. Ignore by Rule Type

```bash
SQL Injection
XSS
```

---

### ⚙️ Usage

Run scan with ignore rules enabled:

```bash
php artisan security:scan --ignore
```

---

### ⚠️ Important Notes

* Ignore rules are applied **after scan results are generated**
* Rule matching is based on:

  * File path
  * Finding type (e.g., SQL Injection)
* Keep rules minimal to avoid hiding real vulnerabilities

---

### 💡 Best Practice

Use ignore rules only when:

* You have verified a false positive
* The issue is intentionally handled in your code

Avoid blindly ignoring critical issues.

---

---

## 🖥️ Dashboard Preview
<img width="1258" height="624" alt="image" src="https://github.com/user-attachments/assets/f211c83c-08d2-4669-a15d-f2fd792ad549" />


### CLI Output
<img width="566" height="428" alt="image" src="https://github.com/user-attachments/assets/ce8e0f12-3fb0-41f7-8e4f-9f10a0df1a50" />


## Why SecureScan?

Most Laravel security tools:
- Only scan dependencies ❌
- No UI ❌
- No real-time feedback ❌

SecureScan provides:
- Code-level scanning ✅
- Real-time dashboard ✅
- Developer-friendly output ✅

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
