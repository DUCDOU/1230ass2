<?php
/**
 * COMP1230 Assignment 2: Voting Application
 * Core Functions File
 * Gia Duc Can 101570606
 */

// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// File paths
define('USERS_FILE', 'users.txt');
define('TOPICS_FILE', 'topics.txt');
define('VOTES_FILE', 'votes.txt');
// USER MANAGEMENT FUNCTIONS

//Registers a new user by storing username and password in users.txt
function registerUser($username, $password) {
    // Check if username already exists
    if (file_exists(USERS_FILE)) {
        $users = file(USERS_FILE, FILE_IGNORE_NEW_LINES);
        foreach ($users as $user) {
            $userData = explode(':', $user);
            if ($userData[0] === $username) {
                return false; // Username already exists
            }
        }
    }
    // Add new user
    $userEntry = $username . ':' . $password . "\n";
    $result = file_put_contents(USERS_FILE, $userEntry, FILE_APPEND | LOCK_EX);
    return $result !== false;
}

//Authenticates a user by checking users.txt for matching credentials
function authenticateUser($username, $password) {
    if (!file_exists(USERS_FILE)) {
        return false;
    }
    $users = file(USERS_FILE, FILE_IGNORE_NEW_LINES);
    foreach ($users as $user) {
        $userData = explode(':', $user);
        if ($userData[0] === $username && $userData[1] === $password) {
            return true;
        }
    }
    return false;
}
// TOPIC MANAGEMENT FUNCTIONS

//Generates a unique ID for a new topic
function generateTopicID() {
    if (!file_exists(TOPICS_FILE)) {
        return 1;
    }
    $topics = file(TOPICS_FILE, FILE_IGNORE_NEW_LINES);
    if (empty($topics)) {
        return 1;
    }
    $maxID = 0;
    foreach ($topics as $topic) {
        $topicData = explode('|', $topic);
        if (isset($topicData[0]) && is_numeric($topicData[0])) {
            $maxID = max($maxID, (int)$topicData[0]);
        }
    }
    return $maxID + 1;
}

// Creates a new topic and stores it in topics.txt
function createTopic($username, $title, $description) {
    $topicID = generateTopicID();
    $topicEntry = $topicID . '|' . $username . '|' . $title . '|' . $description . "\n";
    $result = file_put_contents(TOPICS_FILE, $topicEntry, FILE_APPEND | LOCK_EX);
    return $result !== false;
}

//Retrieves all topics stored in topics.txt
function getTopics() {
    if (!file_exists(TOPICS_FILE)) {
        return [];
    }
    $topicsData = file(TOPICS_FILE, FILE_IGNORE_NEW_LINES);
    $topics = [];
    foreach ($topicsData as $line) {
        $parts = explode('|', $line);
        if (count($parts) === 4) {
            $topics[] = [
                'topicID' => $parts[0],
                'creator' => $parts[1],
                'title' => $parts[2],
                'description' => $parts[3]
            ];
        }
    }
    return $topics;
}
// VOTING FUNCTIONS

//Checks if a user has already voted on a given topic
function hasVoted($username, $topicID) {
    if (!file_exists(VOTES_FILE)) {
        return false;
    }

    $votes = file(VOTES_FILE, FILE_IGNORE_NEW_LINES);
    foreach ($votes as $vote) {
        $voteData = explode('|', $vote);
        if (count($voteData) === 3 && $voteData[0] === $username && $voteData[1] === $topicID) {
            return true;
        }
    }
    return false;
}

//Casts a vote (up or down) for a topic

function vote($username, $topicID, $voteType) {
    // Check if user already voted
    if (hasVoted($username, $topicID)) {
        return false;
    }

    // Record the vote
    $voteEntry = $username . '|' . $topicID . '|' . $voteType . "\n";
    $result = file_put_contents(VOTES_FILE, $voteEntry, FILE_APPEND | LOCK_EX);
    return $result !== false;
}
// Retrieves the total upvotes and downvotes for a topic
function getVoteResults($topicID) {
    $results = ['up' => 0, 'down' => 0];

    if (!file_exists(VOTES_FILE)) {
        return $results;
    }

    $votes = file(VOTES_FILE, FILE_IGNORE_NEW_LINES);
    foreach ($votes as $vote) {
        $voteData = explode('|', $vote);
        if (count($voteData) === 3 && $voteData[1] === $topicID) {
            if ($voteData[2] === 'up') {
                $results['up']++;
            } elseif ($voteData[2] === 'down') {
                $results['down']++;
            }
        }
    }
    return $results;
}
//Gets voting history for a specific user
function getUserVotingHistory($username) {
    if (!file_exists(VOTES_FILE)) {
        return [];
    }

    $votes = file(VOTES_FILE, FILE_IGNORE_NEW_LINES);
    $history = [];

    foreach ($votes as $vote) {
        $voteData = explode('|', $vote);
        if (count($voteData) === 3 && $voteData[0] === $username) {
            $history[] = [
                'topicID' => $voteData[1],
                'voteType' => $voteData[2]
            ];
        }
    }
    return $history;
}
//Gets total number of topics created by a user
function getTotalTopicsCreated($username) {
    if (!file_exists(TOPICS_FILE)) {
        return 0;
    }

    $topics = file(TOPICS_FILE, FILE_IGNORE_NEW_LINES);
    $count = 0;

    foreach ($topics as $topic) {
        $topicData = explode('|', $topic);
        if (count($topicData) === 4 && $topicData[1] === $username) {
            $count++;
        }
    }
    return $count;
}
//Gets total number of votes cast by a user
function getTotalVotesCast($username) {
    return count(getUserVotingHistory($username));
}

// SESSION MANAGEMENT FUNCTIONS

// Sets a session variable
function setSession($key, $value) {
    $_SESSION[$key] = $value;
    return true;
}
// Retrieves a session variable

function getSession($key) {
    return isset($_SESSION[$key]) ? $_SESSION[$key] : null;
}
// COOKIE MANAGEMENT FUNCTIONS

//Sets a cookie with a given key and value
function setAppCookie($key, $value) { // Renamed from setCookie to setAppCookie
    // Check if headers have already been sent
    if (headers_sent()) {
        return false;
    }
    return setcookie($key, $value, time() + (86400 * 30), "/"); // 30 days
}

//Retrieves a cookie value

function getCookie($key) {
    return isset($_COOKIE[$key]) ? $_COOKIE[$key] : null;
}

//Sets the theme preference
function setTheme($theme) {
    // Try to set cookie first
    if (!headers_sent()) {
        setAppCookie('theme', $theme); // Updated the call to the new function name
    }
    // Always set in session as backup
    setSession('theme', $theme);
    return true;
}

// Gets the current theme preference
function getTheme() {
    // Check session first
    $sessionTheme = getSession('theme');
    if ($sessionTheme !== null) {
        return $sessionTheme;
    }
    // Then check cookie
    $cookieTheme = getCookie('theme');
    if ($cookieTheme !== null) {
        // Store in session for future use
        setSession('theme', $cookieTheme);
        return $cookieTheme;
    }
    // Default to light
    return 'light';
}

// HELPER FUNCTIONS

//Checks if user is logged in
function isLoggedIn() {
    return getSession('username') !== null;
}

// Gets the current logged-in username
function getCurrentUser() {
    return getSession('username');
}
// Logs out the current user
function logout() {
    session_destroy();
}