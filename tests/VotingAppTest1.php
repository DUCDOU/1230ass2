<?php

use PHPUnit\Framework\TestCase;

class VotingAppTest1 extends TestCase
{
    private $userFile = __DIR__ . '/users.txt';
    private $topicFile = __DIR__ . '/topics.txt';
    private $voteFile = __DIR__ . '/votes.txt';
    private $sessionFile = __DIR__ . '/session.txt';
    private $cookieFile = __DIR__ . '/cookie.txt';
    private $ids     = __DIR__ . '/ids.txt';

    // Set up before each test
    protected function setUp(): void
    {
        // Clean up the test files before each test
        @unlink($this->userFile);
        @unlink($this->topicFile);
        @unlink($this->voteFile);
        @unlink($this->sessionFile);
        @unlink($this->cookieFile);
        
        // Create empty files for testing
        file_put_contents($this->userFile, '');
        file_put_contents($this->topicFile, '');
        file_put_contents($this->voteFile, '');
        if (session_status() == PHP_SESSION_NONE) {
            @session_start();
        }        
    }

    // Test user registration
    public function testRegisterUser()
    {
        require_once __DIR__ . '/../functions.php';
        
        $username = 'student';
        $password = 'password123';
        $result = registerUser($username, $password);
        $this->assertTrue($result, "User registration failed.");

        // Check if the user is correctly saved in the file
        $contents = file_get_contents($this->userFile);
        $this->assertStringContainsString('student:password123', $contents, "User data is not saved correctly.");
    }

    // Test user authentication
    public function testAuthenticateUser()
    {
        require_once __DIR__ . '/../functions.php';

        $username = 'student';
        $password = 'password123';
        registerUser($username, $password);

        $result = authenticateUser($username, $password);
        $this->assertTrue($result, "User authentication failed with correct credentials.");

        $result = authenticateUser($username, 'wrongpassword');
        $this->assertFalse($result, "User authentication passed with incorrect credentials.");
    }

    // Test creating a new topic
    public function testCreateTopic()
    {
        require_once __DIR__ . '/../functions.php';

        $username = 'student';
        $topicTitle = 'Favorite Programming Language';
        $description = 'Vote for your favorite programming language.';

        $result = createTopic($username, $topicTitle, $description);
        $this->assertTrue($result, "Failed to create a new topic.");

        // Check if the topic is correctly saved in the file
        $contents = file_get_contents($this->topicFile);
        $this->assertMatchesRegularExpression('/\d+|student|Favorite Programming Language|Vote for your favorite programming language./', $contents, "Topic data is not saved correctly.");
    }

    // Test retrieving all topics
    public function testGetTopics()
    {
        require_once __DIR__ . '/../functions.php';

        createTopic('student', 'Favorite Movie', 'Vote for your favorite movie.');
        createTopic('teacher', 'Best Coding Language', 'Vote for the best coding language.');

        $topics = getTopics();
        $this->assertCount(2, $topics, "Topic retrieval failed.");

        $this->assertEquals('Favorite Movie', $topics[0]['title'], "First topic title is incorrect.");
        $this->assertEquals('Best Coding Language', $topics[1]['title'], "Second topic title is incorrect.");
    }

    // Test voting mechanism
    public function testVote()
    {
        require_once __DIR__ . '/../functions.php';

        createTopic('student', 'Favorite Movie', 'Vote for your favorite movie.');
        $topicId = 0; // Assuming the first topic is at index 0

        // Student votes up
        $result = vote('student', $topicId, 'up');
        $this->assertTrue($result, "Failed to cast an upvote.");

        // Another student votes down
        $result = vote('student2', $topicId, 'down');
        $this->assertTrue($result, "Failed to cast a downvote.");

        // Retrieve vote results
        $results = getVoteResults($topicId);
        $this->assertEquals(1, $results['up'], "Upvote count is incorrect.");
        $this->assertEquals(1, $results['down'], "Downvote count is incorrect.");
    }

    // Test checking if a user has already voted
    public function testHasVoted()
    {
        require_once __DIR__ . '/../functions.php';

        createTopic('student', 'Favorite Movie', 'Vote for your favorite movie.');
        $topicId = 0; // Assuming the first topic is at index 0

        // Student votes up
        vote('student', $topicId, 'up');

        // Check if the user has already voted
        $result = hasVoted('student', $topicId);
        $this->assertTrue($result, "User should have already voted.");
        
        // Check for a user who hasn't voted
        $result = hasVoted('new_user', $topicId);
        $this->assertFalse($result, "User has not voted yet.");
    }

    // Test session management functions
    public function testSessionManagement()
    {
        require_once __DIR__ . '/../functions.php';
        // Start session for testing
        if (session_status() === PHP_SESSION_NONE) {
            @session_start();
        }
        setSession('user', 'student');
        $user = getSession('user');
        $this->assertEquals('student', $user, "Failed to set or get session variable.");
        // Clean up: Only destroy if session is active
        if (session_status() === PHP_SESSION_ACTIVE) {
            session_unset();
            session_destroy();
        }  
    }

    // Test cookie management functions
    public function testCookieManagement()
    {
        require_once __DIR__ . '/../functions.php';
        // Test setting a cookie
        $result = set_cookie('theme', 'dark');
        $this->assertTrue($result, "Failed to set cookie value.");

        $theme = getCookie('theme');
        $this->assertEquals('dark', $theme, "Failed to set or get cookie value.");
        // Clean up by unsetting the 
        unset($_COOKIE['theme']);        
    }

    // Clean up after each test
    protected function tearDown(): void
    {
        // Clean up the test files after each test
        @unlink($this->userFile);
        @unlink($this->topicFile);
        @unlink($this->voteFile);
        @unlink($this->sessionFile);
        @unlink($this->cookieFile);
        @unlink($this->ids);
    }
}

