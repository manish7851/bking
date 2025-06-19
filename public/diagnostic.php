<?php
// This script helps verify the Khalti integration keys

echo "Khalti Key Diagnostic Tool\n";
echo "========================\n\n";

// Check .env file
$env_path = __DIR__ . '/../.env';
if (file_exists($env_path)) {
    $env_content = file_get_contents($env_path);
    echo "Checking .env file:\n";
    
    // Public key check
    preg_match('/KHALTI_PUBLIC_KEY=([^\n\r]+)/', $env_content, $public_matches);
    if (!empty($public_matches[1])) {
        $public_key = $public_matches[1];
        echo "✓ Public key found: " . substr($public_key, 0, 15) . "...\n";
        echo "  Key length: " . strlen($public_key) . " characters\n";
        
        if (strpos($public_key, 'test_public_key_') === 0 || strpos($public_key, 'live_public_key_') === 0) {
            echo "✓ Public key has valid prefix\n";
        } else {
            echo "✗ Public key does NOT have valid prefix (should start with test_public_key_ or live_public_key_)\n";
        }
    } else {
        echo "✗ Public key not found in .env\n";
    }
    
    // Secret key check
    preg_match('/KHALTI_SECRET_KEY=([^\n\r]+)/', $env_content, $secret_matches);
    if (!empty($secret_matches[1])) {
        $secret_key = $secret_matches[1];
        echo "✓ Secret key found: " . substr($secret_key, 0, 15) . "...\n";
        echo "  Key length: " . strlen($secret_key) . " characters\n";
        
        if (strpos($secret_key, 'test_secret_key_') === 0 || strpos($secret_key, 'live_secret_key_') === 0) {
            echo "✓ Secret key has valid prefix\n";
        } else {
            echo "✗ Secret key does NOT have valid prefix (should start with test_secret_key_ or live_secret_key_)\n";
        }
    } else {
        echo "✗ Secret key not found in .env\n";
    }
} else {
    echo "✗ .env file not found\n";
}

// Check config
echo "\nChecking config/khalti.php:\n";
if (file_exists(__DIR__ . '/../config/khalti.php')) {
    $config_content = file_get_contents(__DIR__ . '/../config/khalti.php');
    
    // Check for hardcoded public key
    preg_match("/'public_key' => env\('KHALTI_PUBLIC_KEY', '([^']+)'\)/", $config_content, $public_matches);
    if (!empty($public_matches[1])) {
        echo "✓ Fallback public key found: " . substr($public_matches[1], 0, 15) . "...\n";
        
        if (strpos($public_matches[1], 'test_public_key_') === 0 || strpos($public_matches[1], 'live_public_key_') === 0) {
            echo "✓ Fallback public key has valid prefix\n";
        } else {
            echo "✗ Fallback public key does NOT have valid prefix\n";
        }
    } else {
        echo "✗ No fallback public key found in config\n";
    }
    
    // Check for hardcoded secret key
    preg_match("/'secret_key' => env\('KHALTI_SECRET_KEY', '([^']+)'\)/", $config_content, $secret_matches);
    if (!empty($secret_matches[1])) {
        echo "✓ Fallback secret key found: " . substr($secret_matches[1], 0, 15) . "...\n";
        
        if (strpos($secret_matches[1], 'test_secret_key_') === 0 || strpos($secret_matches[1], 'live_secret_key_') === 0) {
            echo "✓ Fallback secret key has valid prefix\n";
        } else {
            echo "✗ Fallback secret key does NOT have valid prefix\n";
        }
    } else {
        echo "✗ No fallback secret key found in config\n";
    }
} else {
    echo "✗ config/khalti.php not found\n";
}

// Check blade template
echo "\nChecking create.blade.php for hardcoded keys:\n";
if (file_exists(__DIR__ . '/../resources/views/booking/create.blade.php')) {
    $blade_content = file_get_contents(__DIR__ . '/../resources/views/booking/create.blade.php');
    
    // Look for hardcoded keys
    preg_match('/window\.khaltiPublicKey\s*=\s*"([^"]+)"/', $blade_content, $key_matches);
    
    if (!empty($key_matches[1])) {
        echo "✓ Found hardcoded public key in template: " . substr($key_matches[1], 0, 15) . "...\n";
        
        if ($key_matches[1] == 'test_public_key_dc74a3b7f7bd44aa88e6b1f6b3e544bd') {
            echo "✓ Hardcoded key matches expected value\n";
        } else {
            echo "✗ Hardcoded key does NOT match expected value\n"; 
            echo "  Expected: test_public_key_dc74a3b7f7bd44aa88e6b1f6b3e544bd\n";
            echo "  Found: " . $key_matches[1] . "\n";
        }
    } else {
        echo "✗ No hardcoded key found in template\n";
    }
} else {
    echo "✗ create.blade.php not found\n";
}

echo "\nDiagnostic complete. If all checks pass, the Khalti integration should work correctly.\n";
