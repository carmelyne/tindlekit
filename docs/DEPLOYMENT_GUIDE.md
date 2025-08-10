# 🚀 15-Minute Deployment Guide

*From fork to live platform in under 15 minutes*

---

## 🎯 Quick Start Checklist

- [ ] **2 min:** Fork repository and download files
- [ ] **3 min:** Set up database (SQLite or MySQL)
- [ ] **5 min:** Upload files to shared hosting
- [ ] **3 min:** Configure domain and test endpoints
- [ ] **2 min:** Customize branding and submit first idea

**Total: 15 minutes to live platform** ⏰

---

## 📋 Prerequisites

### **What You Need**

- Shared hosting account (Bluehost, SiteGround, etc.)
- Domain name (or subdomain)
- FTP access or file manager
- Basic text editor

### **What You Don't Need**

- Command line experience
- Server administration knowledge
- Complex build tools or dependencies
- Database administration skills

---

## 🗄️ Database Setup Options

### **Option A: SQLite** (Recommended for beginners)

**Pros:** No database setup required, works anywhere
**Cons:** Limited to moderate traffic

```bash
# Files will automatically create the SQLite database
# Just make sure /backend/db/ folder is writable
chmod 755 backend/db/
```

### **Option B: MySQL** (Better for production)

**Pros:** Better performance, shared hosting optimized
**Cons:** Requires database creation

1. **Create Database in cPanel**
   - Log into your hosting control panel
   - Find "MySQL Databases" section
   - Create new database: `yourdomain_andrej`
   - Create user with full permissions

2. **Update Configuration**

   ```php
   // backend/config.php
   $db_type = 'mysql';
   $db_host = 'localhost';
   $db_name = 'yourdomain_andrej';
   $db_user = 'yourdomain_user';
   $db_pass = 'your_secure_password';
   ```

---

## 📁 File Upload Strategy

### **Method 1: FTP Upload** (Most Reliable)

```
Your hosting root/
├── public_html/
│   ├── backend/           # Upload entire backend folder
│   ├── frontend/          # Upload entire frontend folder
│   ├── .htaccess          # Important for URL routing
│   └── index.php          # Points to frontend/index.html
```

### **Method 2: File Manager** (Built into most cPanel)

1. Access File Manager in hosting control panel
2. Navigate to `public_html` or `www` folder
3. Upload project zip file
4. Extract in the correct location
5. Set file permissions (755 for folders, 644 for files)

### **Method 3: Git Deploy** (If supported)

```bash
# Some hosts support git deployment
git clone https://github.com/yourusername/tindlekit.git
cd tindlekit
# Follow host-specific deployment steps
```

---

## ⚙️ Configuration Files

### **1. .htaccess Setup** (Critical for routing)

```apache
# In public_html/.htaccess
RewriteEngine On
RewriteRule ^api/(.*)$ backend/index.php [QSA,L]
RewriteRule ^$ frontend/index.html [L]

# Security headers
Header always set X-Content-Type-Options nosniff
Header always set X-Frame-Options DENY
Header always set X-XSS-Protection "1; mode=block"

# Enable CORS for local development
Header always set Access-Control-Allow-Origin "*"
Header always set Access-Control-Allow-Methods "GET, POST, OPTIONS"
Header always set Access-Control-Allow-Headers "Content-Type"
```

### **2. PHP Configuration** (backend/config.php)

```php
<?php
// Database Configuration
$db_type = 'sqlite'; // or 'mysql'

if ($db_type === 'sqlite') {
    $db_path = __DIR__ . '/db/tindlekit.db';
    $pdo = new PDO("sqlite:$db_path");
} else {
    // MySQL settings
    $db_host = 'localhost';
    $db_name = 'your_database_name';
    $db_user = 'your_username';
    $db_pass = 'your_password';
    $pdo = new PDO("mysql:host=$db_host;dbname=$db_name", $db_user, $db_pass);
}

// Initialize database if needed
$pdo->exec(file_get_contents(__DIR__ . '/db/schema.sql'));
?>
```

### **3. Frontend Configuration** (frontend/assets/js/config.js)

```javascript
// API Configuration
const API_BASE_URL = '/api'; // Relative URL for same domain
// const API_BASE_URL = 'https://yourdomain.com/api'; // Absolute URL if needed

// Branding Configuration
const SITE_TITLE = 'The Andrej Effect - Your Community';
const TOKEN_EMOJI = '🍀'; // Customize your token emoji
const MAX_VOTES_PER_SESSION = 10; // Prevent spam voting
```

---

## 🧪 Testing Your Deployment

### **1. Manual Testing Checklist**

- [ ] **Homepage loads:** Visit your domain
- [ ] **Ideas display:** See sample ideas on leaderboard
- [ ] **Voting works:** Click token button, count increases
- [ ] **New idea submission:** Fill form, idea appears
- [ ] **Mobile responsive:** Test on phone browser
- [ ] **Error handling:** Try invalid inputs

