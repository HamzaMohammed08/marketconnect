<?php
// Helper functions for the website

// Format currency 
function format_currency($amount, $include_symbol = true) {
    $formatted = number_format($amount, 2, '.', ',');
    if ($include_symbol) {
        $formatted = 'R' . $formatted;
    }
    return $formatted;
}

// Get condition text
function get_condition_text($condition_status) {
    // Handle both old numeric format and new enum format
    switch ($condition_status) {
        // New enum format
        case 'new':
            return 'New';
        case 'like_new':
            return 'Like New';
        case 'good':
            return 'Good';
        case 'fair':
            return 'Fair';
        case 'poor':
            return 'Poor';
        
        // Legacy numeric format (for backward compatibility)
        case 1:
            return 'New';
        case 2:
            return 'Like New';
        case 3:
            return 'Good';
        case 4:
            return 'Fair';
        case 5:
            return 'Poor';
        
        default:
            return 'Unknown';
    }
}

// Cut string if too long
function truncate_string($string, $length, $append = '...') {
    if (strlen($string) > $length) {
        $string = substr($string, 0, $length - strlen($append)) . $append;
    }
    return $string;
}

// Show how long ago something happened
function time_elapsed_string($datetime) {
    $now = new DateTime;
    $ago = new DateTime($datetime);
    $diff = $now->diff($ago);

    if ($diff->d > 0) {
        return $diff->d . ' day' . ($diff->d > 1 ? 's' : '') . ' ago';
    } elseif ($diff->h > 0) {
        return $diff->h . ' hour' . ($diff->h > 1 ? 's' : '') . ' ago';
    } elseif ($diff->i > 0) {
        return $diff->i . ' minute' . ($diff->i > 1 ? 's' : '') . ' ago';
    } else {
        return 'just now';
    }
}
?>
