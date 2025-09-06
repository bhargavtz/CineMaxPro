<?php
// Check if Composer is installed
if (!file_exists('composer.phar')) {
    // Download Composer
    echo "Downloading Composer...\n";
    copy('https://getcomposer.org/installer', 'composer-setup.php');
    if (hash_file('sha384', 'composer-setup.php') === trim(file_get_contents('https://composer.github.io/installer.sig'))) {
        echo "Installer verified\n";
        echo shell_exec('php composer-setup.php');
        unlink('composer-setup.php');
        echo "Composer installed successfully\n";
    } else {
        echo "Installer corrupt\n";
        unlink('composer-setup.php');
        exit(1);
    }
}

// Install dependencies
echo shell_exec('php composer.phar install');
echo "Dependencies installed successfully\n";
?>
