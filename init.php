<?php
function sanitize_post_data() {
    global $_POST;

    if ($_SERVER["REQUEST_METHOD"] == "POST") {
        foreach ($_POST as $key => $value) {
            if (is_array($value)) {
                $_POST[$key] = array_map("sanitize_value", $value);
            } else {
                $_POST[$key] = sanitize_value($value);
            }
        }
    }
}

function sanitize_value($value) {
    $value = trim($value);
    
    if (filter_var($value, FILTER_VALIDATE_INT)) {
        return filter_var($value, FILTER_SANITIZE_NUMBER_INT);
    }
    if (filter_var($value, FILTER_VALIDATE_FLOAT)) {
        return filter_var($value, FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION);
    }

    return htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
}

sanitize_post_data();
?>
