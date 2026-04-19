# 🔐 Security Policy

## 📦 Supported Versions

The following versions of **Laravel SecureScan** are currently supported with security updates:

| Version | Supported |
| ------- | --------- |
| 1.x     | ✅ Yes     |
| < 1.0   | ❌ No      |

---

## 🚨 Reporting a Vulnerability

If you discover a security vulnerability in **Laravel SecureScan**, please report it responsibly.

### 📧 Contact

Please send an email to:

**Email:** [dhanikkeraliya@gmail.com](mailto:dhanikkeraliya@gmail.com)

---

## 📝 What to Include

To help us resolve the issue quickly, please include:

* A clear description of the vulnerability
* Steps to reproduce the issue
* Affected version(s)
* Potential impact (e.g., data exposure, code execution)
* Proof of concept (if available)
* Suggested fix (optional but appreciated)

---

## ⏱️ Response Timeline

We aim to respond as follows:

* Initial response: **within 48 hours**
* Status update: **within 3–5 days**
* Fix release: depends on severity and complexity

---

## 🔒 Responsible Disclosure

We kindly ask that you:

* **Do NOT publicly disclose** the vulnerability before a fix is released
* Allow us reasonable time to investigate and patch the issue
* Avoid exploiting the vulnerability beyond necessary proof-of-concept

We will acknowledge and credit responsible disclosures (if desired).

---

## 🛡️ Security Best Practices for Users

Since Laravel SecureScan analyzes your application code and configuration:

### ⚠️ Important Warnings

* Do NOT expose the dashboard publicly without authentication
* Restrict access using middleware (e.g., `auth`)
* Avoid running scans on production systems without proper access control
* Do not share scan reports containing sensitive data

---

## 🔍 Scope

This security policy applies to:

* Core package functionality
* CLI scanning features
* Web dashboard (routes, controllers, views)

It does NOT cover:

* Misconfiguration in user applications
* Third-party integrations or dependencies

---

## 📢 Security Updates

Security fixes will be released via:

* GitHub releases
* Packagist updates

Users are encouraged to:

```bash
composer update dhanikkeraliya/laravel-securescan
```

to receive the latest security patches.

---

## 🙏 Acknowledgements

We appreciate the efforts of security researchers and contributors who help improve this package.

Your responsible disclosure helps make Laravel SecureScan safer for everyone.

---

## 📄 License

This project is open-source and licensed under the MIT License.
