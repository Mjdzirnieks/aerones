
# Aerones PHP Downloader - Proof of Concept

This project is a proof-of-concept PHP application developed for the Aerones Senior Backend Developer Assignment.

It downloads multiple files concurrently, supports resuming interrupted downloads, handles errors with automatic retry logic, and provides detailed logging.

---

## 🚀 Technologies Used

- PHP 8.1+
- ReactPHP (event-loop, http-client)
- Monolog (for logging)

---

## 📋 Features

- **Concurrent downloads** using ReactPHP event loop
- **Resume downloads** from the point of interruption (using HTTP `Range` headers)
- **Automatic retry** with exponential backoff in case of errors (network failures, timeouts)
- **Detailed logging** of all major events: start, progress, retries, errors, and completion
- **Partial download storage** in `tmp/` folder; completed files moved to `completed/` folder
- **Simple setup and execution**

---

## ⚙️ Setup Instructions

1. Clone or unzip the repository:

```bash
git clone <repository-url>
cd aerones-downloader
```

2. Install dependencies:

```bash
composer install
```

3. Create necessary folders:

```bash
mkdir tmp completed logs
```

4. Run the application:

```bash
php public/index.php
```

---

## 📂 Project Structure

```
/src
  Downloader.php
/public
  index.php
/tmp           (Temporary downloads)
/completed     (Finished downloads)
/logs          (Application logs)
composer.json
README.md
```

---

## ✅ Assumptions

- The remote server supports HTTP `Range` requests.
- Network interruptions only occur on the client-side.
- Files are sufficiently large to allow testing of resumption functionality.
- No authentication is required to access the URLs.

---

## 🔍 Testing Instructions

To manually test the download resumption functionality:

1. Start downloading with `php public/index.php`.
2. During download progress, disable the network connection (Wi-Fi OFF or unplug Ethernet).
3. Observe retry attempts and error logging.
4. Restore network connection.
5. The application should automatically resume downloads from the point they were interrupted.

---

## 🧠 Design Decisions

- **ReactPHP** was chosen to achieve non-blocking concurrent downloads in PHP.
- **HTTP `Range` header** is used to resume downloads from partial file sizes.
- **Exponential backoff** strategy is applied for retrying failed downloads.
- **Monolog** is used for flexible and detailed event logging.
- Simple structure was preferred to keep the focus on the main problem — download concurrency and resilience.

---

## 📜 Notes

This project is intended as a technical demonstration only and is not a production-ready software.

---
