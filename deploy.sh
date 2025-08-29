#!/bin/bash
set -euo pipefail
trap 'echo "❌ Script failed at line $LINENO"' ERR

# Load environment variables
if [ ! -f ".env" ]; then
  echo "❌ .env file not found in $(pwd). Aborting."
  exit 1
fi
set -a
# shellcheck disable=SC1091
source .env
set +a

echo "🚀 Andrej Tokens – Deployment Script"
echo "===================================="

echo "🔍 Using FTP config:"
echo "  HOST: ${FTP_HOST:-<unset>}"
echo "  USER: ${FTP_USER:-<unset>}"
echo "  DIR : ${FTP_DIR:-<unset>}"

if [[ -z "${FTP_HOST:-}" || -z "${FTP_USER:-}" || -z "${FTP_PASS:-}" ]]; then
  echo "❌ Missing one or more FTP credentials. Aborting."
  exit 1
fi

echo "📁 Current path: $(pwd)"

# Prepare deployment dir

echo "📂 Preparing deployment files..."
rm -rf ./deploy
mkdir -p ./deploy


# Build Tailwind CSS (prefer styles.src.css → styles.css)
if command -v npx >/dev/null 2>&1; then
  if [ -f "styles.src.css" ]; then
    echo "🔨 Building Tailwind CSS (styles.src.css → styles.css)..."
    npx tailwindcss -i ./styles.src.css -o ./styles.css --minify || {
      echo "⚠️  Tailwind build failed; deploying existing styles.css";
    }
  elif [ -f "styles.css" ] && grep -q "@tailwind" "styles.css"; then
    echo "🔨 Building Tailwind CSS (styles.css contains @tailwind)..."
    npx tailwindcss -i ./styles.css -o ./styles.css --minify || {
      echo "⚠️  Tailwind build failed; deploying raw styles.css";
    }
  else
    echo "ℹ️  No Tailwind source found; skipping Tailwind build"
  fi
else
  echo "ℹ️  npx not found; skipping Tailwind build (deploying existing styles.css)"
fi


# Copy application files (flat root only)
echo "📋 Copying application files (flat root only)..."

FILES_TO_COPY=(
  404.html
  500.html
  503.html
  .env
  about.php
  api.php
  categories.json
  config.php
  community.php
  idea.php
  index.php
  main.js
  privacy.php
  styles.css
  submit-idea.php
  user.php
  includes/head.php
  includes/header.php
  includes/footer.php
  includes/gdpr-consent.php
)

