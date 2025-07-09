# 🚀 PHP Headless CMS – The Fast, Flexible, Open-Source CMS for Modern PHP Projects

> **A truly headless, API-first CMS, built with pure PHP, Tailwind CSS, Alpine.js, and MySQL.**  
> Simple to install, easy to extend, and perfect for solo devs, agencies, or anyone building websites, apps, or digital products.

---

## ✨ Why PHP Headless CMS?

Strapi changed the game for Node.js developers—but PHP devs have been stuck with outdated, bloated, or overcomplicated tools.  
**Not anymore.**

PHP Headless CMS is a real answer for the PHP community:  
- **No React. No NPM. No "just use Laravel."**
- Instantly spin up content collections, build APIs, and manage media from a beautiful admin panel.
- Built from scratch for **performance, flexibility, and developer happiness**.

---

## 🔥 Features

- **Instant Setup:** Get started in minutes. No CLI, no hidden gotchas.
- **Visual Content Type Builder:** Drag-and-drop UI for creating fields, with 11+ field types (text, rich text, media, date, select, etc).
- **Dynamic Content Management:** Create, edit, filter, and manage any content. Draft/publish, bulk operations, full search.
- **API-First:** All content instantly available via RESTful APIs—no extra config, ever.
- **Modern Media Library:** Upload images and docs, manage from UI or API.
- **Flexible Database:** MySQL backend, JSON fields for unlimited, schema-less content structures.
- **Responsive UI:** Built with Tailwind CSS + Alpine.js—clean, fast, and mobile-ready.
- **Simple Auth:** Admin login out of the box; session-based, JWT coming soon.
- **MIT Licensed & Open Source:** Fork, extend, use for personal or commercial projects.

---

## 🏗️ Project Structure

php-headless-cms/
├── app/
│ ├── Core/ # Framework, config, DB, routing
│ ├── Controllers/ # Admin & installer logic
│ ├── Models/ # [For future use]
│ ├── Services/ # [Coming soon]
│ ├── Middleware/ # [Planned: Auth, CORS, etc.]
│ ├── Helpers/ # Shared logic & utilities
│ └── Views/ # Admin panel & installer screens
├── config/ # App, DB, routes
├── storage/ # Uploads, cache, logs, install marker
├── public/ # index.php, assets, .htaccess
├── vendor/ # Composer dependencies
├── .env # Environment config (created on install)
├── .env.example # Example env file
└── composer.json # PHP dependencies


**Database tables:**  
- `users`, `content_types`, `content_entries`, `media`, `settings`  
All major content fields use flexible JSON columns for dynamic, schema-less data.

---

## 🚦 Quick Start

```bash
git clone https://github.com/yourusername/php-headless-cms.git
cd php-headless-cms
composer install
cp .env.example .env
chmod 755 storage/ public/uploads/
# Now open your browser and go to /install to complete the 4-step installation.
# Default admin login: admin / admin (please change this during install!)
🛣️ Main Routes
Path	Purpose
/install	Guided installation (4 steps)
/admin	Admin panel (dashboard, content types, entries)
/api/{type}	RESTful API for your content types

🗺️ Roadmap
 Drag & Drop Content Type Builder

 Dynamic API Endpoints

 Admin Dashboard (Stats, Quick Actions)

 Media Library

 RESTful API with filtering, pagination, auth

 User management (roles & permissions, API keys)

 JWT Authentication (for APIs)

 Plugin/Extension system

 Content relations (link types, parent/child)

 Auto-generated API documentation

 More advanced media management (thumbnails, cropping)

Want to help? Star the repo, file issues, or open PRs!

💡 Why This Project Exists
Too many PHP CMS options are heavy, legacy, or locked into frameworks you may not want.
PHP Headless CMS gives you the power of headless/content APIs with the simplicity and raw speed of PHP.

Build faster MVPs and real products.

Ship APIs for web, mobile, or Jamstack projects—without a JavaScript backend.

Open source, for the PHP community.

🤝 Contributing
Feedback, bug reports, PRs, and ideas are not just welcome—they’re needed!
Open an issue or discussion, and let’s build something the PHP community actually wants.

📢 License
MIT License. Free for personal or commercial use.
You own your data and your code.

🚀 Let’s Make PHP Exciting Again!
Star, fork, and share.
If you believe in modern, fast, open PHP—this project is for you.

Inspired by the flexibility of @Strapi, but with the power and speed of PHP.
Built for devs, by a dev. If you build something cool, let us know!
