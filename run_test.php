
<?php
// Define the PHPUnit command to run both test files and save output to output.html
const DS = DIRECTORY_SEPARATOR;
$command = '../vendor/bin/phpunit --testdox-html output.html VotingAppTest2.php VotingAppTest1.php';
$command = '..'. DS . 'vendor' . DS . 'bin' . DS . 'phpunit -c phpunit.xml';

// Run PHPUnit and capture the output in output.html
exec($command);

// Check if output.html was created and display its contents
if (file_exists('output.html')) {
    $output = file_get_contents('output.html');
    echo $output;
    #unlink('output.html'); // Optionally delete the file after displaying it
}
?>

