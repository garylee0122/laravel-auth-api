# Laravel Auth API

## 📌 專案介紹

這是一個使用 Laravel + Sanctum 建立的登入驗證 API，提供使用者註冊、登入、Token 驗證與 API 保護功能。

使用 Laravel 搭配 Sanctum 驗證機制開發 RESTful API。
* 實作使用者驗證功能（註冊／登入），並採用安全的密碼雜湊處理
* 建構商品 CRUD API，包含資料驗證與 API Resource 轉換
* 採用 Service Layer 架構，實現乾淨的程式碼分層設計
* 設計基於使用者的授權機制，確保用戶僅能存取自己的資料
* 整合 Laravel Sanctum 實現基於 Token 的身份驗證

---

## 🚀 技術

* Laravel 12
* MySQL
* Laravel Sanctum（Token Authentication）

---

## ✨ 功能

* 使用者註冊（Register）
* 使用者登入（Login）
* Token 發放（Sanctum）
* API 驗證（Auth Middleware）
* 受保護 API（/api/me）
* 使用 Bearer Token 呼叫 API

---

## 📂 API 說明

### 註冊

POST /api/register

---

### 登入

POST /api/login

---

### 取得使用者（需登入）

GET /api/me

Header:
Authorization: Bearer {token}

---

## 📌 回傳格式

{
"status": "success",
"data": {}
}

---

## 🧠 專案設計

* Controller：處理 API 請求
* Sanctum：Token 驗證
* Middleware：保護 API
* MySQL：資料儲存

---

## 📎 學習重點

* Token-based Authentication
* API 安全機制
* Laravel Sanctum 使用
* RESTful API 設計

---

## 👨‍💻 作者

Gary Lee
