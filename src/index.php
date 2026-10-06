<?php
$host     = getenv('PGHOST')     ?: 'restaurant-db';
$db       = getenv('PGDATABASE') ?: 'restaurant_db';
$user     = getenv('PGUSER')     ?: 'chef_admin';
$password = getenv('PGPASSWORD') ?: 'kitchen_secure_pass';
$port     = getenv('PGPORT')     ?: '5432';

$menu = [];
$db_error = null;

try {
    $dsn = "pgsql:host=$host;port=$port;dbname=$db";
    $pdo = new PDO($dsn, $user, $password, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
    ]);

    // Query categories with their menu items using a JOIN
    $sql = "SELECT c.name AS category_name, m.name AS item_name, m.description, m.price, m.is_available
            FROM categories c
            JOIN menu_items m ON c.id = m.category_id
            WHERE m.is_available = TRUE
            ORDER BY c.display_order ASC, m.name ASC";

    $stmt = $pdo->query($sql);

    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $menu[$row['category_name']][] = $row;
    }

} catch (PDOException $e) {
    $db_error = $e->getMessage();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gourmet Bistro - Menu</title>
    <style>
        :root {
            --primary: #991b1b;
            --accent: #d97706;
            --bg: #fafaf9;
            --card-bg: #ffffff;
            --text: #1c1917;
            --text-muted: #78716c;
            --border: #e7e5e4;
        }

        body {
            font-family: 'Segoe UI', system-ui, -apple-system, sans-serif;
            background-color: var(--bg);
            color: var(--text);
            margin: 0;
            padding: 0;
        }

        header {
            background: linear-gradient(rgba(0,0,0,0.6), rgba(0,0,0,0.6)), url('https://images.unsplash.com/photo-1517248135467-4c7edcad34c4?auto=format&fit=crop&w=1200&q=80') center/cover;
            color: white;
            text-align: center;
            padding: 5rem 1rem;
        }

        header h1 {
            font-size: 3rem;
            margin: 0 0 0.5rem 0;
            letter-spacing: 1px;
        }

        header p {
            font-size: 1.25rem;
            color: #f5f5f4;
            margin: 0;
        }

        main {
            max-width: 900px;
            margin: 2rem auto;
            padding: 0 1rem;
        }

        .error-banner {
            background-color: #fef2f2;
            border: 1px solid #f87171;
            color: #991b1b;
            padding: 1rem;
            border-radius: 8px;
            text-align: center;
        }

        .category-section {
            background: var(--card-bg);
            border-radius: 12px;
            padding: 2rem;
            margin-bottom: 2rem;
            box-shadow: 0 4px 6px -1px rgba(0,0,0,0.05);
            border: 1px solid var(--border);
        }

        .category-title {
            font-size: 1.75rem;
            color: var(--primary);
            border-bottom: 2px solid var(--border);
            padding-bottom: 0.5rem;
            margin-top: 0;
            margin-bottom: 1.5rem;
        }

        .menu-grid {
            display: grid;
            grid-template-columns: 1fr;
            gap: 1.5rem;
        }

        .menu-item {
            display: flex;
            justify-content: space-between;
            align-items: baseline;
            gap: 1rem;
        }

        .item-details {
            flex-grow: 1;
        }

        .item-name {
            font-size: 1.1rem;
            font-weight: 600;
            margin: 0 0 0.25rem 0;
        }

        .item-desc {
            font-size: 0.9rem;
            color: var(--text-muted);
            margin: 0;
        }

        .item-price {
            font-size: 1.15rem;
            font-weight: 700;
            color: var(--accent);
            white-space: nowrap;
        }

        @media (min-width: 640px) {
            .menu-grid {
                grid-template-columns: repeat(2, 1fr);
            }
        }
    </style>
</head>
<body>

    <header>
        <h1>J&K Gourmet Bistro</h1>
        <p>Fresh Ingredients • Authentic Flavors • Culinary Excellence</p>
    </header>

    <main>
        <?php if ($db_error): ?>
            <div class="error-banner">
                <strong>Database Error:</strong> <?php echo htmlspecialchars($db_error); ?>
            </div>
        <?php else: ?>
            <?php foreach ($menu as $category => $items): ?>
                <section class="category-section">
                    <h2 class="category-title"><?php echo htmlspecialchars($category); ?></h2>
                    <div class="menu-grid">
                        <?php foreach ($items as $item): ?>
                            <div class="menu-item">
                                <div class="item-details">
                                    <h3 class="item-name"><?php echo htmlspecialchars($item['item_name']); ?></h3>
                                    <p class="item-desc"><?php echo htmlspecialchars($item['description']); ?></p>
                                </div>
                                <div class="item-price">
                                    $<?php echo number_format((float)$item['price'], 2); ?>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </section>
            <?php endforeach; ?>
        <?php endif; ?>
    </main>

</body>
</html>