### **2. API Endpoint Testing**

```bash
# Test GET endpoint
curl https://yourdomain.com/api/ideas

# Test POST endpoint
curl -X POST https://yourdomain.com/api/vote \
  -H "Content-Type: application/json" \
  -d '{"id": 1}'

# Test idea submission
curl -X POST https://yourdomain.com/api/idea \
  -H "Content-Type: application/json" \
  -d '{"title": "Test Idea", "description": "Testing deployment"}'
```

### **3. Performance Testing**

- Check page load speed with browser dev tools
- Test with multiple concurrent users (ask friends to test)
- Verify database performance with sample data
- Monitor hosting resource usage

---

## 🔧 Common Issues & Solutions

### **Issue: 500 Internal Server Error**

**Causes & Solutions:**

```php
// 1. Check file permissions
chmod 755 backend/
chmod 644 backend/*.php

// 2. Verify PHP version (requires 7.0+)
// Check in hosting control panel

// 3. Enable error reporting temporarily
ini_set('display_errors', 1);
error_reporting(E_ALL);
```

### **Issue: Database Connection Failed**

**SQLite Solutions:**

```bash
# Ensure database folder is writable
chmod 755 backend/db/
chmod 666 backend/db/andrej.db # If file exists
```

**MySQL Solutions:**

```php
// Double-check credentials in config.php
// Verify database exists in hosting control panel
// Test connection with simple script:
try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname", $user, $pass);
    echo "Connection successful!";
} catch(PDOException $e) {
    echo "Connection failed: " . $e->getMessage();
}
```

### **Issue: Frontend Can't Reach Backend**

**Common Solutions:**

```javascript
// 1. Check API URL in frontend config
const API_BASE_URL = '/api'; // Should work for most setups

// 2. Verify .htaccess routing
// Make sure rewrite rules are active

// 3. Test API directly in browser
// Visit: https://yourdomain.com/api/ideas
```

### **Issue: CORS Errors**

**Solution:**

```apache
# Add to .htaccess
Header always set Access-Control-Allow-Origin "*"
Header always set Access-Control-Allow-Methods "GET, POST, OPTIONS"
Header always set Access-Control-Allow-Headers "Content-Type"
```

---

## 🎨 Quick Customization Guide

### **1. Branding (5 minutes)**

```css
/* frontend/assets/css/styles.css */
:root {
  --primary-color: #your-brand-color;
  --token-color: #your-token-color;
  --background: #your-background;
}
```

```html
<!-- frontend/index.html -->
<title>Your Community - Idea Funding Platform</title>
<h1>🎯 Your Community Ideas</h1>
```

### **2. Sample Data (3 minutes)**

```sql
-- Add to backend/db/schema.sql
INSERT INTO ideas (title, description, votes) VALUES
('Welcome to Your Community', 'This is your first sample idea!', 5),
('Customize This Platform', 'Fork and make it your own', 3),
('Share Your Ideas', 'What should we build next?', 1);
```

### **3. Community Rules (2 minutes)**

```markdown
<!-- Add to frontend/index.html -->
<div class="community-rules">
  <h3>Community Guidelines</h3>
  <ul>
    <li>✅ Educational projects encouraged</li>
    <li>✅ Open source ideas preferred</li>
    <li>✅ Be kind and constructive</li>
    <li>❌ No spam or self-promotion</li>
  </ul>
</div>
```

---

## 📊 Monitoring & Maintenance

### **Weekly Tasks** (15 minutes)

- [ ] Check error logs for issues
- [ ] Review new ideas for spam/inappropriate content
- [ ] Backup database
- [ ] Monitor hosting resource usage

### **Monthly Tasks** (30 minutes)

- [ ] Update PHP and hosting environment
- [ ] Review and moderate old ideas
- [ ] Analyze usage patterns
- [ ] Plan feature improvements

### **Tools for Monitoring**

- Google Analytics for traffic
- Hosting control panel for resource usage
- Simple logging in PHP for error tracking
- Community feedback for feature requests

---

## 🌟 Success Indicators

**Week 1:** Platform is live and functional
**Week 2:** First community ideas submitted
**Month 1:** Regular voting activity and engagement
**Month 3:** Ideas getting built or implemented
**Month 6:** Active contributor community

---

## 🆘 Getting Help

### **Documentation Resources**

- Read through all `/docs/` files
- Check `/docs/LLM_PROMPTS.md` for AI assistance
- Review GitHub issues for similar problems

### **Community Support**

- Open GitHub issue with detailed error information
- Join community discussions in repository
- Share your deployment experience to help others

### **Professional Support**

- Contact your hosting provider for server issues
- Consider hiring freelancer for complex customizations
- Upgrade hosting plan if performance becomes limiting

---

🍀 **Remember:** The goal isn't perfect deployment - it's getting started and learning through building. Don't let perfect be the enemy of functional!

*Deploy fast. Learn faster. Help others deploy too.*
