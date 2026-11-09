<?php

return [
    'title' => 'Devices',
    'create_device' => 'Create Device',
    'edit_device' => 'Edit Device',
    'monitor_status' => 'Monitor Status',
    'update_selected' => 'Update Selected',
    'restart' => 'Restart',
    'restart_confirm' => 'Are you sure you want to restart this device?',
    
    // Table headers
    'id' => 'ID Clock',
    'oficina' => 'Office',
    'ubicacion' => 'Location',
    'online' => 'Online',
    'last_attendance' => 'Last Attendance',
    'desfases_hoy' => 'Today Issues',
    
    // Form fields
    'device_name' => 'Device Name',
    'device_id' => 'Device ID',
    'ip_address' => 'IP Address',
    'port' => 'Port',
    'serial_number' => 'Serial Number',
    
    // Messages
    'created_successfully' => 'Device created successfully.',
    'updated_successfully' => 'Device updated successfully.',
    'deleted_successfully' => 'Device deleted successfully.',
    'restart_successfully' => 'Device restart command sent successfully.',
    'error_creating' => 'Error creating device.',
    'error_updating' => 'Error updating device.',
    'error_deleting' => 'Error deleting device.',
    'error_restarting' => 'Error restarting device.',
    
    // Device log
    'device_log_title' => 'Device Log',
    'finger_log_title' => 'Finger Log',
    'fingerprints_title' => 'Fingerprints',
    'activity_title' => 'Device Activity',
    'monitor_title' => 'Device Monitor',

    // Fingerprint pull / push
    'device' => 'Device',
    'filter' => 'Filter',
    'all_offices' => 'All offices',
    'device_not_found' => 'Device not found.',
    'offline_warning' => 'offline',
    'employee_pin' => 'Employee PIN',
    'employee_name' => 'Name',
    'finger' => 'Finger',
    'template_size' => 'Template size',
    'valid' => 'Valid',
    'source' => 'Source',
    'captured_at' => 'Captured',
    'no_templates_yet' => 'No fingerprint templates captured yet. Queue a pull to ask a terminal for them.',
    'fingerprints_captured' => 'Fingerprints captured',
    'pull_fingerprints' => 'Pull fingerprints',
    'push_fingerprints' => 'Send fingerprints',
    'queue_pull' => 'Queue pull',
    'pull_mode' => 'What to pull',
    'pull_mode_bulk' => 'Everything the device holds',
    'pull_mode_bulk_help' => 'Sends a CHECK command plus one query per face type. Best for a terminal that was already enrolled in the field.',
    'pull_mode_roster' => 'Every employee on this office roster',
    'pull_mode_roster_help' => 'One query per employee per finger. Precise, but queues a lot of commands.',
    'pull_mode_pin' => 'One employee',
    'pull_mode_pin_help' => 'Queries all ten finger slots for a single PIN.',
    'pull_explainer' => 'Templates do not arrive immediately. The command waits in the queue until the terminal next polls the server, and the templates are uploaded after that - usually within a minute for an online device.',
    'pull_queued' => ':count command(s) queued for :device. Templates will appear here as the terminal answers.',
    'pull_nothing_queued' => 'Nothing queued - an identical pull is already pending for that device.',
    'push_queued' => ':count fingerprint(s) queued for :device.',
    'push_nothing_queued' => 'Nothing to send - the device already holds every stored template (:skipped up to date).',
];
