<?php
require_once 'functions.php';

// Check if user is logged in
if (!isLoggedIn()) {
    header('Location: index.php');
    exit;
}

$username = getCurrentUser();
$theme = getTheme();

// Get user statistics
$totalTopicsCreated = getTotalTopicsCreated($username);
$totalVotesCast = getTotalVotesCast($username);
$votingHistory = getUserVotingHistory($username);

// Get all topics to map topic IDs to titles
$allTopics = getTopics();
$topicMap = [];
foreach ($allTopics as $topic) {
    $topicMap[$topic['topicID']] = $topic;
}

// Get user's created topics
$userTopics = array_filter($allTopics, function($topic) use ($username) {
    return $topic['creator'] === $username;
});
?>
    <!DOCTYPE html>
    <html lang="en">
    <head>
        <meta charset="UTF-8">
        <title>Profile - Voting App</title>
        <style>
            body {
                font-family: Arial, sans-serif;
                margin: 20px;
            <?php if ($theme === 'dark'): ?>
                background-color: #222;
                color: #fff;
            <?php endif; ?>
            }
            .container { max-width: 900px; margin: 0 auto; }
            .section {
                border: 1px solid #ddd;
                padding: 15px;
                margin: 15px 0;
            <?php if ($theme === 'dark'): ?>
                border-color: #555;
                background-color: #333;
            <?php endif; ?>
            }
            .stats { background-color: #f5f5f5; padding: 15px; margin: 15px 0; }
            <?php if ($theme === 'dark'): ?>
            .stats { background-color: #444; }
            <?php endif; ?>
            .vote-item { padding: 10px; margin: 10px 0; background-color: #fafafa; }
            <?php if ($theme === 'dark'): ?>
            .vote-item { background-color: #444; }
            <?php endif; ?>
            nav { margin-bottom: 20px; }
            nav a { margin-right: 15px; }
        </style>
    </head>
    <body>
    <div class="container">
        <h1>User Profile</h1>

        <nav>
            <a href="dashboard.php">Dashboard</a>
            <a href="topics.php">Topics</a>
            <a href="logout.php">Logout</a>
        </nav>

        <h2>Welcome, <?php echo htmlspecialchars($username); ?>!</h2>

        <div class="stats">
            <h3>Your Statistics</h3>
            <p><strong>Topics Created:</strong> <?php echo $totalTopicsCreated; ?></p>
            <p><strong>Votes Cast:</strong> <?php echo $totalVotesCast; ?></p>
        </div>

        <div class="section">
            <h2>Your Voting History</h2>
            <?php if (empty($votingHistory)): ?>
                <p>You haven't voted on any topics yet. <a href="topics.php">Browse topics</a></p>
            <?php else: ?>
                <?php foreach ($votingHistory as $vote): ?>
                    <?php if (isset($topicMap[$vote['topicID']])): ?>
                        <?php $topic = $topicMap[$vote['topicID']]; ?>
                        <div class="vote-item">
                            <strong><?php echo htmlspecialchars($topic['title']); ?></strong><br>
                            Created by: <?php echo htmlspecialchars($topic['creator']); ?><br>
                            Your vote: <strong><?php echo $vote['voteType'] === 'up' ? 'Upvote' : 'Downvote'; ?></strong>
                        </div>
                    <?php endif; ?>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>

        <div class="section">
            <h2>Your Created Topics</h2>
            <?php if (empty($userTopics)): ?>
                <p>You haven't created any topics yet. <a href="dashboard.php">Create one now</a></p>
            <?php else: ?>
                <?php foreach ($userTopics as $topic): ?>
                    <?php $voteResults = getVoteResults($topic['topicID']); ?>
                    <div class="vote-item">
                        <strong><?php echo htmlspecialchars($topic['title']); ?></strong><br>
                        <?php echo htmlspecialchars($topic['description']); ?><br>
                        Upvotes: <?php echo $voteResults['up']; ?> |
                        Downvotes: <?php echo $voteResults['down']; ?> |
                        Score: <?php echo ($voteResults['up'] - $voteResults['down']); ?>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>
    </body>
    </html>
<?php show_source(__FILE__); ?>