for f in "${FILES_TO_COPY[@]}" .htaccess; do
  if [ -f "$f" ]; then
    if [[ "$f" == includes/* ]]; then
      mkdir -p "./deploy/includes"
      cp "$f" "./deploy/$f"
    else
      cp "$f" ./deploy/
    fi
  else
    echo "ℹ️  Skipped missing file: $f"
  fi
done

# Copy Tindlekit UI system
echo "🎨 Copying Tindlekit UI system..."
if [ -d "ui" ]; then
  mkdir -p "./deploy/ui"
  cp -r ui/* "./deploy/ui/"
  echo "✅ UI system copied (ui/)"
else
  echo "ℹ️  No ui/ directory found; skipping UI system copy"
fi

# Set production environment in deployed .env
echo "🔧 Setting ENV=production for deployment..."
if [ -f "./deploy/.env" ]; then
  sed -i.bak 's/ENV=local/ENV=production/' "./deploy/.env" && rm "./deploy/.env.bak"
  echo "✅ Environment set to production"
else
  echo "⚠️  No .env file found in deploy directory"
fi

# Include uploads folder structure (empty) if exists locally
if [ -d uploads ]; then
  mkdir -p ./deploy/uploads
fi

 # Ensure a basic .htaccess exists
if [ ! -f "./deploy/.htaccess" ]; then
  echo "🔧 Creating basic .htaccess file..."
  cat > ./deploy/.htaccess << 'HTACCESS'
RewriteEngine On

# Force HTTPS
RewriteCond %{HTTPS} !=on
RewriteRule ^(.*)$ https://%{HTTP_HOST}%{REQUEST_URI} [L,R=301]

# Serve custom error pages
ErrorDocument 404 /404.html
ErrorDocument 500 /500.html
ErrorDocument 503 /503.html

# Alternative error document format for some shared hosts
# ErrorDocument 404 "404.html"
# ErrorDocument 500 "500.html"
# ErrorDocument 503 "503.html"

# Enable gzip compression
<IfModule mod_deflate.c>
    AddOutputFilterByType DEFLATE text/plain
    AddOutputFilterByType DEFLATE text/html
    AddOutputFilterByType DEFLATE text/xml
    AddOutputFilterByType DEFLATE text/css
    AddOutputFilterByType DEFLATE application/xml
    AddOutputFilterByType DEFLATE application/xhtml+xml
    AddOutputFilterByType DEFLATE application/rss+xml
    AddOutputFilterByType DEFLATE application/javascript
    AddOutputFilterByType DEFLATE application/x-javascript
    AddOutputFilterByType DEFLATE text/javascript
</IfModule>

# Set correct MIME types for JavaScript modules
<IfModule mod_mime.c>
    AddType application/javascript .js
    AddType text/css .css
</IfModule>

# Security headers
<IfModule mod_headers.c>
    Header always set X-Content-Type-Options nosniff
    Header always set X-Frame-Options DENY
    Header always set X-XSS-Protection "1; mode=block"
    Header always set Referrer-Policy "strict-origin-when-cross-origin"
</IfModule>

# Disable directory browsing
Options -Indexes

    # Pretty routes
    # /idea/{slug} -> idea.php?slug={slug}
    RewriteRule ^idea/([a-z0-9-]+)/?$ idea.php?slug=$1 [L,QSA]
    # /user/{username} -> user.php?username={username}
    RewriteRule ^user$ - [R=404,L]
    RewriteRule ^user/$ - [R=404,L]
    RewriteRule ^user/([A-Za-z0-9._-]+)/?$ user.php?username=$1 [L,QSA]

# Pretty URLs (remove .php from URLs)
RewriteCond %{REQUEST_FILENAME} !-d
RewriteCond %{REQUEST_FILENAME}\.php -f
RewriteRule ^(.+?)/?$ $1.php [L]

# /category/Name -> index.php?category=Name
RewriteRule ^category/(.+)$ index.php?category=$1 [L,QSA]

# Protect sensitive files
# Apache 2.4 syntax
<Files ".env">
    Require all denied
</Files>
<FilesMatch "\.(md|json|lock|sql|sqlite|yml|yaml)$">
    Require all denied
</FilesMatch>

# Legacy Apache 2.2 fallback (shared hosting)
<IfModule !mod_authz_core.c>
  <FilesMatch "\.(env|md|json|lock|sql|sqlite|yml|yaml)$">
      Order allow,deny
      Deny from all
  </FilesMatch>
</IfModule>

# PHP settings for shared hosting
<IfModule mod_php.c>
    php_value memory_limit 128M
    php_value max_execution_time 30
    php_value upload_max_filesize 20M
    php_value post_max_size 20M
</IfModule>
HTACCESS
fi

echo "📊 Deployment package contents:"
ls -la ./deploy

echo "🔍 Checking critical files:"
for critical_file in "404.html" "500.html" "503.html" "index.php" ".htaccess"; do
  if [ -f "./deploy/$critical_file" ]; then
    echo "✅ $critical_file found"
  else
    echo "❌ $critical_file MISSING"
  fi
done

# Cache busting (flat root only)
TS=$(date +%s)
if [ -f "./deploy/styles.css" ]; then
  echo "/* Updated: $TS */" >> ./deploy/styles.css
  echo "✅ CSS cache-bust appended (styles.css)"
else
  echo "ℹ️  No styles.css found for cache-bust (skipped)"
fi
if [ -f "./deploy/main.js" ]; then
  echo "// Updated: $TS" >> ./deploy/main.js
  echo "✅ JS cache-bust appended (main.js)"
else
  echo "ℹ️  No main.js found for cache-bust (skipped)"
fi

echo "🧘 Pausing for 2s before deploy..."
sleep 2

echo "📤 Uploading to $FTP_HOST as $FTP_USER into $FTP_DIR..."

lftp -u "$FTP_USER","$FTP_PASS" "$FTP_HOST" <<EOF
set ftp:list-options -a
set cmd:fail-exit yes
set net:max-retries 3
set net:timeout 30
set ftp:ssl-allow no
cd "$FTP_DIR"
mirror -R --only-newer --parallel=2 --verbose \
  --exclude-glob="*.md" \
  --exclude-glob="*.git*" \
  ./deploy .
bye
EOF

echo "🧹 Cleaning up deployment directory..."
chmod -R 755 ./deploy 2>/dev/null || true
rm -rf ./deploy

echo "✅ Deployment complete!"
echo "🌐 Site should be live shortly at your configured domain."
