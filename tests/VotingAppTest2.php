<?php

use PHPUnit\Framework\TestCase;

class VotingAppTest2 extends TestCase
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
        #@unlink($this->userFile);
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
    public function testRegisterUserSuccess()
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

    public function testRegisterUserFailureExistingUsername()
    {
        require_once __DIR__ . '/../functions.php';

        $username = 'student';
        $password = 'password123';
        registerUser($username, $password);
        $result = registerUser($username, $password);
        $this->assertFalse($result, "User registration should fail with existing username.");
    }

    // Test user authentication
    public function testAuthenticateUserSuccess()
    {
        require_once __DIR__ . '/../functions.php';

        $username = 'student';
        $password = 'password123';
        registerUser($username, $password);

        $result = authenticateUser($username, $password);
        $this->assertTrue($result, "User authentication failed with correct credentials.");
    }

    public function testAuthenticateUserFailureInvalidCredentials()
    {
        require_once __DIR__ . '/../functions.php';

        $username = 'student';
        $password = 'password123';
        registerUser($username, $password);

        $result = authenticateUser($username, 'wrongpassword');
        $this->assertFalse($result, "User authentication should fail with incorrect credentials.");
    }

    // Test creating a new topic
    public function testCreateTopicSuccess()
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
    public function testGetTopicsSuccess()
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
    public function testVoteSuccess()
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
    }

    public function testVoteFailureInvalidTopicId()
    {
        require_once __DIR__ . '/../functions.php';

        $result = vote('student', -1, 'up');
        $this->assertFalse($result, "Voting should fail with invalid topic ID.");
    }

    // Test checking if a user has already voted
    public function testHasVotedSuccess()
    {
        require_once __DIR__ . '/../functions.php';

        createTopic('student', 'Favorite Movie', 'Vote for your favorite movie.');
        $topicId = 0; // Assuming the first topic is at index 0

        // Student votes up
        vote('student', $topicId, 'up');

        // Check if the user has already voted
        $result = hasVoted('student', $topicId);
        $this->assertTrue($result, "User should have already voted.");
    }

    public function testHasVotedFailureUserHasNotVoted()
    {
        require_once __DIR__ . '/../functions.php';

        createTopic('student', 'Favorite Movie', 'Vote for your favorite movie.');
        $topicId = 0; // Assuming the first topic is at index 0

        // Check if the user has already voted without voting
        $result = hasVoted('new_user', $topicId);
        $this->assertFalse($result, "User has not voted yet.");
    }

    // Test session management functions
    public function testSessionManagementSuccess()
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
    public function testCookieManagementSuccess()
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