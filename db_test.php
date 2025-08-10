<?php
// Debug database connection
echo "🔧 Database Connection Test\n";
echo "=========================\n";

// Load config
require_once __DIR__ . '/config.php';

echo "ENV: " . getenv('ENV') . "\n";
echo "DB_DSN: " . $DB_DSN . "\n";
echo "DB_USER: " . $DB_USER . "\n";
echo "DB_PASS: " . (empty($DB_PASS) ? '(empty)' : '(set)') . "\n";

try {
    $pdo = new PDO($DB_DSN, $DB_USER, $DB_PASS, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    ]);
    echo "✅ Database connection successful!\n";
    
    // Test query
    $stmt = $pdo->prepare('SELECT COUNT(*) as count FROM ideas');
    $stmt->execute();
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    echo "✅ Found {$result['count']} ideas in database\n";
    
    // Test data
    $stmt = $pdo->prepare('SELECT id, title, tokens FROM ideas LIMIT 3');
    $stmt->execute();
    $ideas = $stmt->fetchAll(PDO::FETCH_ASSOC);
    echo "✅ Sample data:\n";
    foreach ($ideas as $idea) {
        echo "  - {$idea['title']} ({$idea['tokens']} tokens)\n";
    }
    
} catch (Throwable $e) {
    echo "❌ Database error: " . $e->getMessage() . "\n";
    echo "File: " . $e->getFile() . ":" . $e->getLine() . "\n";
}
?>