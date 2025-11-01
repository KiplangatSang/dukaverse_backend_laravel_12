<?php

return [
    'credentials' => env('FIREBASE_CREDENTIALS', storage_path('app/firebase-credentials.json')),
    'database_url' => env('FIREBASE_DATABASE_URL', 'https://your-project.firebaseio.com'),
    'project_id' => env('FIREBASE_PROJECT_ID', 'your-project-id'),
];
