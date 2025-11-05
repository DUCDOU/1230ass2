<?php
require_once 'functions.php';

// Check if user is logged in
if (!isLoggedIn()) {
    header('Location: index.php');
    exit;
}

$username = getCurrentUser();
$error = '';
$success = '';

// Handle topic creation
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['create_topic'])) {
    $title = trim($_POST['title'] ?? '');
    $description = trim($_POST['description'] ?? '');

    if (empty($title) || empty($description)) {
        $error = 'Both title and description are required.';
    } else {
        if (createTopic($username, $title, $description)) {
            $success = 'Topic created successfully!';
        } else {
            $error = 'Failed to create topic.';
        }
    }
}

// Handle theme change
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['change_theme'])) {
    $newTheme = $_POST['theme'] ?? 'light';
    setTheme($newTheme);
    header('Location: dashboard.php');
    exit;
}

$theme = getTheme();
$totalTopicsCreated = getTotalTopicsCreated($username);
$totalVotesCast = getTotalVotesCast($username);
?>
    <!DOCTYPE html>
    <html lang="en">
    <head>
        <meta charset="UTF-8">
        <title>Dashboard - Voting App</title>
        <style>
            body {
                font-family: Arial, sans-serif;
                margin: 20px;
            <?php if ($theme === 'dark'): ?>
                background-color: #222;
                color: #fff;
            <?php endif; ?>
            }
            .container { max-width: 800px; margin: 0 auto; }
            input, textarea { width: 100%; padding: 8px; margin: 5px 0; }
            button { padding: 10px 20px; background-color: #4CAF50; color: white; border: none; cursor: pointer; margin: 5px; }
            .error { color: red; padding: 10px; background-color: #ffebee; margin: 10px 0; }
            .success { color: green; padding: 10px; background-color: #e8f5e9; margin: 10px 0; }
            .stats { background-color: #f5f5f5; padding: 15px; margin: 20px 0; }
            <?php if ($theme === 'dark'): ?>
            .stats { background-color: #333; }
            input, textarea { background-color: #444; color: #fff; border: 1px solid #555; }
            <?php endif; ?>
            textarea { min-height: 80px; }
            nav { margin-bottom: 20px; }
            nav a { margin-right: 15px; }
        </style>
    </head>
    <body>
    <div class="container">
        <h1>Dashboard</h1>

        <nav>
            <a href="topics.php">View Topics</a>
            <a href="profile.php">Profile</a>
            <a href="logout.php">Logout</a>
        </nav>

        <p>Welcome, <strong><?php echo htmlspecialchars($username); ?></strong>!</p>

        <div class="stats">
            <h3>Your Statistics</h3>
            <p>Topics Created: <?php echo $totalTopicsCreated; ?></p>
            <p>Votes Cast: <?php echo $totalVotesCast; ?></p>
        </div>

        <h2>Create New Topic</h2>

        <?php if ($error): ?>
            <div class="error"><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>

        <?php if ($success): ?>
            <div class="success"><?php echo htmlspecialchars($success); ?></div>
        <?php endif; ?>

        <form method="POST">
            <label>Topic Title:</label>
            <input type="text" name="title" required>

            <label>Description:</label>
            <textarea name="description" required></textarea>

            <button type="submit" name="create_topic">Create Topic</button>
        </form>

        <h2>Theme Settings</h2>
        <form method="POST">
            <label>Select Theme:</label>
            <select name="theme">
                <option value="light" <?php echo $theme === 'light' ? 'selected' : ''; ?>>Light</option>
                <option value="dark" <?php echo $theme === 'dark' ? 'selected' : ''; ?>>Dark</option>
            </select>
            <button type="submit" name="change_theme">Apply Theme</button>
        </form>
    </div>
    </body>
    </html>
<?php show_source(__FILE__); ?>