<?php
$sql = file_get_contents('sans-spmb.sql');

// Search for activity logs mentioning field or step
preg_match('/INSERT INTO `spmb_activity_logs` \([^)]+\) VALUES\s*([\s\S]+?);/i', $sql, $mLogs);
if (!empty($mLogs[1])) {
    preg_match_all('/\(([^)]+)\)/', $mLogs[1], $tuples);
    foreach ($tuples[1] as $t) {
        if (str_contains($t, '44') || str_contains(strtolower($t), 'field') || str_contains(strtolower($t), 'step') || str_contains(strtolower($t), 'formulir')) {
            echo "Activity Log: " . substr($t, 0, 200) . "\n";
        }
    }
}

// Search all form steps in SQL
preg_match('/INSERT INTO `spmb_form_steps` \([^)]+\) VALUES\s*([\s\S]+?);/i', $sql, $mSteps);
echo "\n=== ALL FORM STEPS IN SQL ===\n";
echo $mSteps[1] ?? 'None';

// Search all form fields in SQL
preg_match('/INSERT INTO `spmb_form_fields` \([^)]+\) VALUES\s*([\s\S]+?);/i', $sql, $mFields);
echo "\n\n=== ALL FORM FIELDS IN SQL ===\n";
echo $mFields[1] ?? 'None';
