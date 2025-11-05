<?php
require_once 'functions.php';

// Check if user is logged in
if (!isLoggedIn()) {
    header('Location: index.php');
    exit;
}

$username = getCurrentUser();
$message = '';

// Handle voting
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['vote'])) {
    $topicID = $_POST['topic_id'] ?? '';
    $voteType = $_POST['vote_type'] ?? '';

    if (vote($username, $topicID, $voteType)) {
        $message = 'Vote recorded successfully!';
    } else {
        $message = 'You have already voted on this topic.';
    }
}

$topics = getTopics();
$theme = getTheme();
?>
    <!DOCTYPE html>
    <html lang="en">
    <head>
        <meta charset="UTF-8">
        <title>Topics - Voting App</title>
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
            .topic {
                border: 1px solid #ddd;
                padding: 15px;
                margin: 15px 0;
            <?php if ($theme === 'dark'): ?>
                border-color: #555;
                background-color: #333;
            <?php endif; ?>
            }
            button { padding: 8px 15px; background-color: #4CAF50; color: white; border: none; cursor: pointer; margin: 5px; }
            .btn-down { background-color: #f44336; }
            .btn-disabled { background-color: #ccc; cursor: not-allowed; }
            .message { padding: 10px; background-color: #e8f5e9; color: green; margin: 10px 0; }
            nav { margin-bottom: 20px; }
            nav a { margin-right: 15px; }
        </style>
    </head>
    <body>
    <div class="container">
        <h1>All Topics</h1>

        <nav>
            <a href="dashboard.php">Dashboard</a>
            <a href="profile.php">Profile</a>
            <a href="logout.php">Logout</a>
        </nav>

        <?php if ($message): ?>
            <div class="message"><?php echo htmlspecialchars($message); ?></div>
        <?php endif; ?>

        <?php if (empty($topics)): ?>
            <p>No topics yet. <a href="dashboard.php">Create the first one!</a></p>
        <?php else: ?>
            <?php foreach ($topics as $topic): ?>
                <?php
                $voteResults = getVoteResults($topic['topicID']);
                $hasUserVoted = hasVoted($username, $topic['topicID']);
                ?>
                <div class="topic">
                    <h3><?php echo htmlspecialchars($topic['title']); ?></h3>
                    <p><em>Created by: <?php echo htmlspecialchars($topic['creator']); ?></em></p>
                    <p><?php echo htmlspecialchars($topic['description']); ?></p>

                    <p>
                        <strong>Votes:</strong>
                        Upvotes: <?php echo $voteResults['up']; ?> |
                        Downvotes: <?php echo $voteResults['down']; ?> |
                        Score: <?php echo ($voteResults['up'] - $voteResults['down']); ?>
                    </p>

                    <?php if ($hasUserVoted): ?>
                        <button class="btn-disabled" disabled>Already Voted</button>
                    <?php else: ?>
                        <form method="POST" style="display: inline;">
                            <input type="hidden" name="topic_id" value="<?php echo $topic['topicID']; ?>">
                            <input type="hidden" name="vote_type" value="up">
                            <button type="submit" name="vote">Upvote</button>
                        </form>
                        <form method="POST" style="display: inline;">
                            <input type="hidden" name="topic_id" value="<?php echo $topic['topicID']; ?>">
                            <input type="hidden" name="vote_type" value="down">
                            <button type="submit" name="vote" class="btn-down">Downvote</button>
                        </form>
                    <?php endif; ?>
                </div>
            <?php endforeach; ?>

            <h2>Leaderboard - Top Topics</h2>
            <?php
            // Sort topics by score
            $topicsWithScores = [];
            foreach ($topics as $topic) {
                $voteResults = getVoteResults($topic['topicID']);
                $topicsWithScores[] = [
                        'title' => $topic['title'],
                        'score' => $voteResults['up'] - $voteResults['down'],
                        'upvotes' => $voteResults['up'],
                        'downvotes' => $voteResults['down']
                ];
            }
            usort($topicsWithScores, function($a, $b) {
                return $b['score'] - $a['score'];
            });
            $topTopics = array_slice($topicsWithScores, 0, 5);
            ?>
            <ol>
                <?php foreach ($topTopics as $topic): ?>
                    <li>
                        <strong><?php echo htmlspecialchars($topic['title']); ?></strong> -
                        Score: <?php echo $topic['score']; ?>
                        (<?php echo $topic['upvotes']; ?> up / <?php echo $topic['downvotes']; ?> down)
                    </li>
                <?php endforeach; ?>
            </ol>
        <?php endif; ?>
    </div>
    </body>
    </html>
<?php show_source(__FILE__); ?>